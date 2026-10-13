<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'price', 'stock', 'image', 'description'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset('storage/'.$this->image) : asset('images/product-placeholder.svg');
    }
}
