<div class="row g-4">
    <div class="col-lg-8"><div class="table-responsive bg-white border rounded-4"><table class="table align-middle mb-0">
        <thead><tr><th scope="col">Sản phẩm</th><th scope="col">Đơn giá</th><th scope="col">Số lượng</th></tr></thead>
        <tbody>@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td class="text-nowrap">{{ number_format((float) $item->price, 0, ',', '.') }} ₫</td><td>{{ $item->quantity }}</td></tr>@endforeach</tbody>
        <tfoot><tr><th colspan="2">Tổng thanh toán (miễn phí giao hàng)</th><td class="price fw-bold text-nowrap">{{ number_format((float) $order->total_amount, 0, ',', '.') }} ₫</td></tr></tfoot>
    </table></div></div>
    <div class="col-lg-4"><div class="form-panel"><h2 class="h5">Thông tin giao hàng</h2><p class="mb-2" style="white-space: pre-line;">{{ $order->shipping_address }}</p><p>Điện thoại: {{ $order->phone }}</p>@if($order->note)<p style="white-space: pre-line;">Ghi chú: {{ $order->note }}</p>@endif<p class="text-secondary small mb-0">Thanh toán khi nhận hàng (COD)</p></div></div>
</div>
