<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'publish_year',
        'description',
        'category_id',
        'stock',
        'total_copies',
        'cover_path',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Optional scope to check availability
    public function scopeAvailable($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function getCoverUrlAttribute()
    {
        return $this->cover_path
            ? asset('storage/' . $this->cover_path)
            : null;
    }

    public function getCategoryNameAttribute()
    {
        return $this->category->name ?? 'Uncategorized';
    }
}
