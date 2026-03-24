<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantMenu extends Model
{
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
