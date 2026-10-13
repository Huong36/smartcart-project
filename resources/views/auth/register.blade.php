@extends('layouts.app')
@section('title', 'Tạo tài khoản')
@section('content')
<div class="row justify-content-center"><div class="col-md-9 col-lg-7">
    <div class="form-panel">
        <p class="eyebrow">BẮT ĐẦU VỚI SMARTCART</p>
        <h1 class="h2 fw-bold">Tạo tài khoản</h1>
        <p class="text-secondary mb-4">Chỉ vài thông tin để bắt đầu. Các mục có dấu * là bắt buộc.</p>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12"><label for="name" class="form-label">Họ tên *</label><input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" autocomplete="name" @error('name') aria-describedby="name-error" @enderror>@error('name')<div class="invalid-feedback" id="name-error">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label for="email" class="form-label">Email *</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required maxlength="255" autocomplete="username" @error('email') aria-describedby="email-error" @enderror>@error('email')<div class="invalid-feedback" id="email-error">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="password" class="form-label">Mật khẩu *</label><input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" autocomplete="new-password" aria-describedby="password-help @error('password') password-error @enderror"><div class="form-text" id="password-help">Ít nhất 8 ký tự.</div>@error('password')<div class="invalid-feedback" id="password-error">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label for="password_confirmation" class="form-label">Xác nhận mật khẩu *</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password"></div>
                <div class="col-12"><label for="phone" class="form-label">Số điện thoại</label><input id="phone" name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" autocomplete="tel" @error('phone') aria-describedby="phone-error" @enderror>@error('phone')<div class="invalid-feedback" id="phone-error">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label for="address" class="form-label">Địa chỉ</label><textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2" maxlength="1000" autocomplete="street-address" @error('address') aria-describedby="address-error" @enderror>{{ old('address') }}</textarea>@error('address')<div class="invalid-feedback" id="address-error">{{ $message }}</div>@enderror</div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3 mt-4">Tạo tài khoản</button>
        </form>
        <p class="text-center text-secondary mt-4 mb-0">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </div>
</div></div>
@endsection
