<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'price',
        'compare_at_price',
        'description',
        'details',
        'images',
        'in_stock',
        'stock_count',
        'reorder_point',
        'rating',
        'review_count',
        'best_seller',
        'is_new',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'details' => 'array',
            'images' => 'array',
            'in_stock' => 'boolean',
            'stock_count' => 'integer',
            'reorder_point' => 'integer',
            'rating' => 'decimal:1',
            'review_count' => 'integer',
            'best_seller' => 'boolean',
            'is_new' => 'boolean',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_count <= ($this->reorder_point ?? 10);
    }

    /**
     * Products at or below their reorder point — a per-product override
     * where set, falling back to the original hardcoded 10 where not.
     * Single source of truth used by both the Low Stock report and the
     * dashboard alert, so the two never drift apart.
     */
    public function scopeLowStock($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('reorder_point')->whereColumn('stock_count', '<=', 'reorder_point');
        })->orWhere(function ($q) {
            $q->whereNull('reorder_point')->where('stock_count', '<=', 10);
        });
    }

    /**
     * Keep stock_count and in_stock in sync whenever stock changes.
     */
    public function decrementStock(int $quantity): void
    {
        $this->stock_count = max(0, $this->stock_count - $quantity);
        $this->in_stock = $this->stock_count > 0;
        $this->save();
    }
}
