@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<div class="row g-5 align-items-center auth-shell">
    <div class="col-lg-6">
        <p class="eyebrow">CHÀO MỪNG TRỞ LẠI</p>
        <h1 class="display-4 fw-bold mb-4">Một tài khoản.<br>Nhiều điều để khám phá.</h1>
        <p class="text-secondary fs-5">Đăng nhập để bắt đầu trải nghiệm mua sắm của bạn cùng SmartCart.</p>
        <div class="auth-decoration mt-4" aria-hidden="true"><span>Smart</span><strong>choices.</strong></div>
    </div>
    <div class="col-lg-5 offset-lg-1">
        <div class="form-panel">
            <h2 class="h3 fw-bold">Đăng nhập</h2>
            <p class="text-secondary mb-4">Nhập thông tin tài khoản của bạn.</p>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username" autofocus @error('email') aria-describedby="email-error" @enderror>@error('email')<div class="invalid-feedback" id="email-error">{{ $message }}</div>@enderror</div>
                <div class="mb-3"><label for="password" class="form-label">Mật khẩu</label><input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" @error('password') aria-describedby="password-error" @enderror>@error('password')<div class="invalid-feedback" id="password-error">{{ $message }}</div>@enderror</div>
                <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember" @checked(old('remember'))><label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label></div>
                <button type="submit" class="btn btn-primary w-100 py-3">Đăng nhập</button>
            </form>
            <p class="text-secondary text-center mt-4 mb-0">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
        </div>
    </div>
</div>
@endsection
