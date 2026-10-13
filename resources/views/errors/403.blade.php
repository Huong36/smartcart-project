@extends('layouts.app')
@section('title', 'Không có quyền truy cập')
@section('content')
<div class="text-center py-5"><p class="eyebrow">403 · TRUY CẬP BỊ GIỚI HẠN</p><h1 class="h2 fw-bold">Bạn không có quyền truy cập trang này.</h1><p class="text-secondary my-3">Trang quản trị chỉ dành cho tài khoản quản trị viên.</p><a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a></div>
@endsection
