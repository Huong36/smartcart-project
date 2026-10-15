@extends('layouts.admin')
@section('title', 'Thêm Sản phẩm mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="text-secondary text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Quay lại danh sách</span>
    </a>
    <h1 class="h3 fw-bold mb-1">Thêm Sản phẩm mới</h1>
    <p class="text-secondary mb-0">Nhập đầy đủ thông tin chi tiết và tải ảnh minh họa cho sản phẩm.</p>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf

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
                               value="{{ old('name') }}"
                               placeholder="Ví dụ: Tai nghe không dây Sony WH-1000XM5"
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
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                                       value="{{ old('price') }}"
                                       placeholder="Ví dụ: 490000"
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
                               min="1"
                               step="1"
                               class="form-control @error('stock') is-invalid @enderror"
                               id="stock"
                               name="stock"
                               value="{{ old('stock', 20) }}"
                               placeholder="Ví dụ: 20"
                               required>
                        <div class="form-text">Số lượng sản phẩm ban đầu phải lớn hơn 0.</div>
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
                                  rows="5"
                                  placeholder="Mô tả thông số kỹ thuật, tính năng nổi bật hoặc chất liệu sản phẩm...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-4 shadow-sm border-0">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary px-4">Lưu sản phẩm</button>
            </div>
        </div>

        <!-- Sidebar / Media Column -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-dark">Ảnh đại diện sản phẩm</h5>

                    <div class="mb-3 text-center">
                        <div id="imagePreviewContainer" class="border rounded-3 p-2 bg-light mb-2 d-flex align-items-center justify-content-center" style="min-height: 200px;">
                            <img id="imagePreview" src="{{ asset('images/product-placeholder.svg') }}" alt="Xem trước ảnh" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;">
                        </div>
                        <small class="text-secondary" id="previewNote">Xem trước ảnh sản phẩm</small>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label fw-semibold">Tải ảnh lên</label>
                        <input class="form-control @error('image') is-invalid @enderror"
                               type="file"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Định dạng hỗ trợ: <strong>jpeg, png, webp</strong>. Tối đa 5MB.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-light p-3">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-2">Quy tắc dữ liệu</h6>
                    <ul class="small text-secondary ps-3 mb-0">
                        <li class="mb-2">Tên sản phẩm bắt buộc phải có.</li>
                        <li class="mb-2">Giá và số lượng tồn kho phải lớn hơn 0.</li>
                        <li class="mb-2">Ảnh sản phẩm được lưu tự động vào thư mục <code>storage/app/public/products</code>.</li>
                        <li>Nếu không tải ảnh, hệ thống sẽ sử dụng ảnh giữ chỗ (placeholder) mặc định.</li>
                    </ul>
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
                    document.getElementById('previewNote').textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
