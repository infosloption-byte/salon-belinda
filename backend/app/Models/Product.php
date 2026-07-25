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

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest('id');
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
     * Logs a 'sale' movement on the ledger — quantity_change reflects what
     * was actually deducted even if $quantity would have taken stock below
     * zero.
     */
    public function decrementStock(int $quantity, ?string $referenceType = null, ?int $referenceId = null): void
    {
        $before = $this->stock_count;
        $this->stock_count = max(0, $this->stock_count - $quantity);
        $this->in_stock = $this->stock_count > 0;
        $this->save();

        $this->logMovement('sale', $this->stock_count - $before, $referenceType, $referenceId);
    }

    /**
     * Manual restock or write-off from the admin — moves stock_count by
     * $delta (positive to add, negative to remove) and logs it. Used by the
     * dedicated stock-movement endpoint, as opposed to decrementStock()
     * (sales) or logCorrection() (raw product-form edits).
     */
    public function applyMovement(string $type, int $delta, ?string $reason = null, ?int $createdBy = null): StockMovement
    {
        $this->stock_count = max(0, $this->stock_count + $delta);
        $this->in_stock = $this->stock_count > 0;
        $this->save();

        return $this->logMovement($type, $delta, null, null, $reason, $createdBy);
    }

    /**
     * Log a movement for a stock_count change that already happened
     * elsewhere (the admin Product edit form lets stock_count be typed in
     * directly) — records the ledger entry without touching stock again.
     */
    public function logCorrection(int $delta, ?int $createdBy = null): ?StockMovement
    {
        if ($delta === 0) {
            return null;
        }

        return $this->logMovement('correction', $delta, null, null, 'Manual edit via product form', $createdBy);
    }

    private function logMovement(
        string $type,
        int $delta,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null,
        ?int $createdBy = null,
    ): StockMovement {
        return $this->stockMovements()->create([
            'type' => $type,
            'quantity_change' => $delta,
            'balance_after' => $this->stock_count,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => $createdBy,
        ]);
    }
}
