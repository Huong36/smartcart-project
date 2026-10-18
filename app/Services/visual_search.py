"""Local OpenCLIP image retrieval engine; start from the Laravel project root."""
import argparse
import base64
import hashlib
import hmac
import io
import json
import logging
import os
from pathlib import Path
import secrets
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

import numpy as np
import open_clip
from huggingface_hub import hf_hub_download
from huggingface_hub.errors import LocalEntryNotFoundError
from PIL import Image, ImageOps, UnidentifiedImageError
import torch

MODEL = "ViT-B-32"
WEIGHTS = "laion2b_s34b_b79k"
MODEL_REVISION = "1a25a446712ba5ee05982a381eed697ef9b435cf"
MAX_IMAGE_BYTES = 2 * 1024 * 1024
Image.MAX_IMAGE_PIXELS = 36_000_000


class ImageIndex:
    def __init__(self, root):
        self.root = Path(root).resolve()
        self.allowed = (self.root / "storage/app/public").resolve()
        self.state = self.root / "storage/app/private/visual-search"
        self.state.mkdir(parents=True, exist_ok=True)
        token_file = self.state / "token"
        if not token_file.exists():
            token_file.write_text(secrets.token_hex(32), encoding="utf-8")
        self.token = token_file.read_text(encoding="utf-8").strip()
        self.lock = threading.Lock()
        torch.set_num_threads(min(4, os.cpu_count() or 1))
        self.device = "cuda" if torch.cuda.is_available() else "cpu"
        logging.info("Loading %s / %s on %s", MODEL, WEIGHTS, self.device)
        checkpoint_options = dict(
            repo_id="laion/CLIP-ViT-B-32-laion2B-s34B-b79K",
            filename="open_clip_model.safetensors",
            revision=MODEL_REVISION,
            cache_dir=os.environ.get("VISUAL_SEARCH_MODEL_CACHE", str(Path.home() / ".cache/smartcart-clip")),
        )
        try:
            checkpoint = hf_hub_download(**checkpoint_options, local_files_only=True)
        except LocalEntryNotFoundError:
            checkpoint = hf_hub_download(**checkpoint_options)
        self.model, _, self.preprocess = open_clip.create_model_and_transforms(
            MODEL, pretrained=checkpoint, device=self.device,
        )
        self.model.eval()
        self.cache_file = self.state / "index.npz"
        self.cache = {}
        if self.cache_file.exists():
            try:
                with np.load(self.cache_file, allow_pickle=False) as data:
                    if str(data["model"]) == f"{MODEL}:{WEIGHTS}:{MODEL_REVISION}":
                        self.cache = dict(zip(data["keys"].tolist(), data["vectors"]))
            except (ValueError, OSError, KeyError):
                logging.warning("Cached image index is invalid; rebuilding when needed")
        self.indexed = 0
        logging.info("Model ready; cached images: %d", len(self.cache))

    def encode(self, images):
        tensor = torch.stack([self.preprocess(image) for image in images]).to(self.device)
        with torch.inference_mode():
            features = self.model.encode_image(tensor)
            features = features / features.norm(dim=-1, keepdim=True).clamp_min(1e-12)
        return features.cpu().float().numpy()

    def search(self, request):
        raw = base64.b64decode(request["image"], validate=True)
        if len(raw) > MAX_IMAGE_BYTES:
            raise ValueError("Image too large")
        with Image.open(io.BytesIO(raw)) as opened:
            if opened.format not in {"JPEG", "PNG", "WEBP"} or max(opened.size) > 6000:
                raise ValueError("Unsupported image")
            query = ImageOps.exif_transpose(opened).convert("RGB")
        catalog = request["catalog"]
        if not isinstance(catalog, list) or len(catalog) > 5000:
            raise ValueError("Invalid catalog")
        minimum = float(request.get("minimum_similarity", 0.45))
        if not np.isfinite(minimum) or not 0 <= minimum <= 1:
            raise ValueError("Invalid threshold")
        limit = min(8, max(1, int(request.get("limit", 8))))
        with self.lock:
            entries = []
            missing = []
            for item in catalog:
                path = Path(item["path"]).resolve()
                if not path.is_relative_to(self.allowed) or not path.is_file():
                    continue
                product_id = int(item["id"])
                fingerprint = hashlib.sha256(path.read_bytes()).hexdigest()
                key = f"{product_id}:{fingerprint}"
                entries.append((product_id, key))
                if key not in self.cache:
                    try:
                        with Image.open(path) as opened:
                            opened.verify()
                        missing.append((key, path))
                    except (OSError, ValueError, Image.DecompressionBombError):
                        entries.pop()
            for start in range(0, len(missing), 8):
                batch = missing[start:start + 8]
                images = []
                for _, path in batch:
                    with Image.open(path) as opened:
                        images.append(ImageOps.exif_transpose(opened).convert("RGB"))
                vectors = self.encode(images)
                self.cache.update((key, vector) for (key, _), vector in zip(batch, vectors))
            active = {key for _, key in entries}
            changed = bool(missing) or active != set(self.cache)
            self.cache = {key: self.cache[key] for key in active}
            self.indexed = len(entries)
            if changed and self.cache:
                temporary = self.cache_file.with_suffix(".tmp")
                with temporary.open("wb") as output:
                    np.savez_compressed(output, model=f"{MODEL}:{WEIGHTS}:{MODEL_REVISION}", keys=np.array(list(self.cache)), vectors=np.stack(list(self.cache.values())))
                temporary.replace(self.cache_file)
            elif changed and self.cache_file.exists():
                self.cache_file.unlink()
            if not entries:
                return {"matches": []}
            query_vector = self.encode([query])[0]
            matrix = np.stack([self.cache[key] for _, key in entries])
            scores = matrix @ query_vector
            ranking = np.argsort(-scores)[:limit]
            cutoff = max(minimum, float(scores.max()) - 0.12)
            return {"matches": [
                {"id": entries[int(i)][0], "similarity": float(np.clip(scores[i], -1, 1))}
                for i in ranking if scores[i] >= cutoff
            ]}


