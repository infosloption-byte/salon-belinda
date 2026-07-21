<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobItem extends Model
{
    protected $fillable = [
        'job_id',
        'service_id',
        'service_name',
        'staff_id',
        'base_price',
        'discount_type',
        'discount_value',
        'final_price',
        'commission_percent',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'commission_percent' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobItem $item) {
            $item->final_price = self::computeFinalPrice(
                $item->base_price,
                $item->discount_type,
                $item->discount_value
            );

            // Commission is calculated on the discounted (final) price, and
            // snapshotted here so later changes to a staff member's rate
            // never rewrite historical commission figures.
            $item->commission_amount = (int) round($item->final_price * ($item->commission_percent / 100));
        });
    }

    public static function computeFinalPrice(int $basePrice, string $discountType, ?float $discountValue): int
    {
        $discountValue ??= 0;

        return match ($discountType) {
            'percent' => max(0, (int) round($basePrice - ($basePrice * min($discountValue, 100) / 100))),
            'fixed' => max(0, $basePrice - (int) round($discountValue)),
            default => $basePrice,
        };
    }

    public function job()
    {
        return $this->belongsTo(SalonJob::class, 'job_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
