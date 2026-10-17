<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        try {
            $summary = $this->cartSummary($request->session()->get('cart', []));
        } catch (ValidationException $exception) {
            return redirect()->route('cart.index')->withErrors($exception->errors());
        }
        if (! $request->session()->has('checkout_token')) {
            $request->session()->put('checkout_token', (string) Str::uuid());
        }

        return view('checkout', $summary + ['checkoutToken' => $request->session()->get('checkout_token')]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $existing = $request->user()->orders()->where('checkout_token', $data['checkout_token'])->first();
        if ($existing) {
            return redirect()->route('orders.show', $existing);
        }
        if (! hash_equals((string) $request->session()->get('checkout_token', ''), $data['checkout_token'])) {
            throw ValidationException::withMessages(['cart' => 'Phiên thanh toán đã hết hạn. Vui lòng tải lại trang thanh toán.']);
        }
        $cart = $request->session()->get('cart', []);
        [$order, $created] = DB::transaction(function () use ($request, $data, $cart): array {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $existing = $request->user()->orders()->where('checkout_token', $data['checkout_token'])->first();
            if ($existing) {
                return [$existing, false];
            }
            $summary = $this->cartSummary($cart, true);
            $order = $request->user()->orders()->create([
                'shipping_address' => $data['shipping_address'],
                'phone' => $data['phone'],
                'note' => $data['note'] ?? null,
                'total_amount' => $summary['totalAmount'],
                'status' => Order::STATUS_PENDING,
            ]);
            $order->checkout_token = $data['checkout_token'];
            $order->save();
            foreach ($summary['lines'] as $line) {
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $line['quantity'],
                ]);
                $product->decrement('stock', $line['quantity']);
            }

            return [$order, true];
        }, 3);
        if ($created) {
            $request->session()->forget(['cart', 'checkout_token']);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Đặt hàng thành công! Đơn hàng đang chờ xác nhận.')
            ->with('order_success', true)
            ->with('order_id', $order->id)
            ->with('order_total', $order->total_amount);
    }

    /** @return array{lines: array, totalAmount: string} */
    private function cartSummary(mixed $cart, bool $lock = false): array
    {
        if (! is_array($cart) || $cart === []) {
            throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.']);
        }
        foreach ($cart as $id => $quantity) {
            if (! ctype_digit((string) $id) || (int) $id < 1 || ! is_int($quantity) || $quantity < 1 || $quantity > 999) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng có số lượng không hợp lệ. Vui lòng cập nhật lại.']);
            }
        }
        $query = Product::whereIn('id', array_keys($cart))->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $products = $query->get()->keyBy('id');
        $lines = [];
        $total = 0;
        foreach ($cart as $id => $quantity) {
            $product = $products->get($id);
            if (! $product) {
                throw ValidationException::withMessages(['cart' => 'Một sản phẩm đã bị xóa. Vui lòng cập nhật giỏ hàng.']);
            }
            if ($product->stock < $quantity) {
                throw ValidationException::withMessages(['cart' => $product->name.' chỉ còn '.$product->stock.' sản phẩm. Vui lòng cập nhật giỏ hàng.']);
            }
            [$whole, $fraction] = explode('.', $product->price);
            $price = (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
            if (str_starts_with($product->price, '-') || $price <= 0) {
                throw ValidationException::withMessages(['cart' => 'Giá sản phẩm không hợp lệ. Vui lòng liên hệ cửa hàng.']);
            }
            $subtotal = $price * $quantity;
            $total += $subtotal;
            if ($total > 999999999999) {
                throw ValidationException::withMessages(['cart' => 'Giá trị đơn hàng quá lớn. Vui lòng chia thành các đơn nhỏ hơn.']);
            }
            $lines[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $this->decimalAmount($subtotal)];
        }

        return ['lines' => $lines, 'totalAmount' => $this->decimalAmount($total)];
    }

    private function decimalAmount(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
