<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SmartCart') · SmartCart</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="storefront-body">
<a class="visually-hidden-focusable p-3" href="#main-content">Đến nội dung chính</a>
<div class="store-announcement"><div class="container"><span>SMART CHOICES. EVERY DAY.</span><span>Thanh toán COD <span aria-hidden="true">·</span> Miễn phí giao hàng</span></div></div>
<header class="store-header">
    <nav class="navbar navbar-expand-xl container py-3" aria-label="Điều hướng chính">
        <a class="navbar-brand brand" href="{{ route('home') }}"> SmartCart<span class="brand-dot">.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="main-nav">
            {{-- Thanh tìm kiếm trực tiếp (Live Search) --}}
            <div class="search-wrapper mx-md-3 my-2 my-md-0 flex-grow-1" style="max-width: 420px;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted" id="search-addon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                        </svg>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" id="navbar-live-search" placeholder="Tìm sản phẩm (tối thiểu 2 ký tự)..." aria-label="Tìm kiếm sản phẩm" aria-describedby="search-addon" autocomplete="off">
                </div>
                {{-- Dropdown kết quả gợi ý Live Search --}}
                <div id="navbar-search-dropdown" class="search-dropdown d-none" role="region" aria-live="polite"></div>
            </div>

            <div class="navbar-nav ms-auto align-items-md-center gap-md-2">
                <a class="nav-link" href="{{ route('home') }}">Trang chủ</a>

                {{-- Nút Giỏ hàng với Badge số lượng --}}
                @php
                    $navCartCount = array_sum(array_map('intval', session('cart', [])));
                @endphp
                <a class="nav-link cart-nav-link d-inline-flex align-items-center gap-1" href="{{ route('cart.index') }}" aria-label="Giỏ hàng">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <span>Giỏ hàng</span>
                    <span id="navbar-cart-badge" class="cart-badge-pill {{ $navCartCount > 0 ? '' : 'd-none' }}">{{ $navCartCount }}</span>
                </a>

                @auth
                    <a class="nav-link" href="{{ route('orders.index') }}">Đơn hàng của tôi</a>
                    @if(auth()->user()->isAdmin())
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Quản trị</a>
                    @endif
                    <a class="nav-link" href="{{ route('account') }}">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm" type="submit">Đăng xuất</button></form>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Tạo tài khoản</a>
                @endauth
            </div>
        </div>
    </nav>
</header>
<main id="main-content" class="container store-main">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @yield('content')
</main>
<footer class="store-footer mt-auto"><div class="container">
    <div class="footer-main"><div><a class="brand" href="{{ route('home') }}"> SmartCart<span class="brand-dot">.</span></a><p>Những lựa chọn nhỏ.<br>Niềm vui cho mỗi ngày.</p></div><div><h2>Khám phá</h2><a href="{{ route('home') }}#products-heading">Tất cả sản phẩm</a><a href="{{ route('cart.index') }}">Giỏ hàng của bạn</a></div><div><h2>Tài khoản</h2>@auth<a href="{{ route('account') }}">Thông tin cá nhân</a><a href="{{ route('orders.index') }}">Đơn hàng của tôi</a>@else<a href="{{ route('login') }}">Đăng nhập</a><a href="{{ route('register') }}">Tạo tài khoản</a>@endauth</div><div class="footer-payment"><span class="footer-payment-icon" aria-hidden="true">✓</span><h2>Mua sắm thật đơn giản</h2><p>Thanh toán khi nhận hàng.<br>Miễn phí giao hàng.</p></div></div>
    {{-- Widget thời tiết TP.HCM (Open-Meteo API) --}}
    <div class="mb-3">
        <div class="weather-footer-box d-flex justify-content-between align-items-center flex-wrap gap-2" id="hcm-weather-widget">
            <div class="text-secondary small d-flex align-items-center gap-2">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>Đang tải thông tin thời tiết TP. Hồ Chí Minh...</span>
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap justify-content-between gap-2 text-secondary small">
        <span class="fw-semibold">SmartCart</span><span>© {{ date('Y') }} · Mua sắm theo cách của bạn</span>
    </div>
</div></footer>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('vendor/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('js/cart.js') }}" defer></script>
<script src="{{ asset('js/search.js') }}" defer></script>
<script src="{{ asset('js/weather.js') }}" defer></script>
@if(session('order_success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.SmartCartNotifyOrderSuccess) {
            window.SmartCartNotifyOrderSuccess(@json(session('order_id')), @json(session('order_total')));
        }
    });
</script>
@endif
@stack('scripts')
</body>
</html>
