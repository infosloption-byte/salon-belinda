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
