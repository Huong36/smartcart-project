<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the shopping cart page with items, subtotals, and total, or JSON payload if requested.
     */
    public function index(Request $request): View|JsonResponse
    {
        $cartData = $this->buildCartData();

        if ($request->expectsJson()) {
            return response()->json(array_merge(['success' => true], $cartData));
        }

        return view('cart.index', [
            'items' => $cartData['items'],
            'totalPrice' => $cartData['raw_total_price'],
            'totalPriceFormatted' => $cartData['total_price'],
            'totalQty' => $cartData['total_qty'],
        ]);
    }

    /**
     * Return current cart payload as JSON for dynamic client synchronization.
     */
    public function data(): JsonResponse
    {
        return response()->json(array_merge(['success' => true], $this->buildCartData()));
    }

    /**
     * Return current cart summary count for dynamic badges.
     */
    public function summary(): JsonResponse
    {
        $cart = session()->get('cart', []);
        $totalQty = array_sum(array_map('intval', $cart));

        return response()->json([
            'cart_count' => $totalQty,
        ]);
    }

    /**
     * Add a product to the cart session via Fetch API.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $productId = (int) $validated['product_id'];
        $addQty = isset($validated['quantity']) ? (int) $validated['quantity'] : 1;

        $product = Product::findOrFail($productId);

        if ($product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm "'.$product->name.'" hiện đã hết hàng.',
            ], 422);
        }

        $cart = session()->get('cart', []);
        $currentQty = isset($cart[$productId]) ? (int) $cart[$productId] : 0;
        $newQty = $currentQty + $addQty;

        if ($newQty > $product->stock) {
            $availableToAdd = max(0, $product->stock - $currentQty);

            return response()->json([
                'success' => false,
                'message' => $availableToAdd > 0
                    ? 'Bạn chỉ có thể thêm tối đa '.$availableToAdd.' sản phẩm nữa (kho còn '.$product->stock.').'
                    : 'Số lượng trong giỏ đã đạt mức tối đa trong kho ('.$product->stock.' sản phẩm).',
            ], 422);
        }

        $cart[$productId] = $newQty;
        session()->put('cart', $cart);

        $totalQty = array_sum(array_map('intval', $cart));

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm "'.$product->name.'" vào giỏ hàng!',
            'cart_count' => $totalQty,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'added_qty' => $addQty,
                'current_qty' => $newQty,
            ],
        ]);
    }

    /**
     * Update quantity of a product in cart via Fetch API.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $productId = (int) $validated['product_id'];
        $quantity = (int) $validated['quantity'];

        $cart = session()->get('cart', []);

        if (! isset($cart[$productId])) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không có trong giỏ hàng.',
            ], 404);
        }

        $product = Product::findOrFail($productId);

        if ($quantity > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => 'Kho chỉ còn '.$product->stock.' sản phẩm.',
                'max_available' => $product->stock,
            ], 422);
        }

        $cart[$productId] = $quantity;
        session()->put('cart', $cart);

        // Recalculate totals
        $totals = $this->calculateCartTotals($cart);
        $itemSubtotal = (int) round((float) $product->price * $quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công.',
            'product_id' => $productId,
            'quantity' => $quantity,
            'item_subtotal' => number_format($itemSubtotal, 0, ',', '.').' ₫',
            'raw_item_subtotal' => $itemSubtotal,
            'total_price' => number_format($totals['total_price'], 0, ',', '.').' ₫',
            'raw_total_price' => $totals['total_price'],
            'cart_count' => $totals['total_qty'],
        ]);
    }

    /**
     * Remove a product from the cart via Fetch API.
     */
    public function remove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $productId = (int) $validated['product_id'];
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        $totals = $this->calculateCartTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            'product_id' => $productId,
            'cart_count' => $totals['total_qty'],
            'total_price' => number_format($totals['total_price'], 0, ',', '.').' ₫',
            'raw_total_price' => $totals['total_price'],
            'is_empty' => empty($cart),
        ]);
    }

    /**
     * Clear all items in the cart.
     */
    public function clear(): JsonResponse
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Đã làm trống giỏ hàng.',
            'cart_count' => 0,
            'total_price' => '0 ₫',
            'raw_total_price' => 0,
            'is_empty' => true,
        ]);
    }

    /**
     * Helper to compute total quantity and total price from cart session.
     *
     * @param  array<int, int>  $cart
     * @return array{total_qty: int, total_price: int}
     */
    private function calculateCartTotals(array $cart): array
    {
        $data = $this->buildCartData();

        return [
            'total_qty' => $data['total_qty'],
            'total_price' => $data['raw_total_price'],
        ];
    }

    /**
     * Build the structured cart items and totals.
     *
     * @return array{
     *     items: array<int, array<string, mixed>>,
     *     total_price: string,
     *     raw_total_price: int,
     *     total_qty: int,
     *     is_empty: bool
     * }
     */
    private function buildCartData(): array
    {
        $cart = session()->get('cart', []);
        $items = [];
        $totalPrice = 0;
        $totalQty = 0;

        if (! empty($cart)) {
            $productIds = array_keys($cart);
            $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($cart as $productId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                $product = $products->get($productId);

                if (! $product) {
                    $items[] = [
                        'product_id' => (int) $productId,
                        'product' => null,
                        'name' => 'Sản phẩm không còn khả dụng (ID #'.$productId.')',
                        'image_url' => null,
                        'category_name' => 'SmartCart',
                        'price' => 0,
                        'price_formatted' => '0 ₫',
                        'quantity' => $qty,
                        'subtotal' => 0,
                        'subtotal_formatted' => '0 ₫',
                        'stock' => 0,
                        'stock_warning' => 'Sản phẩm đã bị xóa khỏi hệ thống.',
                        'is_available' => false,
                    ];

                    continue;
                }

                $unitPrice = (float) $product->price;
                $subtotal = (int) round($unitPrice * $qty);
                $isOutOfStock = $product->stock <= 0;
                $stockWarning = null;

                if ($isOutOfStock) {
                    $stockWarning = 'Sản phẩm hiện đang hết hàng.';
                } elseif ($qty > $product->stock) {
                    $stockWarning = 'Số lượng vượt quá tồn kho hiện có (còn '.$product->stock.' sản phẩm).';
                }

                $items[] = [
                    'product_id' => $product->id,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image_url' => $product->image_url,
                        'category_name' => $product->category?->name,
                        'stock' => (int) $product->stock,
                    ],
                    'name' => $product->name,
                    'image_url' => $product->image_url,
                    'category_name' => $product->category?->name ?? 'SmartCart',
                    'price' => $unitPrice,
                    'price_formatted' => number_format($unitPrice, 0, ',', '.').' ₫',
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'subtotal_formatted' => number_format($subtotal, 0, ',', '.').' ₫',
                    'stock' => (int) $product->stock,
                    'stock_warning' => $stockWarning,
                    'is_available' => (! $isOutOfStock && $qty <= $product->stock),
                ];

                $totalPrice += $subtotal;
                $totalQty += $qty;
            }
        }

        return [
            'items' => $items,
            'total_price' => number_format($totalPrice, 0, ',', '.').' ₫',
            'raw_total_price' => $totalPrice,
            'total_qty' => $totalQty,
            'is_empty' => empty($items),
        ];
    }
}
