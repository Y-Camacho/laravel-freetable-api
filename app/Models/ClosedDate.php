<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosedDate extends Model
{
    protected $fillable = [
        'restaurant_id',
        'date'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
