<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService)
    {
    }

    public function store(Request $request, Restaurant $restaurant): JsonResponse
    {
        $data = $request->validate([
            'reservation_time' => 'required|date|after:now',
            'people' => 'required|integer|min:1|max:30',
        ]);

        $reservation = $this->reservationService->createForUser($request->user(), $restaurant, $data);

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'data' => new ReservationResource($reservation),
        ], 201);
    }

    public function indexMine(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $reservations = $this->reservationService->listForUser($request->user(), $perPage);

        return ReservationResource::collection($reservations);
    }

    public function indexByRestaurant(Request $request, Restaurant $restaurant)
    {
        if (!$this->canManageRestaurantReservations($request, $restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $perPage = (int) $request->input('per_page', 10);
        $reservations = $this->reservationService->listForRestaurant($restaurant, $perPage);

        return ReservationResource::collection($reservations);
    }

    public function updateStatus(Request $request, Reservation $reservation): JsonResponse
    {
        if (!$this->canManageRestaurantReservations($request, $reservation->restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $updated = $this->reservationService->updateStatus($reservation, $data['status']);

        return response()->json([
            'message' => 'Estado de reserva actualizado correctamente',
            'data' => new ReservationResource($updated),
        ]);
    }

    public function destroy(Request $request, Reservation $reservation): JsonResponse
    {
        $user = $request->user();
        $canCancelOwn = (int) $reservation->user_id === (int) $user->id;
        $canManageRestaurant = $this->canManageRestaurantReservations($request, $reservation->restaurant);

        if (!$canCancelOwn && !$canManageRestaurant) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $cancelled = $this->reservationService->cancel($reservation);

        return response()->json([
            'message' => 'Reserva cancelada correctamente',
            'data' => new ReservationResource($cancelled),
        ]);
    }

    private function canManageRestaurantReservations(Request $request, Restaurant $restaurant): bool
    {
        $user = $request->user();

        return $user->isAdmin() || ($user->isManager() && (int) $restaurant->manager_id === (int) $user->id);
    }
}
