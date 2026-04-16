<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;

use App\Services\AvailabilityService;

class AvailabilityController extends Controller
{
    public function index(Request $request, $restaurantId, AvailabilityService $service)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $slots = $service->getAvailableSlots(
            $restaurant,
            $request->date,
            $request->people
        );

        return response()->json($slots);
    }
}
