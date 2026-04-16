<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Reservation;
use Carbon\Carbon;

class AvailabilityService
{
    public function getAvailableSlots(Restaurant $restaurant, string $date, int $people)
    {
        // 1. Check cerrado
        if ($restaurant->closedDates()->where('date', $date)->exists()) {
            return [];
        }

        // 2. Horario
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $hours = $restaurant->openingHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$hours) return [];

        // 3. Generar slots
        $slots = [];
        $availableSlots = [];

        $start = Carbon::parse($date . ' ' . $hours->open_time);
        $end = Carbon::parse($date . ' ' . $hours->close_time);

        $interval = $restaurant->config->slot_duration;

        while ($start < $end) {
            $slots[] = $start->copy();
            $start->addMinutes($interval);
        }

        // 4. Filtrar disponibilidad
        foreach ($slots as $slot) {

            $reservations = Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_time', [
                    $slot,
                    $slot->copy()->addMinutes($restaurant->config->reservation_duration)
                ])
                ->get();

            $occupiedTableIds = $reservations->pluck('table_id');

            $availableTables = $restaurant->tables()
                ->whereNotIn('id', $occupiedTableIds)
                ->where('capacity', '>=', $people)
                ->orderBy('capacity')
                ->get();

            if ($availableTables->isNotEmpty()) {
                $availableSlots[] = $slot->format('H:i');
            }
        }

        return $availableSlots;
    }
}