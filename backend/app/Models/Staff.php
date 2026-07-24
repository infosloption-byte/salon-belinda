<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'name',
        'role_title',
        'phone',
        'commission_percent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function jobItems()
    {
        return $this->hasMany(JobItem::class);
    }

    public function shifts()
    {
        return $this->hasMany(StaffShift::class);
    }

    /** Services this staff member is qualified to perform. */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_staff');
    }
}
