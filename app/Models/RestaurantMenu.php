<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class RestaurantMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'file_path'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}
