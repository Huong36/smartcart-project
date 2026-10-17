<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', Rule::in(array_keys(Order::STATUS_LABELS))]]);
        $orders = Order::with('user')->withCount('items')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load(['user', 'items'])]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([Order::STATUS_PROCESSING, Order::STATUS_COMPLETED])],
        ]);
        DB::transaction(function () use ($order, $data): void {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $next = [
                Order::STATUS_PENDING => Order::STATUS_PROCESSING,
                Order::STATUS_PROCESSING => Order::STATUS_COMPLETED,
            ];
            if (($next[$locked->status] ?? null) !== $data['status']) {
                throw ValidationException::withMessages(['status' => 'Trạng thái không hợp lệ hoặc đơn vừa được cập nhật. Vui lòng tải lại trang.']);
            }
            $locked->update(['status' => $data['status']]);
        }, 3);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $cancelled = $order->cancel($request->user());

        return redirect()->route('admin.orders.show', $order)->with('success', $cancelled
            ? 'Đã hủy đơn hàng và hoàn lại số lượng sản phẩm vào kho.'
            : 'Đơn hàng đã được hủy trước đó.');
    }
}
