@extends('layouts.admin')
@section('title', 'Chỉnh sửa Sản phẩm: ' . $product->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="text-secondary text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Quay lại danh sách</span>
    </a>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Chỉnh sửa Sản phẩm</h1>
            <p class="text-secondary mb-0">Cập nhật thông tin chi tiết cho sản phẩm <span class="fw-semibold text-dark">"{{ $product->name }}"</span></p>
        </div>
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
            Mã sản phẩm: #{{ $product->id }}
        </span>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Thông tin cơ bản</h5>

                    <!-- Product Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $product->name) }}"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category & Price -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label fw-semibold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input type="number"
                                       step="any"
                                       min="1"
                                       class="form-control @error('price') is-invalid @enderror"
                                       id="price"
                                       name="price"
                                       value="{{ old('price', (int) $product->price) }}"
                                       required>
                                <span class="input-group-text bg-light text-secondary">₫</span>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Giá phải lớn hơn 0 VNĐ.</div>
                        </div>
                    </div>

                    <!-- Stock Quantity -->
                    <div class="mb-3">
                        <label for="stock" class="form-label fw-semibold">Số lượng trong kho <span class="text-danger">*</span></label>
                        <input type="number"
                               min="0"
                               step="1"
                               class="form-control @error('stock') is-invalid @enderror"
                               id="stock"
                               name="stock"
                               value="{{ old('stock', $product->stock) }}"
                               required>
                        <div class="form-text">Số lượng sản phẩm trong kho phải lớn hơn hoặc bằng 0.</div>
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-2">
                        <label for="description" class="form-label fw-semibold">Mô tả sản phẩm</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-4 shadow-sm border-0">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary px-4">Cập nhật sản phẩm</button>
            </div>
        </div>

        <!-- Sidebar / Media Column -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Ảnh đại diện sản phẩm</h5>

                    <div class="mb-3 text-center">
                        <div id="imagePreviewContainer" class="border rounded-3 p-2 bg-light mb-2 d-flex align-items-center justify-content-center" style="min-height: 200px;">
                            <img id="imagePreview" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;">
                        </div>
                        <small class="text-secondary" id="previewNote">
                            {{ $product->image ? 'Ảnh hiện tại: ' . basename($product->image) : 'Đang dùng ảnh mặc định' }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">Thay đổi ảnh mới</label>
                        <input class="form-control @error('image') is-invalid @enderror"
                               type="file"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Chọn tệp ảnh mới nếu muốn thay đổi ảnh hiện tại. Định dạng: <strong>jpeg, png, webp</strong>.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-light p-3">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-2">Thống kê sản phẩm</h6>
                    <p class="small text-secondary mb-1">Mã sản phẩm: <strong>#{{ $product->id }}</strong></p>
                    <p class="small text-secondary mb-1">Ngày tạo: {{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    <p class="small text-secondary mb-3">Cập nhật lần cuối: {{ $product->updated_at ? $product->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>

                    @if($product->orderItems()->exists())
                        <div class="alert alert-info py-2 px-3 small mb-0 border-0">
                            <strong>Đã có đơn hàng:</strong> Sản phẩm này đã phát sinh giao dịch trong đơn hàng của khách và không thể xóa để bảo toàn lịch sử hóa đơn.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('image')?.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                const preview = document.getElementById('imagePreview');
                if (preview) {
                    preview.src = event.target.result;
                    document.getElementById('previewNote').textContent = 'Ảnh mới chọn: ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
