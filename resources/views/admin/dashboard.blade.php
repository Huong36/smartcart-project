@extends('layouts.admin')
@section('title', 'Tổng quan Quản trị')
@section('content')
<p class="eyebrow">QUẢN TRỊ SMARTCART</p>
<h1 class="h2 fw-bold mb-2">Tổng quan cửa hàng</h1>
<p class="text-secondary mb-4">Xin chào, {{ auth()->user()->name }}. Đây là tình hình cửa hàng của bạn.</p>
<div class="row g-4">
    @foreach([['Tổng đơn hàng', number_format($orderCount)], ['Doanh thu đã hoàn thành', number_format((float) $revenue, 0, ',', '.').' ₫'], ['Sản phẩm', number_format($productCount)], ['Số lượng trong kho', number_format($stockCount)], ['Danh mục', number_format($categoryCount)]] as [$label, $value])
    <div class="col-md-6 col-xl-4"><div class="stat-panel"><p class="text-secondary mb-3">{{ $label }}</p><p class="h2 fw-bold mb-0">{{ $value }}</p></div></div>
    @endforeach
</div>
@endsection
