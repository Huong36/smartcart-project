@extends('layouts.app')
@section('title', 'Tài khoản của tôi')
@section('content')
<p class="eyebrow">TÀI KHOẢN CỦA TÔI</p>
<h1 class="h2 fw-bold mb-4">Xin chào, {{ auth()->user()->name }}</h1>
<div class="form-panel"><dl class="row mb-0">
    <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ auth()->user()->email }}</dd>
    <dt class="col-sm-3">Số điện thoại</dt><dd class="col-sm-9">{{ auth()->user()->phone ?: 'Chưa cung cấp' }}</dd>
    <dt class="col-sm-3">Địa chỉ</dt><dd class="col-sm-9">{{ auth()->user()->address ?: 'Chưa cung cấp' }}</dd>
    <dt class="col-sm-3">Loại tài khoản</dt><dd class="col-sm-9">{{ auth()->user()->isAdmin() ? 'Quản trị viên' : 'Khách hàng' }}</dd>
</dl></div>
@endsection
