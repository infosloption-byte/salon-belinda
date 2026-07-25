<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SalonJob extends Model
{
    protected $table = 'jobs_salon';

    protected $fillable = [
        'customer_id',
        'appointment_id',
        'status',
        'job_date',
        'notes',
        'created_by',
        'subtotal',
        'total_paid',
        'balance_due',
        'total_tips',
        'discount_type',
        'discount_value',
    ];

    protected function casts(): array
    {
        return [
            'job_date' => 'date',
            'discount_value' => 'decimal:2',
        ];
    }

    /**
     * Appended so every endpoint that serializes a SalonJob (show, store,
     * addItem, addPayment, updateDiscount, ...) carries these without each
     * controller having to remember to compute them separately.
     */
    protected $appends = ['discount_amount', 'total_after_discount'];

    protected function discountAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->subtotal - self::applyDiscount((int) $this->subtotal, $this->discount_type ?? 'none', $this->discount_value),
        );
    }

    protected function totalAfterDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => self::applyDiscount((int) $this->subtotal, $this->discount_type ?? 'none', $this->discount_value),
        );
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(JobItem::class, 'job_id');
    }

    public function payments()
    {
        return $this->hasMany(JobPayment::class, 'job_id');
    }

    /**
     * Recompute and persist the cached subtotal/total_paid/balance_due.
     * Call this after any item or payment is added, changed, or removed —
     * or after the job-level discount itself is changed.
     */
    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('final_price');
        $totalPaid = (int) $this->payments()->sum('amount');
        $totalTips = (int) $this->payments()->sum('tip_amount');
        $totalAfterDiscount = self::applyDiscount($subtotal, $this->discount_type ?? 'none', $this->discount_value);

        $this->forceFill([
            'subtotal' => $subtotal,
            'total_paid' => $totalPaid,
            'balance_due' => $totalAfterDiscount - $totalPaid,
            'total_tips' => $totalTips,
        ])->save();
    }

    /**
     * Job-level discount applied on top of the item subtotal (distinct
     * from the per-item discounts already folded into each item's
     * final_price). Same percent/fixed shape as JobItem's discount, kept
     * as a separate static rather than reusing JobItem::computeFinalPrice
     * so the two stay independent if either's rules diverge later.
     */
    public static function applyDiscount(int $subtotal, string $discountType, $discountValue): int
    {
        $discountValue = (float) ($discountValue ?? 0);

        return match ($discountType) {
            'percent' => max(0, (int) round($subtotal - ($subtotal * min($discountValue, 100) / 100))),
            'fixed' => max(0, $subtotal - (int) round($discountValue)),
            default => $subtotal,
        };
    }
}
