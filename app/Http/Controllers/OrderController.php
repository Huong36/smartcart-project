<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => $request->user()->orders()->withCount('items')->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, int $order): View
    {
        return view('orders.show', [
            'order' => $request->user()->orders()->with('items')->findOrFail($order),
        ]);
    }

    public function cancel(Request $request, int $order): RedirectResponse
    {
        $ownedOrder = $request->user()->orders()->findOrFail($order);
        $cancelled = $ownedOrder->cancel($request->user());

        return redirect()->route('orders.show', $ownedOrder)->with('success', $cancelled
            ? 'Đã hủy đơn hàng và hoàn lại số lượng sản phẩm vào kho.'
            : 'Đơn hàng đã được hủy trước đó.');
    }
}