def handler_for(index):
    class Handler(BaseHTTPRequestHandler):
        def respond(self, code, payload):
            body = json.dumps(payload, allow_nan=False).encode("utf-8")
            self.send_response(code)
            self.send_header("Content-Type", "application/json")
            self.send_header("Content-Length", str(len(body)))
            self.send_header("Cache-Control", "no-store")
            self.end_headers()
            self.wfile.write(body)

        def do_GET(self):
            if self.path == "/health":
                self.respond(200, {"ready": True, "model": MODEL, "weights": WEIGHTS, "indexed": index.indexed})
            else:
                self.respond(404, {"error": "Not found"})

        def do_POST(self):
            if not hmac.compare_digest(self.headers.get("Authorization", ""), "Bearer " + index.token):
                self.respond(401, {"error": "Unauthorized"})
                return
            if self.path != "/search":
                self.respond(404, {"error": "Not found"})
                return
            try:
                length = int(self.headers.get("Content-Length", "0"))
                if not 0 < length <= 8 * 1024 * 1024 or self.headers.get_content_type() != "application/json":
                    raise ValueError("Invalid request")
                result = index.search(json.loads(self.rfile.read(length)))
                self.respond(200, result)
            except (ValueError, KeyError, TypeError, OSError, UnidentifiedImageError, Image.DecompressionBombError):
                self.respond(422, {"error": "Invalid image or catalog"})
            except Exception:
                logging.exception("Image retrieval failed")
                self.respond(503, {"error": "Image search unavailable"})

    return Handler


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--root", type=Path, default=Path(__file__).resolve().parents[2])
    parser.add_argument("--port", type=int, default=8020)
    args = parser.parse_args()
    logging.basicConfig(level=logging.INFO, format="%(levelname)s %(message)s")
    engine = ImageIndex(args.root)
    server = ThreadingHTTPServer(("127.0.0.1", args.port), handler_for(engine))
    logging.info("Listening on http://127.0.0.1:%d", args.port)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        server.server_close()
