@extends('layouts.admin')
@section('title', 'Quản lý Sản phẩm')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <p class="eyebrow mb-1">QUẢN TRỊ SẢN PHẨM</p>
        <h1 class="h3 fw-bold mb-0">Danh sách Sản phẩm</h1>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        <span>Thêm sản phẩm mới</span>
    </a>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5 col-lg-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-secondary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên sản phẩm, mô tả..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-md-4 col-lg-4">
                <select name="category_id" class="form-select">
                    <option value="">-- Tất cả danh mục --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Lọc dữ liệu</button>
                @if(request('q') || request('category_id'))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card card-table border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Ảnh</th>
                    <th style="min-width: 220px;">Tên sản phẩm</th>
                    <th style="min-width: 150px;">Danh mục</th>
                    <th style="min-width: 130px;">Giá bán</th>
                    <th style="min-width: 110px;" class="text-center">Tồn kho</th>
                    <th style="min-width: 120px;">Ngày tạo</th>
                    <th style="width: 140px;" class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-thumb">
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $product->name }}</div>
                            <span class="text-secondary small">#{{ $product->id }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                {{ $product->category?->name ?? 'Chưa phân loại' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold text-primary">
                                {{ number_format((float) $product->price, 0, ',', '.') }} ₫
                            </span>
                        </td>
                        <td class="text-center">
                            @if($product->stock <= 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                    Hết hàng (0)
                                </span>
                            @elseif($product->stock <= 5)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">
                                    Sắp hết ({{ $product->stock }})
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                    {{ $product->stock }} chiếc
                                </span>
                            @endif
                        </td>
                        <td class="text-secondary small">
                            {{ $product->created_at ? $product->created_at->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa sản phẩm">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="delete-confirm-form" data-name="{{ $product->name }}" data-type="sản phẩm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa sản phẩm">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <div class="mb-2">
                                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <p class="fw-semibold mb-1">Không tìm thấy sản phẩm nào</p>
                            <p class="small text-muted mb-3">Hãy tạo mới sản phẩm hoặc thử từ khóa/bộ lọc khác.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Thêm sản phẩm mới</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
