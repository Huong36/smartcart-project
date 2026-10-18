@extends('client')
@section('title', $product->name)
@section('content')
<nav aria-label="Đường dẫn" class="product-breadcrumb"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><a href="{{ route('home', ['category_id' => $product->category_id]) }}#products-heading">{{ $product->category->name }}</a><span>/</span><span>{{ $product->name }}</span></nav>
<div class="product-detail">
    <div class="product-detail-image"><span class="detail-image-tag">SMARTCART COLLECTION</span><img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="600" height="600"><span class="detail-image-symbol" aria-hidden="true">✳</span></div>
    <div class="product-detail-info">
        <p class="eyebrow">{{ $product->category->name }}</p><h1>{{ $product->name }}</h1>
        <p class="detail-stock {{ $product->stock > 0 ? '' : 'is-sold-out' }}"><span aria-hidden="true">●</span> {{ $product->stock > 0 ? 'Còn '.$product->stock.' sản phẩm' : 'Sản phẩm tạm hết hàng' }} <span class="detail-product-id">Mã SP: #{{ $product->id }}</span></p>
        <p class="detail-price">{{ number_format((float) $product->price, 0, ',', '.') }} <span>₫</span></p>
        <p class="detail-description">{{ $product->description ?: 'Cửa hàng đang cập nhật mô tả sản phẩm.' }}</p>
        <div class="detail-purchase"><button type="button" class="btn btn-primary btn-add-to-cart" data-product-id="{{ $product->id }}" data-quantity="1" @disabled($product->stock < 1)><span aria-hidden="true">+</span> Thêm vào giỏ</button><a href="{{ route('cart.index') }}" class="btn btn-outline-secondary">Xem giỏ hàng <span aria-hidden="true">↗</span></a></div>
        <div class="detail-service"><span aria-hidden="true">✓</span><div><strong>Nhận hàng rồi thanh toán</strong><p>Thanh toán COD. Miễn phí giao hàng.</p></div></div>
        <div class="detail-service"><span aria-hidden="true">▤</span><div><strong>Dễ dàng theo dõi đơn hàng</strong><p>Xem thông tin và trạng thái đơn trong tài khoản của bạn.</p></div></div>
    </div>
</div>
<a class="detail-back" href="{{ route('home') }}#products-heading">← Tiếp tục khám phá sản phẩm</a>
@endsection
