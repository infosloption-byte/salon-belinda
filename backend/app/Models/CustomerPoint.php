<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPoint extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'balance_after',
        'reason',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
