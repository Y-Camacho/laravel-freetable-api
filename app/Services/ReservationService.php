<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationService
{
    public function createForUser(User $user, Restaurant $restaurant, array $data): Reservation
    {
        return DB::transaction(function () use ($user, $restaurant, $data) {

            $dateTime = Carbon::parse($data['reservation_time']);
            $people = $data['people'];

            // 🔎 Buscar mesa disponible
            $table = $this->findAvailableTable($restaurant, $dateTime, $people);

            if (!$table) {
                throw new \Exception('No hay disponibilidad para esa hora');
            }

            // ✅ Crear reserva
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'reservation_time' => $dateTime,
                'people' => $people,
                'status' => 'pending',
                'table_id' => $table->id, // 👈 IMPORTANTE
            ]);

            return $reservation->load(['user', 'restaurant']);
        });
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

    private function findAvailableTable(Restaurant $restaurant, Carbon $dateTime, int $people)
    {
        $duration = $restaurant->config->reservation_duration;

        // Reservas que se solapan
        $reservations = Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_time', [
                $dateTime,
                $dateTime->copy()->addMinutes($duration)
            ])
            ->get();

        $occupiedTableIds = $reservations->pluck('table_id');

        return $restaurant->tables()
            ->whereNotIn('id', $occupiedTableIds)
            ->where('capacity', '>=', $people)
            ->orderBy('capacity') // mejor ajuste
            ->first();
    }
}
