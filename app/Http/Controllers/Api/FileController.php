<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function uploadImage(Request $request, $restaurantId)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'is_cover' => 'boolean'
        ]);

        $path = $request->file('image')->store('restaurants/images', 'public');

        $image = RestaurantImage::create([
            'restaurant_id' => $restaurantId,
            'path' => $path,
            'is_cover' => $request->is_cover ?? false
        ]);

        return response()->json($image);
    }    

    public function uploadMenu(Request $request, $restaurantId)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:5120',
            'name' => 'required|string'
        ]);

        $path = $request->file('file')->store('restaurants/menus', 'public');

        $menu = RestaurantMenu::create([
            'restaurant_id' => $restaurantId,
            'name' => $request->name,
            'file_path' => $path
        ]);

        return response()->json($menu);
    }
}
