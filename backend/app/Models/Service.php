<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'name',
        'description',
        'duration',
        'price',
        'price_prefix',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Display string matching the frontend's original format, e.g.
     * "LKR 4,500" or "From LKR 6,500".
     */
    public function getPriceLabelAttribute(): string
    {
        $formatted = 'LKR '.number_format($this->price);

        return $this->price_prefix ? "{$this->price_prefix} {$formatted}" : $formatted;
    }
}
