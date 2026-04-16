<?php

namespace Database\Seeders;

use App\Models\ClosedDate;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\RestaurantConfig;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RestaurantSetupSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::all();

        if ($restaurants->isEmpty()) {
            $this->command?->warn('No hay restaurantes. Ejecuta InitialDataSeeder primero.');
            return;
        }

        foreach ($restaurants as $restaurant) {

            // Limpieza previa
            OpeningHour::where('restaurant_id', $restaurant->id)->delete();
            RestaurantTable::where('restaurant_id', $restaurant->id)->delete();
            RestaurantConfig::where('restaurant_id', $restaurant->id)->delete();
            ClosedDate::where('restaurant_id', $restaurant->id)->delete();

            // Configuración base
            RestaurantConfig::create([
                'restaurant_id' => $restaurant->id,
                'slot_duration' => 30,        // cada 30 min
                'reservation_duration' => 90, // reserva dura 1h30
                'buffer_minutes' => 15,
            ]);

            // Horarios (lunes a domingo)
            foreach (range(1, 7) as $day) {

                // Domingo (0 en Carbon, pero aquí usamos 7 como domingo opcional)
                $dayOfWeek = $day % 7;

                // Ejemplo:
                // Lunes a jueves → comida + cena
                // Viernes a domingo → horario más largo

                if ($dayOfWeek >= 1 && $dayOfWeek <= 4) {
                    // L-J
                    OpeningHour::create([
                        'restaurant_id' => $restaurant->id,
                        'day_of_week' => $dayOfWeek,
                        'open_time' => '13:00',
                        'close_time' => '16:00',
                    ]);

                    OpeningHour::create([
                        'restaurant_id' => $restaurant->id,
                        'day_of_week' => $dayOfWeek,
                        'open_time' => '20:00',
                        'close_time' => '23:00',
                    ]);

                } else {
                    // V-D (más amplio)
                    OpeningHour::create([
                        'restaurant_id' => $restaurant->id,
                        'day_of_week' => $dayOfWeek,
                        'open_time' => '13:00',
                        'close_time' => '23:30',
                    ]);
                }
            }

            // Mesas (mix realista)
            $tables = [
                ['capacity' => 2, 'count' => 4],
                ['capacity' => 4, 'count' => 5],
                ['capacity' => 6, 'count' => 3],
                ['capacity' => 8, 'count' => 1],
            ];

            foreach ($tables as $group) {
                for ($i = 0; $i < $group['count']; $i++) {
                    RestaurantTable::create([
                        'restaurant_id' => $restaurant->id,
                        'capacity' => $group['capacity'],
                    ]);
                }
            }

            // Días cerrados (2 fechas únicas para evitar duplicados)
            $closedDayOffsets = collect(range(3, 30))->random(2);

            foreach ($closedDayOffsets as $offset) {
                ClosedDate::create([
                    'restaurant_id' => $restaurant->id,
                    'date' => Carbon::now()->addDays($offset)->toDateString(),
                ]);
            }
        }
    }
}