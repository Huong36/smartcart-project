@extends('client')
@section('title', 'Trang chủ')
@section('content')
<section class="hero mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-8"><p class="eyebrow">CHỌN ĐIỀU BẠN THÍCH</p><h1 class="display-4 fw-bold mb-3">Mua sắm thông minh.<br>Thêm vui mỗi ngày.</h1><p class="text-secondary fs-5 mb-4">Khám phá những lựa chọn cho công việc, cuộc sống và những điều bạn yêu thích.</p>@guest<a href="{{ route('register') }}" class="btn btn-primary px-4 py-3">Tham gia SmartCart →</a>@else<a href="{{ route('account') }}" class="btn btn-primary px-4 py-3">Tài khoản của tôi →</a>@endguest</div>
        <div class="col-lg-4 d-none d-lg-block"><div class="hero-symbol" aria-hidden="true">S<span>+</span></div></div>
    </div>
</section>
<section aria-labelledby="products-heading">
    <div class="d-flex justify-content-between align-items-baseline mb-4"><h2 id="products-heading" class="h3 fw-bold mb-0">Sản phẩm mới</h2><span class="small text-secondary">Gợi ý dành cho bạn</span></div>
    <form method="GET" action="{{ route('home') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4"><label for="catalog-query" class="form-label">Tìm sản phẩm</label><input id="catalog-query" class="form-control" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Tên sản phẩm..."></div>
        <div class="col-md-3"><label for="catalog-category" class="form-label">Danh mục</label><select id="catalog-category" name="category_id" class="form-select"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label for="catalog-sort" class="form-label">Sắp xếp</label><select id="catalog-sort" name="sort" class="form-select"><option value="newest" @selected(request('sort', 'newest') === 'newest')>Mới nhất</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option></select></div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Lọc</button><a href="{{ route('home') }}" class="btn btn-outline-secondary">Bỏ lọc</a></div>
    </form>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <p class="text-secondary small">Tìm thấy {{ $products->total() }} sản phẩm</p>
    <div class="row g-4">
    @forelse($products as $product)
        <div class="col-6 col-lg-3"><article class="card product-card h-100"><a href="{{ route('products.show', $product) }}"><img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" loading="lazy"></a><div class="card-body d-flex flex-column"><p class="small text-secondary mb-2">{{ $product->category->name }}</p><h3 class="h6 fw-semibold"><a class="text-decoration-none" href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3><p class="fw-bold price">{{ number_format((float) $product->price, 0, ',', '.') }} ₫</p><button type="button" class="btn btn-primary btn-sm btn-add-to-cart mt-auto" data-product-id="{{ $product->id }}" data-quantity="1" @disabled($product->stock < 1)>{{ $product->stock > 0 ? 'Thêm vào giỏ' : 'Hết hàng' }}</button></div></article></div>
    @empty
        <p class="text-secondary">Không có sản phẩm phù hợp. Bạn thử đổi bộ lọc nhé.</p>
    @endforelse
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</section>
@endsection
