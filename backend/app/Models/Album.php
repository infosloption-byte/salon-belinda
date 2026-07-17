<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'couple_names',
        'event_date',
        'location',
        'category',
        'description',
        'cover_image',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(AlbumPhoto::class)->orderBy('sort_order');
    }

    public static function makeUniqueSlug(string $title): string
    {
        $base = \Illuminate\Support\Str::slug($title);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
