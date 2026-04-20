<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'phone' => $this->phone,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance' => isset($this->distance) ? round((float) $this->distance, 3) : null,
            'manager_id' => $this->manager_id,
            'created_at' => $this->created_at,
            'manager' => new UserResource($this->whenLoaded('manager')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'cover_image' => new RestaurantImageResource($this->whenLoaded('coverImage')),
            'images' => RestaurantImageResource::collection($this->whenLoaded('images')),
            'menus' => RestaurantMenuResource::collection($this->whenLoaded('menus')),
        ];
    }
}
