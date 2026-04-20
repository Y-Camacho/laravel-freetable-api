<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'latitude',
        'longitude',
        'manager_id'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
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

    public function openingHours()
    {
        return $this->hasMany(OpeningHour::class);
    }

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function config()
    {
        return $this->hasOne(RestaurantConfig::class);
    }

    public function closedDates()
    {
        return $this->hasMany(ClosedDate::class);
    }

    // imagen principal
    public function coverImage()
    {
        return $this->hasOne(RestaurantImage::class)->where('is_cover', true);
    }

    // Categorias
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_restaurant');
    }
}
