@extends('client')
@section('title', 'Đơn hàng #'.$order->id)
@section('content')
<a href="{{ route('orders.index') }}" class="d-inline-block mb-3">← Lịch sử đơn hàng</a>
<h1 class="h2 fw-bold">Đơn hàng #{{ $order->id }}</h1>
<p class="text-secondary mb-4">{{ $order->created_at->format('d/m/Y H:i') }} · <span class="badge text-bg-light border">{{ $order->status_label }}</span></p>
@include('orders.details')
@if($errors->any())<div class="alert alert-danger mt-4" role="alert">{{ $errors->first() }}</div>@endif
@if($order->canBeCancelledBy(auth()->user()))
<div class="mt-4">
    <p class="text-secondary small">Bạn có thể hủy đơn khi còn chờ xác nhận.</p>
    <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?')">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-outline-danger">Hủy đơn hàng</button>
    </form>
</div>
@endif
@endsection
