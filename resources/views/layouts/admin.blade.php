<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') · SmartCart Admin</title>

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
    <!-- Navbar -->
    <header class="admin-navbar sticky-top d-flex align-items-center px-3 px-lg-4">
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" id="sidebarToggle" aria-label="Đóng mở menu">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <a class="brand text-decoration-none" href="{{ route('admin.dashboard') }}">

                <span>SmartCart</span>
                <span class="badge bg-dark-subtle text-dark border ms-1 fs-6 fw-normal py-1 px-2 d-none d-sm-inline-block">Admin</span>
            </a>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-flex align-items-center gap-1" title="Mở trang bán hàng ở tab mới">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span>Xem cửa hàng</span>
            </a>

            <div class="vr d-none d-sm-block my-2 text-secondary"></div>

            <div class="d-flex align-items-center gap-2">
                <div class="admin-user-avatar">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="d-none d-md-block text-start lh-sm me-1">
                    <div class="fw-semibold small text-truncate" style="max-width: 140px;">{{ auth()->user()?->name ?? 'Quản trị viên' }}</div>
                    <span class="text-secondary" style="font-size: 0.72rem;">Quản trị viên</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" type="submit" title="Đăng xuất khỏi hệ thống">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span class="d-none d-md-inline">Đăng xuất</span>
                </button>
            </form>
        </div>
    </header>

    <!-- App Wrapper with Sidebar and Main Content -->
    <div class="admin-wrapper">
        <!-- Sidebar Backdrop for Mobile -->
        <div class="admin-sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="d-lg-none d-flex align-items-center justify-content-between p-3 border-bottom border-secondary border-opacity-25">
                <span class="fw-bold">Menu Quản trị</span>
                <button type="button" class="btn-close" id="sidebarCloseBtn" aria-label="Đóng"></button>
            </div>

            <div class="admin-nav-heading">Quản trị</div>
            <ul class="admin-nav">
                <li class="admin-nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Tổng quan</span>
                    </a>
                </li>
            </ul>


            <ul class="admin-nav">
                <li class="admin-nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Danh mục sản phẩm</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="{{ route('admin.products.index') }}" class="admin-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span>Sản phẩm</span>
                    </a>
                </li>
            </ul>


            <ul class="admin-nav"><li class="admin-nav-item"><a href="{{ route('admin.orders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path d="M7 4h10a2 2 0 012 2v14l-4-2-3 2-3-2-4 2V6a2 2 0 012-2zM9 8h6M9 12h6"></path></svg><span>Quản lý đơn hàng</span></a></li></ul>
            <div class="admin-nav-heading">Hệ thống</div>
            <ul class="admin-nav pb-3">
                <li class="admin-nav-item">
                    <a href="{{ route('home') }}" class="admin-nav-link">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Về trang chủ</span>
                    </a>
                </li>
                <li class="admin-nav-item">
                    <a href="{{ route('account') }}" class="admin-nav-link">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Tài khoản cá nhân</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Workspace -->
        <main class="admin-main">
            <!-- Flash Message Alerts -->
            @include('partials.flash-toast')

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/flash.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');
            const closeBtn = document.getElementById('sidebarCloseBtn');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            }

            if (toggle) toggle.addEventListener('click', toggleSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

            // Safe delete confirmation handler using event listener (handles names with quotes safely)
            document.addEventListener('submit', function (event) {
                const form = event.target.closest('.delete-confirm-form');
                if (!form) {
                    return;
                }

                const name = form.dataset.name || '';
                const type = form.dataset.type || 'mục';
                const message = `Bạn có chắc chắn muốn xóa ${type} "${name}"?`;

                if (!window.confirm(message)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
