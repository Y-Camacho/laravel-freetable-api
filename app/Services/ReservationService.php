<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReservationService
{
    public function createForUser(User $user, Restaurant $restaurant, array $data): Reservation
    {
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'restaurant_id' => $restaurant->id,
            'reservation_time' => $data['reservation_time'],
            'people' => $data['people'],
            'status' => 'pending',
        ]);

        return $reservation->load(['user', 'restaurant']);
    }

    public function listForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Reservation::with(['restaurant'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(max(1, min(50, $perPage)));
    }

    public function listForRestaurant(Restaurant $restaurant, int $perPage = 10): LengthAwarePaginator
    {
        return Reservation::with(['user', 'restaurant'])
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->paginate(max(1, min(50, $perPage)));
    }

    public function updateStatus(Reservation $reservation, string $status): Reservation
    {
        $reservation->update(['status' => $status]);

        return $reservation->load(['user', 'restaurant']);
    }

    public function cancel(Reservation $reservation): Reservation
    {
        $reservation->update(['status' => 'cancelled']);

        return $reservation->load(['user', 'restaurant']);
    }
}
