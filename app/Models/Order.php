<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ xác nhận',
        self::STATUS_PROCESSING => 'Đang giao',
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    protected $fillable = ['user_id', 'total_amount', 'status', 'shipping_address', 'phone', 'note'];

    protected $attributes = ['status' => self::STATUS_PENDING];

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function canBeCancelledBy(User $actor): bool
    {
        if ($actor->isAdmin()) {
            return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
        }

        return $this->user_id === $actor->id && $this->status === self::STATUS_PENDING;
    }

    public function cancel(User $actor): bool
    {
        return DB::transaction(function () use ($actor): bool {
            $order = self::whereKey($this->id)->lockForUpdate()->firstOrFail();
            abort_unless($actor->isAdmin() || $order->user_id === $actor->id, 403);

            if ($order->status === self::STATUS_CANCELLED) {
                return false;
            }

            if (! $order->canBeCancelledBy($actor)) {
                throw ValidationException::withMessages([
                    'status' => 'Không thể hủy đơn ở trạng thái hiện tại. Vui lòng tải lại trang.',
                ]);
            }

            $items = $order->items()->orderBy('product_id')->get();
            $products = Product::whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'status' => 'Không thể hoàn kho vì sản phẩm không còn tồn tại. Vui lòng liên hệ cửa hàng.',
                    ]);
                }

                $product->increment('stock', $item->quantity);
            }

            $order->update(['status' => self::STATUS_CANCELLED]);

            return true;
        }, 3);
    }
}
