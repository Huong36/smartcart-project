@extends('layouts.admin')
@section('title', 'Xử lý đơn #'.$order->id)
@section('content')
<a href="{{ route('admin.orders.index') }}" class="d-inline-block mb-3">← Danh sách đơn hàng</a>
<h1 class="h2 fw-bold">Đơn hàng #{{ $order->id }}</h1>
<p>Khách hàng: <strong>{{ $order->user->name }}</strong> · {{ $order->user->email }}</p>
<p class="text-secondary">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->status_label }}</p>
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
@php
    $nextStatus = match($order->status) {
        \App\Models\Order::STATUS_PENDING => \App\Models\Order::STATUS_PROCESSING,
        \App\Models\Order::STATUS_PROCESSING => \App\Models\Order::STATUS_COMPLETED,
        default => null,
    };
@endphp
@if($nextStatus)
<form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mb-4">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $nextStatus }}"><button type="submit" class="btn btn-primary">Chuyển sang: {{ \App\Models\Order::STATUS_LABELS[$nextStatus] }}</button></form>
@else<p class="text-secondary mb-4">Đơn đã kết thúc, không thể thay đổi trạng thái.</p>@endif
@include('orders.details')
@if($order->canBeCancelledBy(auth()->user()))
<div class="mt-4">
    <p class="text-secondary small">Admin có thể hủy đơn chờ xác nhận hoặc đang giao. Hệ thống sẽ hoàn lại tồn kho.</p>
    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Hủy đơn hàng và hoàn lại tồn kho?')">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-outline-danger">Hủy đơn hàng</button>
    </form>
</div>
@endif
@endsection
