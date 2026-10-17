@extends('layouts.admin')
@section('title', 'Thêm Danh mục mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="text-secondary text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Quay lại danh sách</span>
    </a>
    <h1 class="h3 fw-bold mb-1">Thêm Danh mục mới</h1>
    <p class="text-secondary mb-0">Tạo nhóm danh mục để phân loại các sản phẩm trong cửa hàng.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Ví dụ: Thiết bị điện tử, Thời trang nam..."
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label fw-semibold">Đường dẫn định danh (Slug)</label>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug') }}"
                               placeholder="tu-dong-tao-neu-de-trong">
                        <div class="form-text">Để trống để hệ thống tự động tạo slug từ tên danh mục.</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Mô tả danh mục</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Mô tả ngắn gọn về nhóm sản phẩm thuộc danh mục này...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-4">Lưu danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4 bg-light p-3">
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-2">Lưu ý khi tạo danh mục</h6>
                <ul class="small text-secondary ps-3 mb-0">
                    <li class="mb-2">Tên danh mục là bắt buộc và nên ngắn gọn, dễ nhớ.</li>
                    <li class="mb-2">Slug phải là duy nhất, không trùng lặp giữa các danh mục.</li>
                    <li class="mb-2">Mỗi danh mục có thể chứa nhiều sản phẩm liên quan.</li>
                    <li>Danh mục đã có sản phẩm sẽ không thể bị xóa cho đến khi chuyển hoặc xóa hết sản phẩm đó.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
