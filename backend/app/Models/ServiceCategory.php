<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'intro',
        'sort_order',
    ];

    public function services()
    {
        return $this->hasMany(Service::class)->orderBy('sort_order');
    }
}
