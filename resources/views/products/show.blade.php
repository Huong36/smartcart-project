@extends('client')
@section('title', $product->name)
@section('content')
<nav aria-label="Đường dẫn" class="mb-4"><a href="{{ route('home') }}">Sản phẩm</a> / {{ $product->name }}</nav>
<div class="row g-4 g-lg-5 align-items-start">
    <div class="col-md-6"><img src="{{ $product->image_url }}" class="img-fluid rounded-4 w-100 border bg-white" style="aspect-ratio: 1; object-fit: contain;" alt="{{ $product->name }}"></div>
    <div class="col-md-6">
        <p class="eyebrow">{{ $product->category->name }}</p><h1 class="h2 fw-bold">{{ $product->name }}</h1>
        <p class="price fs-3 fw-bold my-4">{{ number_format((float) $product->price, 0, ',', '.') }} ₫</p>
        <p class="{{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">{{ $product->stock > 0 ? 'Còn '.$product->stock.' sản phẩm' : 'Sản phẩm tạm hết hàng' }}</p>
        <p style="white-space: pre-line;">{{ $product->description ?: 'Cửa hàng đang cập nhật mô tả sản phẩm.' }}</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="button" class="btn btn-primary btn-add-to-cart px-4 py-3" data-product-id="{{ $product->id }}" data-quantity="1" @disabled($product->stock < 1)>Thêm vào giỏ</button>
            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary px-4 py-3">Xem giỏ hàng</a>
        </div>
    </div>
</div>
@endsection
