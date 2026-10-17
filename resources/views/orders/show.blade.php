@extends('client')
@section('title', 'Đơn hàng #'.$order->id)
@section('content')
<a href="{{ route('orders.index') }}" class="d-inline-block mb-3">← Lịch sử đơn hàng</a>
<h1 class="h2 fw-bold">Đơn hàng #{{ $order->id }}</h1>
<p class="text-secondary mb-4">{{ $order->created_at->format('d/m/Y H:i') }} · <span class="badge text-bg-light border">{{ $order->status_label }}</span></p>
@include('orders.details')
@endsection
