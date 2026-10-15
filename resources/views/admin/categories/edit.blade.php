@extends('layouts.admin')
@section('title', 'Chỉnh sửa Danh mục: ' . $category->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="text-secondary text-decoration-none small d-inline-flex align-items-center gap-1 mb-2">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Quay lại danh sách</span>
    </a>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Chỉnh sửa Danh mục</h1>
            <p class="text-secondary mb-0">Cập nhật thông tin chi tiết cho danh mục <span class="fw-semibold text-dark">"{{ $category->name }}"</span></p>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
            Đang có {{ $category->products()->count() }} sản phẩm
        </span>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $category->name) }}"
                               required
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label fw-semibold">Đường dẫn định danh (Slug) <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('slug') is-invalid @enderror"
                               id="slug"
                               name="slug"
                               value="{{ old('slug', $category->slug) }}"
                               required>
                        <div class="form-text">Đường dẫn thân thiện URL cho danh mục, không chứa dấu cách hay ký tự đặc biệt.</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Mô tả danh mục</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-4">Cập nhật danh mục</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card border-0 shadow-sm rounded-4 bg-light p-3">
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-2">Thông tin danh mục</h6>
                <p class="small text-secondary mb-1">Mã định danh (ID): <strong>#{{ $category->id }}</strong></p>
                <p class="small text-secondary mb-1">Ngày tạo: {{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                <p class="small text-secondary mb-3">Cập nhật lần cuối: {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>

                @if($category->products()->count() > 0)
                    <div class="alert alert-warning py-2 px-3 small mb-0 border-0">
                        <strong>Chú ý:</strong> Danh mục này đang chứa {{ $category->products()->count() }} sản phẩm, không thể xóa cho tới khi danh mục trống.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
