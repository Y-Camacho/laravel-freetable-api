<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_time' => $this->reservation_time,
            'people' => $this->people,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
        ];
    }
}
