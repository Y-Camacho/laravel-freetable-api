<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningHour extends Model
{
    protected $fillable = [
        'restaurant_id',
        'day_of_week', // 0 (domingo) - 6 (sábado)
        'open_time',
        'close_time'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}