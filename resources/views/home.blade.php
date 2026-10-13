@extends('layouts.app')
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
    <div class="row g-4">
    @forelse($products as $product)
        <div class="col-6 col-lg-3"><article class="card product-card h-100"><img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" loading="lazy"><div class="card-body"><p class="small text-secondary mb-2">{{ $product->category->name }}</p><h3 class="h6 fw-semibold">{{ $product->name }}</h3><p class="fw-bold price mb-0">{{ number_format((float) $product->price, 0, ',', '.') }} ₫</p></div></article></div>
    @empty
        <p class="text-secondary">Chưa có sản phẩm. Vui lòng quay lại sau.</p>
    @endforelse
    </div>
</section>
@endsection
