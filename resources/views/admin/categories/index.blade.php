@extends('layouts.admin')
@section('title', 'Quản lý Danh mục')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="eyebrow mb-1">QUẢN TRỊ DANH MỤC</p>
        <h1 class="h3 fw-bold mb-0">Danh sách Danh mục</h1>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        <span>Thêm danh mục mới</span>
    </a>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9 col-lg-10">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-secondary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên danh mục, mã slug hoặc mô tả..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Tìm kiếm</button>
                @if(request('q'))
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card card-table border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">#ID</th>
                    <th style="min-width: 180px;">Tên danh mục</th>
                    <th style="min-width: 160px;">Slug</th>
                    <th style="min-width: 130px;" class="text-center">Số sản phẩm</th>
                    <th>Mô tả</th>
                    <th style="width: 150px;" class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="text-secondary fw-semibold">#{{ $category->id }}</td>
                        <td>
                            <span class="fw-bold text-dark">{{ $category->name }}</span>
                        </td>
                        <td>
                            <code class="text-secondary bg-light px-2 py-1 rounded small">{{ $category->slug }}</code>
                        </td>
                        <td class="text-center">
                            @if($category->products_count > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                    {{ $category->products_count }} sản phẩm
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                    Trống (0)
                                </span>
                            @endif
                        </td>
                        <td class="text-secondary small">
                            {{ Str::limit($category->description ?? 'Chưa có mô tả', 60) }}
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="delete-confirm-form" data-name="{{ $category->name }}" data-type="danh mục">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa danh mục">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <div class="mb-2">
                                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <p class="fw-semibold mb-1">Không tìm thấy danh mục nào</p>
                            <p class="small text-muted mb-3">Hãy tạo mới danh mục hoặc thử từ khóa tìm kiếm khác.</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Thêm danh mục mới</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
