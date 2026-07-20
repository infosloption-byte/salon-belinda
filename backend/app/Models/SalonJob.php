<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'job_date' => 'date',
        ];
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
     * Call this after any item or payment is added, changed, or removed.
     */
    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('final_price');
        $totalPaid = (int) $this->payments()->sum('amount');

        $this->forceFill([
            'subtotal' => $subtotal,
            'total_paid' => $totalPaid,
            'balance_due' => $subtotal - $totalPaid,
        ])->save();
    }
}
