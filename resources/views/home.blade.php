@extends('client')
@section('title', 'Trang chủ')
@section('content')
<section class="store-hero" aria-labelledby="hero-title">
    <div class="store-hero-copy">
        <p class="eyebrow"><span class="eyebrow-dot"></span> MỘT CHÚT MỚI MẺ CHO MỖI NGÀY</p>
        <h1 id="hero-title">Chọn điều bạn thích.<br><em>Sống theo cách bạn.</em></h1>
        <p class="hero-description">Từ góc làm việc đến những phút thư giãn. Khám phá những món đồ cho cuộc sống của riêng bạn.</p>
        <div class="hero-actions"><a href="#products-heading" class="btn btn-primary btn-lg">Khám phá sản phẩm <span aria-hidden="true">↗</span></a><a href="{{ route('cart.index') }}" class="hero-secondary">Giỏ hàng của bạn <span aria-hidden="true">→</span></a></div>
        <div class="hero-note"><span class="hero-note-icon" aria-hidden="true">✓</span> Thanh toán khi nhận hàng · Miễn phí giao hàng</div>
    </div>
    <div class="store-hero-visual"><img src="{{ asset('images/storefront-hero-v2.png') }}" alt="Túi vải, tai nghe và đồ dùng cho cuộc sống mỗi ngày" fetchpriority="high" width="1536" height="1024"><div class="hero-image-label"><span class="hero-label-symbol" aria-hidden="true">✳</span><div>Những điều giản đơn.<strong>Thêm cảm hứng mỗi ngày.</strong></div></div></div>
</section>
<div class="store-benefits" aria-label="Thông tin mua sắm">
    <div><span class="benefit-icon" aria-hidden="true">▦</span><span><strong>Nhiều lựa chọn, một nơi</strong><small>{{ $categories->count() }} danh mục để khám phá</small></span></div>
    <div><span class="benefit-icon" aria-hidden="true">↗</span><span><strong>Mua sắm theo cách của bạn</strong><small>Tìm kiếm và thêm vào giỏ dễ dàng</small></span></div>
    <div><span class="benefit-icon" aria-hidden="true">✓</span><span><strong>Nhận hàng rồi thanh toán</strong><small>COD và miễn phí giao hàng</small></span></div>
</div>
<section class="catalog-section" aria-labelledby="products-heading">
    <div class="section-heading"><div><p class="eyebrow">KHÁM PHÁ SMARTCART</p><h2 id="products-heading">Sản phẩm mới<span class="heading-dot">.</span></h2></div><p>Những lựa chọn cho một ngày trọn vẹn hơn.</p></div>
    <nav class="category-strip" aria-label="Danh mục sản phẩm">
        <a class="category-chip {{ ! request('category_id') ? 'is-active' : '' }}" href="{{ route('home', request()->only('q', 'sort')) }}#products-heading"><span aria-hidden="true">▦</span> Tất cả sản phẩm</a>
        @foreach($categories as $category)
            <a class="category-chip {{ request('category_id') == $category->id ? 'is-active' : '' }}" href="{{ route('home', array_merge(request()->only('q', 'sort'), ['category_id' => $category->id])) }}#products-heading">{{ $category->name }}</a>
        @endforeach
    </nav>
    <form method="GET" action="{{ route('home') }}#products-heading" class="catalog-filters">
        <div class="catalog-query"><label for="catalog-query" class="form-label">Tìm sản phẩm</label><input id="catalog-query" class="form-control" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Bạn đang tìm món đồ nào?"></div>
        <div><label for="catalog-category" class="form-label">Danh mục</label><select id="catalog-category" name="category_id" class="form-select"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div><label for="catalog-sort" class="form-label">Sắp xếp</label><select id="catalog-sort" name="sort" class="form-select"><option value="newest" @selected(request('sort', 'newest') === 'newest')>Mới nhất</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option></select></div>
        <div class="catalog-filter-actions"><button class="btn btn-primary" type="submit">Lọc</button><a href="{{ route('home') }}#products-heading" class="btn btn-outline-secondary">Bỏ lọc</a></div>
    </form>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="catalog-result-line"><p>Tìm thấy <strong>{{ $products->total() }}</strong> sản phẩm</p><span class="catalog-view-label"><span aria-hidden="true">▦</span> Lựa chọn dành cho bạn</span></div>
    <div class="row g-3 g-lg-4">
    @forelse($products as $product)
        <div class="col-6 col-lg-3"><article class="card product-card h-100">
            <a class="product-image-link" href="{{ route('products.show', $product) }}"><img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" loading="lazy" width="400" height="400"><span class="product-availability {{ $product->stock < 1 ? 'is-sold-out' : '' }}">{{ $product->stock > 0 ? 'Còn hàng' : 'Tạm hết hàng' }}</span><span class="product-view-icon" aria-hidden="true">↗</span></a>
            <div class="card-body d-flex flex-column"><p class="product-category">{{ $product->category->name }}</p><h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3><div class="product-card-bottom mt-auto"><p class="price">{{ number_format((float) $product->price, 0, ',', '.') }} <span>₫</span></p><button type="button" class="btn btn-primary btn-sm btn-add-to-cart" data-product-id="{{ $product->id }}" data-quantity="1" @disabled($product->stock < 1) aria-label="Thêm {{ $product->name }} vào giỏ"><span aria-hidden="true">+</span> {{ $product->stock > 0 ? 'Thêm vào giỏ' : 'Hết hàng' }}</button></div></div>
        </article></div>
    @empty
        <div class="col-12"><div class="catalog-empty"><span aria-hidden="true">⌕</span><h3>Chưa tìm thấy món đồ bạn cần</h3><p>Không có sản phẩm phù hợp. Bạn thử đổi bộ lọc nhé.</p><a href="{{ route('home') }}#products-heading" class="btn btn-primary">Xem tất cả sản phẩm</a></div></div>
    @endforelse
    </div>
    <div class="catalog-pagination">{{ $products->links() }}</div>
</section>
<section class="store-closing"><span class="closing-symbol" aria-hidden="true">✳</span><div><p class="eyebrow">SMALL CHOICES. EVERYDAY JOY.</p><h2>Chút cảm hứng mới,<br>cho những điều thường ngày.</h2></div><a href="#products-heading" class="btn btn-light">Tiếp tục khám phá <span aria-hidden="true">↗</span></a></section>
@endsection
