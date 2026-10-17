@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')
@section('content')
<h1 class="h2 fw-bold mb-4">Quản lý đơn hàng</h1>
<form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex flex-wrap gap-2 align-items-center mb-4"><label for="order-status">Trạng thái</label><select id="order-status" name="status" class="form-select w-auto"><option value="">Tất cả</option>@foreach(\App\Models\Order::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select><button class="btn btn-primary" type="submit">Lọc đơn</button></form>
<div class="table-responsive bg-white border rounded-4"><table class="table align-middle mb-0"><thead><tr><th scope="col">Mã đơn</th><th scope="col">Khách hàng</th><th scope="col">Ngày đặt</th><th scope="col">Tổng tiền</th><th scope="col">Trạng thái</th><th scope="col">Chi tiết</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>#{{ $order->id }}</td><td>{{ $order->user->name }}<span class="d-block text-secondary small">{{ $order->user->email }}</span></td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td><td class="text-nowrap">{{ number_format((float) $order->total_amount, 0, ',', '.') }} ₫</td><td>{{ $order->status_label }}</td><td><a href="{{ route('admin.orders.show', $order) }}">Xem / xử lý #{{ $order->id }}</a></td></tr>
@empty<tr><td colspan="6" class="text-center py-5">Không có đơn hàng phù hợp.</td></tr>@endforelse
</tbody></table></div><div class="mt-4">{{ $orders->links() }}</div>
@endsection
