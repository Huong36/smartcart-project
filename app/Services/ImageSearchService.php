<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageSearchService
{
    /** @return list<array{product: Product, similarity: float}> */
    public function search(UploadedFile $image): array
    {
        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        $catalog = [];
        $products = Product::with('category')->whereNotNull('image')->get()->keyBy('id');

        foreach ($products as $product) {
            $path = realpath($disk->path($product->image));
            if (! $root || ! $path || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR) || ! is_file($path)) {
                continue;
            }
            $catalog[] = ['id' => $product->id, 'path' => $path, 'hash' => hash_file('sha256', $path)];
        }

        if ($catalog === []) {
            return [];
        }

        $tokenFile = storage_path('app/private/visual-search/token');
        $token = config('visual-search.token') ?: (is_file($tokenFile) ? trim(file_get_contents($tokenFile)) : '');
        if ($token === '') {
            throw new RuntimeException('Image search engine is not running.');
        }

        $response = Http::connectTimeout(3)->timeout(config('visual-search.timeout'))
            ->withToken($token)->post(rtrim(config('visual-search.url'), '/').'/search', [
                'image' => base64_encode($image->getContent()),
                'catalog' => $catalog,
                'minimum_similarity' => config('visual-search.minimum_similarity'),
                'limit' => 8,
            ]);
        $response->throw();
        $matches = $response->json('matches');
        if (! is_array($matches)) {
            throw new RuntimeException('Invalid image search response.');
        }

        $results = [];
        $seen = [];
        foreach (array_slice($matches, 0, 8) as $match) {
            $id = $match['id'] ?? null;
            $score = $match['similarity'] ?? null;
            if (! is_numeric($id) || ! is_numeric($score) || ! is_finite((float) $score) || isset($seen[$id]) || ! $products->has($id)) {
                continue;
            }
            if ((float) $score < config('visual-search.minimum_similarity') || (float) $score > 1.0001) {
                continue;
            }
            $seen[$id] = true;
            $results[] = ['product' => $products[$id], 'similarity' => (float) $score];
        }
        usort($results, fn (array $a, array $b): int => $b['similarity'] <=> $a['similarity']);

        return $results;
    }
}
