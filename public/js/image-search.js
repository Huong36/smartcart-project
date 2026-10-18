(() => {
    'use strict';
    const form = document.getElementById('image-search-form');
    if (!form) return;
    const fileInput = document.getElementById('image-search-file');
    const cameraFile = document.getElementById('image-camera-file');
    const preview = document.getElementById('image-query-preview');
    const placeholder = document.getElementById('image-preview-placeholder');
    const submit = document.getElementById('find-by-image');
    const choose = document.getElementById('choose-image');
    const camera = document.getElementById('open-camera');
    const panel = document.getElementById('image-camera-panel');
    const video = document.getElementById('image-camera-video');
    const capture = document.getElementById('capture-image');
    const status = document.getElementById('image-search-status');
    const error = document.getElementById('image-search-error');
    const results = document.getElementById('image-search-results');
    const region = document.querySelector('.image-search-results');
    let selected = null;
    let previewUrl = null;
    let stream = null;
    let pending = null;
    let cameraRequest = 0;
    let pageActive = true;

    function showError(message) {
        error.textContent = message;
        error.hidden = !message;
    }
    function closeCamera() {
        cameraRequest++;
        if (stream) stream.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
        panel.hidden = true;
    }
    function selectImage(file) {
        if (!file) return;
        if (pending) pending.abort();
        closeCamera();
        results.replaceChildren();
        showError('');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        selected = null;
        preview.hidden = true;
        placeholder.hidden = false;
        submit.disabled = true;
        status.textContent = 'Chọn ảnh để bắt đầu tìm kiếm.';
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            showError('Hãy chọn ảnh JPG, PNG hoặc WebP.');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            showError('Vui lòng chọn ảnh không quá 2 MB.');
            return;
        }
        selected = file;
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
        placeholder.hidden = true;
        submit.disabled = false;
        status.textContent = 'Ảnh đã sẵn sàng. Bấm “Tìm sản phẩm” để tiếp tục.';
    }
    choose.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => selectImage(fileInput.files[0]));
    cameraFile.addEventListener('change', () => selectImage(cameraFile.files[0]));
    preview.addEventListener('error', () => {
        selected = null;
        submit.disabled = true;
        preview.hidden = true;
        placeholder.hidden = false;
        showError('Không đọc được ảnh này. Bạn hãy chọn ảnh khác.');
    });
    camera.addEventListener('click', async () => {
        showError('');
        closeCamera();
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            if (navigator.maxTouchPoints > 0) cameraFile.click();
            else showError('Camera cần HTTPS hoặc localhost. Bạn vẫn có thể dùng “Chọn ảnh”.');
            return;
        }
        camera.disabled = true;
        const requestNumber = cameraRequest;
        try {
            const acquired = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            if (!pageActive || requestNumber !== cameraRequest) {
                acquired.getTracks().forEach((track) => track.stop());
                return;
            }
            stream = acquired;
            video.srcObject = stream;
            panel.hidden = false;
            capture.disabled = true;
            video.onloadedmetadata = () => { capture.disabled = false; };
            await video.play();
        } catch (e) {
            closeCamera();
            showError(e.name === 'NotAllowedError' ? 'Chưa được cấp quyền camera. Bạn có thể chọn ảnh có sẵn.' : 'Không mở được camera. Bạn có thể chọn ảnh có sẵn.');
        } finally {
            camera.disabled = false;
        }
    });
    document.getElementById('close-camera').addEventListener('click', closeCamera);
    capture.addEventListener('click', () => {
        if (!video.videoWidth) return;
        const canvas = document.createElement('canvas');
        const ratio = Math.min(1, 1600 / Math.max(video.videoWidth, video.videoHeight));
        canvas.width = Math.round(video.videoWidth * ratio);
        canvas.height = Math.round(video.videoHeight * ratio);
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => {
            if (blob) selectImage(new File([blob], 'camera.jpg', { type: 'image/jpeg' }));
            else showError('Không chụp được ảnh. Bạn hãy thử lại.');
        }, 'image/jpeg', 0.85);
    });

    function renderProducts(products) {
        const currency = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });
        products.forEach((product) => {
            const column = document.createElement('div');
            column.className = 'col-6 col-xl-4';
            const card = document.createElement('article');
            card.className = 'card product-card h-100';
            const imageLink = document.createElement('a');
            imageLink.href = product.url;
            imageLink.className = 'product-image-link';
            const image = document.createElement('img');
            image.src = product.image_url;
            image.alt = product.name;
            image.loading = 'lazy';
            imageLink.append(image);
            const body = document.createElement('div');
            body.className = 'card-body d-flex flex-column';
            const category = document.createElement('p');
            category.className = 'product-category';
            category.textContent = product.category;
            const title = document.createElement('h3');
            const link = document.createElement('a');
            link.href = product.url;
            link.textContent = product.name;
            title.append(link);
            const price = document.createElement('p');
            price.className = 'price mt-auto';
            price.textContent = currency.format(Number(product.price));
            const add = document.createElement('button');
            add.type = 'button';
            add.className = 'btn btn-primary btn-sm btn-add-to-cart mt-3';
            add.dataset.productId = product.id;
            add.dataset.quantity = '1';
            add.disabled = product.stock < 1;
            add.textContent = product.stock > 0 ? 'Thêm vào giỏ' : 'Hết hàng';
            add.setAttribute('aria-label', 'Thêm ' + product.name + ' vào giỏ');
            body.append(category, title, price, add);
            card.append(imageLink, body);
            column.append(card);
            results.append(column);
        });
    }
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!selected || pending) return;
        closeCamera();
        pending = new AbortController();
        const timeout = setTimeout(() => pending?.abort(), 125000);
        results.replaceChildren();
        showError('');
        submit.disabled = choose.disabled = camera.disabled = true;
        submit.textContent = 'Đang tìm…';
        region.setAttribute('aria-busy', 'true');
        status.textContent = 'Đang so sánh ảnh với sản phẩm. Lần tìm đầu tiên có thể lâu hơn.';
        const data = new FormData();
        data.append('image', selected);
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: data, signal: pending.signal,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value }
            });
            const payload = await response.json();
            if (!response.ok) {
                const message = payload.errors ? Object.values(payload.errors).flat()[0] : payload.message;
                throw Object.assign(new Error(), { userMessage: response.status === 429 ? 'Bạn tìm quá nhiều lần. Hãy thử lại sau một phút.' : (response.status === 419 ? 'Phiên đã hết hạn. Hãy tải lại trang rồi thử lại.' : message) });
            }
            renderProducts(payload.products);
            status.textContent = payload.products.length ? `Tìm thấy ${payload.products.length} sản phẩm có hình ảnh gần giống. Bạn hãy kiểm tra tên và thông tin trước khi mua.` : payload.message;
        } catch (e) {
            status.textContent = 'Chưa có kết quả.';
            showError(e.name === 'AbortError' ? 'Tìm kiếm mất nhiều thời gian. Bạn hãy thử lại.' : (e.userMessage || 'Không thể tìm kiếm lúc này. Bạn hãy thử lại.'));
        } finally {
            clearTimeout(timeout);
            pending = null;
            submit.disabled = !selected;
            choose.disabled = camera.disabled = false;
            submit.textContent = 'Tìm sản phẩm';
            region.setAttribute('aria-busy', 'false');
        }
    });
    window.addEventListener('pageshow', () => { pageActive = true; });
    window.addEventListener('pagehide', () => {
        pageActive = false;
        closeCamera();
        if (pending) pending.abort();
        if (previewUrl) URL.revokeObjectURL(previewUrl);
    });
})();
