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
<body>
<a class="visually-hidden-focusable p-3" href="#main-content">Đến nội dung chính</a>
<header class="border-bottom bg-white">
    <nav class="navbar navbar-expand-md container py-3" aria-label="Điều hướng chính">
        <a class="navbar-brand brand" href="{{ route('home') }}"><span class="brand-mark">S</span> SmartCart<span class="brand-dot">.</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="main-nav">
            <div class="navbar-nav ms-auto align-items-md-center gap-md-3">
                <a class="nav-link" href="{{ route('home') }}">Trang chủ</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Quản trị</a>
                    @endif
                    <a class="nav-link" href="{{ route('account') }}">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm" type="submit">Đăng xuất</button></form>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                    <a class="btn btn-primary" href="{{ route('register') }}">Tạo tài khoản</a>
                @endauth
            </div>
        </div>
    </nav>
</header>
<main id="main-content" class="container py-4 py-md-5">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @yield('content')
</main>
<footer class="container border-top py-4 mt-auto d-flex flex-wrap justify-content-between gap-2 text-secondary small">
    <span class="fw-semibold">SmartCart</span><span>© {{ date('Y') }} · Mua sắm theo cách của bạn</span>
</footer>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
@stack('scripts')
</body>
</html>
