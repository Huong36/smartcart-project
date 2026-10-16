@extends('layouts.app')
@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="cart-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Giỏ hàng của bạn</h1>
            <p class="text-secondary small mb-0">Quản lý các sản phẩm bạn đã chọn trước khi thanh toán</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
            ← Tiếp tục mua sắm
        </a>
    </div>

    {{-- Khung hiển thị khi giỏ hàng trống --}}
    <div id="cart-empty-state" class="form-panel text-center py-5 {{ count($items) > 0 ? 'd-none' : '' }}">
        <div class="mb-3">
            <span class="d-inline-flex justify-content-center align-items-center bg-light rounded-circle" style="width: 80px; height: 80px; font-size: 2.5rem;">
                🛒
            </span>
        </div>
        <h2 class="h5 fw-bold mb-2">Giỏ hàng của bạn đang trống</h2>
        <p class="text-secondary mb-4">Hãy khám phá các sản phẩm nổi bật và chọn cho mình những món đồ ưng ý nhé!</p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2">
            Khám phá sản phẩm ngay
        </a>
    </div>

    {{-- Khung hiển thị khi có sản phẩm --}}
    <div id="cart-content-wrapper" class="row g-4 {{ count($items) === 0 ? 'd-none' : '' }}">
        {{-- Danh sách sản phẩm --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="cart-table">
                        <thead class="table-light">
                            <tr class="small text-secondary">
                                <th scope="col" style="min-width: 250px;">Sản phẩm</th>
                                <th scope="col" class="text-center" style="width: 120px;">Đơn giá</th>
                                <th scope="col" class="text-center" style="width: 150px;">Số lượng</th>
                                <th scope="col" class="text-end" style="width: 130px;">Thành tiền</th>
                                <th scope="col" class="text-center" style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr id="cart-row-{{ $item['product_id'] }}" class="cart-item-row" data-product-id="{{ $item['product_id'] }}">
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item['product'])
                                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: contain; background: #f8faf9;">
                                            @else
                                                <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                    ⚠️
                                                </div>
                                            @endif
                                            <div>
                                                <p class="small text-muted mb-0">{{ $item['category_name'] ?? 'SmartCart' }}</p>
                                                <h2 class="h6 fw-semibold mb-1 item-product-name">{{ $item['name'] }}</h2>
                                                @if(!empty($item['stock_warning']))
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                                        {{ $item['stock_warning'] }}
                                                    </span>
                                                @else
                                                    <span class="small text-success">Còn {{ $item['stock'] }} sản phẩm</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $item['price_formatted'] }}
                                    </td>
                                    <td>
                                        @if($item['product'])
                                            <div class="input-group input-group-sm justify-content-center mx-auto" style="max-width: 120px;">
                                                <button class="btn btn-outline-secondary btn-qty-minus" type="button" aria-label="Giảm số lượng" data-product-id="{{ $item['product_id'] }}" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>−</button>
                                                <input type="number" class="form-control text-center input-qty px-1" value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] > 0 ? $item['stock'] : 1 }}" data-product-id="{{ $item['product_id'] }}" data-original-val="{{ $item['quantity'] }}" aria-label="Số lượng sản phẩm">
                                                <button class="btn btn-outline-secondary btn-qty-plus" type="button" aria-label="Tăng số lượng" data-product-id="{{ $item['product_id'] }}" {{ $item['quantity'] >= $item['stock'] ? 'disabled' : '' }}>+</button>
                                            </div>
                                        @else
                                            <span class="text-muted small">Không khả dụng</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold price item-subtotal" id="subtotal-{{ $item['product_id'] }}">
                                        {{ $item['subtotal_formatted'] }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link text-danger p-0 btn-remove-item" data-product-id="{{ $item['product_id'] }}" data-product-name="{{ $item['name'] }}" title="Xóa khỏi giỏ hàng" aria-label="Xóa khỏi giỏ hàng">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-clear-cart">
                    Xóa tất cả sản phẩm
                </button>
            </div>
        </div>

        {{-- Tóm tắt đơn hàng --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 2rem;">
                <h2 class="h5 fw-bold mb-3">Tóm tắt đơn hàng</h2>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Tổng số lượng:</span>
                    <span class="fw-semibold" id="cart-summary-qty">{{ $totalQty }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Tạm tính:</span>
                    <span class="fw-bold price fs-5" id="cart-summary-total">{{ $totalPriceFormatted }}</span>
                </div>
                <hr class="my-3">
                <div class="d-flex justify-content-between align-items-baseline mb-4">
                    <span class="fw-bold">Tổng thanh toán:</span>
                    <span class="fw-bold fs-4 price" id="cart-summary-final">{{ $totalPriceFormatted }}</span>
                </div>
                <p class="small text-muted mb-3">
                    * Giá sản phẩm đã bao gồm thuế. Phí vận chuyển sẽ được tính ở bước thanh toán.
                </p>
                <div class="d-grid gap-2">
                    {{-- Điểm tích hợp cho Thành viên 3 (Checkout) --}}
                    <button type="button" class="btn btn-primary py-3 fw-bold" id="btn-checkout" onclick="alert('Module Thanh toán (Checkout) do Thành viên 3 phụ trách đang được tích hợp. Cảm ơn bạn!')">
                        Tiến hành thanh toán →
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary py-2">
                        Tiếp tục mua hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
