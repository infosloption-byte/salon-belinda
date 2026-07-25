<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPayment extends Model
{
    protected $fillable = [
        'job_id',
        'amount',
        'tip_amount',
        'method',
        'paid_at',
        'recorded_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'integer',
            'tip_amount' => 'integer',
        ];
    }

    public function job()
    {
        return $this->belongsTo(SalonJob::class, 'job_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
