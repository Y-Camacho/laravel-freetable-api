<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $fillable = [
        'restaurant_id',
        'capacity'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}