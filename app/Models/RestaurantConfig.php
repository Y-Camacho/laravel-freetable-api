<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantConfig extends Model
{
    protected $fillable = [
        'restaurant_id',
        'slot_duration',     // ej: 30 min
        'reservation_duration', // ej: 90 min
        'buffer_minutes'     // ej: 15 min
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
