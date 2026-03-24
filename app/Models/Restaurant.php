<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'manager_id'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function images()
    {
        return $this->hasMany(RestaurantImage::class);
    }

    public function menus()
    {
        return $this->hasMany(RestaurantMenu::class);
    }

    // imagen principal
    public function coverImage()
    {
        return $this->hasOne(RestaurantImage::class)->where('is_cover', true);
    }
}
