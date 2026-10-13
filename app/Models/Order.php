<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
