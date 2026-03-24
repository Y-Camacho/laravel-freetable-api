<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantImage;
use App\Models\RestaurantMenu;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FileService
{
    public function uploadImage(Restaurant $restaurant, UploadedFile $file, bool $isCover = false, ?string $alt = null): RestaurantImage
    {
        return DB::transaction(function () use ($restaurant, $file, $isCover, $alt) {
            if ($isCover) {
                $restaurant->images()->update(['is_cover' => false]);
            }

            $path = $file->store('restaurants/images', 'public');

            return RestaurantImage::create([
                'restaurant_id' => $restaurant->id,
                'path' => $path,
                'alt' => $alt,
                'is_cover' => $isCover,
            ]);
        });
    }

    public function uploadMenu(Restaurant $restaurant, UploadedFile $file, string $name): RestaurantMenu
    {
        $path = $file->store('restaurants/menus', 'public');

        return RestaurantMenu::create([
            'restaurant_id' => $restaurant->id,
            'name' => $name,
            'file_path' => $path,
        ]);
    }
}
