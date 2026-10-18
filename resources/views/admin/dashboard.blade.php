@extends('layouts.admin')
@section('title', 'Tổng quan Quản trị')
@section('content')
<div class="dashboard-heading">
    <h1>Tổng quan cửa hàng</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Thêm sản phẩm</a>
</div>
<div class="dashboard-stats">
    <div class="dashboard-stat"><span>Tổng đơn hàng</span><strong>{{ number_format($orderCount) }}</strong></div>
    <div class="dashboard-stat"><span>Doanh thu đã hoàn thành</span><strong>{{ number_format((float) $revenue, 0, ',', '.') }} ₫</strong></div>
    <div class="dashboard-stat"><span>Sản phẩm / Danh mục</span><strong>{{ number_format($productCount) }} / {{ number_format($categoryCount) }}</strong></div>
    <div class="dashboard-stat"><span>Số lượng tồn kho</span><strong>{{ number_format($stockCount) }}</strong></div>
</div>
<div class="dashboard-status-list" aria-label="Số đơn theo trạng thái">
    @foreach(\App\Models\Order::STATUS_LABELS as $status => $label)
        <a href="{{ route('admin.orders.index', ['status' => $status]) }}">{{ $label }}: <strong>{{ number_format($statusCounts->get($status, 0)) }}</strong></a>
    @endforeach
</div>
<section class="dashboard-panel">
    <div class="panel-heading"><h2>Đơn hàng gần đây</h2><a href="{{ route('admin.orders.index') }}">Xem tất cả</a></div>
    <div class="table-responsive">
        <table class="table dashboard-orders">
            <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Chi tiết</th></tr></thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a><small class="order-date">{{ $order->created_at->format('d/m/Y') }}</small></td>
                        <td>{{ $order->user->name }}</td>
                        <td class="text-nowrap">{{ number_format((float) $order->total_amount, 0, ',', '.') }} ₫</td>
                        <td><span class="order-status status-{{ $order->status }}">{{ $order->status_label }}</span></td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" aria-label="Xem đơn {{ $order->id }}">Xem</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">Chưa có đơn hàng.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
