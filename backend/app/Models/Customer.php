<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'notes',
    ];

    public function jobs()
    {
        return $this->hasMany(SalonJob::class, 'customer_id');
    }

    public function totalSpent(): int
    {
        return (int) $this->jobs()->sum('total_paid');
    }

    public function visitCount(): int
    {
        return $this->jobs()->count();
    }
}
