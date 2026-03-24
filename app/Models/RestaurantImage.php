<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class RestaurantImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'path',
        'alt',
        'is_cover'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
