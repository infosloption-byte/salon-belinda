<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPayment extends Model
{
    protected $fillable = [
        'job_id',
        'amount',
        'method',
        'paid_at',
        'recorded_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
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
