<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') · SmartCart Admin</title>

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        :root {
            --admin-sidebar-width: 260px;
            --admin-primary: #08796a;
            --admin-primary-dark: #055449;
            --admin-primary-light: #e8f3ee;
            --admin-sidebar-bg: #132420;
            --admin-sidebar-hover: #1c332e;
            --admin-sidebar-active: #08796a;
            --admin-content-bg: #f5f8f7;
        }

        body.admin-body {
            background-color: var(--admin-content-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-navbar {
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid #e1eae6;
            z-index: 1020;
        }

        .admin-wrapper {
            display: flex;
            flex: 1 0 auto;
        }

        .admin-sidebar {
            width: var(--admin-sidebar-width);
            background-color: var(--admin-sidebar-bg);
            color: #d1ded9;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 64px);
            transition: all 0.25s ease;
        }

        .admin-sidebar-brand {
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        .admin-sidebar-brand:hover {
            color: #ffffff;
        }

        .admin-nav-heading {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #7b9e95;
            font-weight: 700;
            padding: 1.25rem 1.5rem 0.5rem;
        }

        .admin-nav {
            padding: 0 0.75rem;
            list-style: none;
            margin: 0;
        }

        .admin-nav-item {
            margin-bottom: 0.25rem;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            border-radius: 0.6rem;
            color: #bcd0ca;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            transition: all 0.15s ease-in-out;
        }

        .admin-nav-link:hover {
            background-color: var(--admin-sidebar-hover);
            color: #ffffff;
        }

        .admin-nav-link.active {
            background-color: var(--admin-sidebar-active);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(8, 121, 106, 0.35);
        }

        .admin-nav-link svg {
            width: 1.15rem;
            height: 1.15rem;
            flex-shrink: 0;
        }

        .admin-main {
            flex: 1 1 auto;
            min-width: 0;
            padding: 1.75rem 2rem 3rem;
        }

        .admin-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--admin-primary-light);
            color: var(--admin-primary);
            font-weight: 700;
            display: grid;
            place-items: center;
            font-size: 0.95rem;
        }

        .card-table {
            background: #ffffff;
            border: 1px solid #e1eae6;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .table > :not(caption) > * > * {
            padding: 0.9rem 1rem;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f7faf9;
            color: #4b635c;
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e1eae6;
        }

        .product-thumb {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            background: #eef3f0;
            border: 1px solid #e1eae6;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1045;
                transform: translateX(-100%);
                box-shadow: 4px 0 16px rgba(0, 0, 0, 0.2);
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
            .admin-sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.45);
                z-index: 1040;
            }
            .admin-sidebar-backdrop.show {
                display: block;
            }
            .admin-main {
                padding: 1.25rem 1rem 2rem;
            }
        }
    </style>
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
                <span class="brand-mark">S</span>
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
                <span class="fw-bold text-white">Menu Quản trị</span>
                <button type="button" class="btn-close btn-close-white" id="sidebarCloseBtn" aria-label="Đóng"></button>
            </div>

            <div class="admin-nav-heading">Điều hướng chính</div>
            <ul class="admin-nav">
                <li class="admin-nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Tổng quan</span>
                    </a>
                </li>
            </ul>

            <div class="admin-nav-heading">Quản lý kho hàng</div>
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

            <div class="admin-nav-heading">Đơn hàng</div>
            <ul class="admin-nav"><li class="admin-nav-item"><a href="{{ route('admin.orders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><span>Quản lý đơn hàng</span></a></li></ul>
            <div class="admin-nav-heading mt-auto">Hệ thống</div>
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Đóng"></button>
                </div>
            @endif

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
