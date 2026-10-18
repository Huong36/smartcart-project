@extends('client')
@section('title', 'Lịch sử đơn hàng')
@section('content')
<p class="eyebrow">TÀI KHOẢN CỦA BẠN</p><h1 class="h2 fw-bold mb-4">Lịch sử đơn hàng</h1>
<div class="table-responsive bg-white border rounded-4"><table class="table align-middle mb-0"><thead><tr><th scope="col">Mã đơn</th><th scope="col">Ngày đặt</th><th scope="col">Tổng tiền</th><th scope="col">Trạng thái</th><th scope="col">Chi tiết</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>#{{ $order->id }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td><td class="text-nowrap">{{ number_format((float) $order->total_amount, 0, ',', '.') }} ₫</td><td><span class="order-status status-{{ $order->status }}">{{ $order->status_label }}</span></td><td><a href="{{ route('orders.show', $order) }}">Xem đơn #{{ $order->id }}</a></td></tr>
@empty<tr><td colspan="5" class="text-center py-5">Bạn chưa có đơn hàng. <a href="{{ route('home') }}">Mua sắm ngay</a></td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $orders->links() }}</div>
@endsection
