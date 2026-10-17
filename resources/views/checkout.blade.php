@extends('client')
@section('title', 'Thanh toán')
@section('content')
<p class="eyebrow">HOÀN TẤT MUA HÀNG</p><h1 class="h2 fw-bold mb-4">Thông tin giao hàng</h1>
@if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><a href="{{ route('cart.index') }}">Kiểm tra giỏ hàng</a></div>@endif
<form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
    @csrf
    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
    <div class="row g-4">
        <div class="col-lg-7"><div class="form-panel">
            <p>Người đặt: <strong>{{ auth()->user()->name }}</strong></p>
            <div class="mb-3"><label for="shipping-address" class="form-label">Địa chỉ nhận hàng <span class="text-danger">*</span></label><textarea id="shipping-address" name="shipping_address" class="form-control @error('shipping_address') is-invalid @enderror" required maxlength="1000" rows="3" autocomplete="street-address">{{ old('shipping_address', auth()->user()->address) }}</textarea>@error('shipping_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label for="checkout-phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label><input id="checkout-phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}" required maxlength="16" pattern="\+?[0-9]{9,15}" autocomplete="tel">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label for="checkout-note" class="form-label">Ghi chú giao hàng</label><textarea id="checkout-note" name="note" class="form-control" maxlength="1000" rows="3">{{ old('note') }}</textarea></div>
            <p class="text-secondary small mb-0">Thanh toán khi nhận hàng (COD). Miễn phí giao hàng.</p>
        </div></div>
        <div class="col-lg-5"><div class="form-panel">
            <h2 class="h5 fw-bold mb-3">Đơn hàng của bạn</h2>
            @foreach($lines as $line)<div class="d-flex justify-content-between gap-3 border-bottom py-3"><div>{{ $line['product']->name }}<span class="text-secondary d-block small">Số lượng: {{ $line['quantity'] }}</span></div><strong class="text-nowrap">{{ number_format((float) $line['subtotal'], 0, ',', '.') }} ₫</strong></div>@endforeach
            <div class="d-flex justify-content-between mt-3"><span>Phí giao hàng</span><span>Miễn phí</span></div>
            <div class="d-flex justify-content-between my-4 fs-5 fw-bold"><span>Tổng thanh toán</span><span class="price">{{ number_format((float) $totalAmount, 0, ',', '.') }} ₫</span></div>
            <button type="submit" class="btn btn-primary w-100 py-3" id="place-order">Xác nhận đặt hàng</button>
            <a class="d-block text-center mt-3" href="{{ route('cart.index') }}">Quay lại giỏ hàng</a>
        </div></div>
    </div>
</form>
@endsection
@push('scripts')
<script>
document.getElementById('checkout-form').addEventListener('submit', function () {
    const button = document.getElementById('place-order');
    button.disabled = true;
    button.textContent = 'Đang tạo đơn hàng...';
});
window.addEventListener('pageshow', function () {
    const button = document.getElementById('place-order');
    button.disabled = false;
    button.textContent = 'Xác nhận đặt hàng';
});
</script>
@endpush
