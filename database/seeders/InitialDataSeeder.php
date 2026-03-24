<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use App\Models\RestaurantMenu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $clients = User::where('role', 'client')->get();

        $commentSamples = [
            'Muy buena experiencia, volveremos pronto.',
            'Servicio rapido y comida sabrosa.',
            'El ambiente es agradable y tranquilo.',
            'Buena relacion calidad-precio.',
            'Platos bien presentados y porciones correctas.',
            'Excelente atencion del personal.',
            'Nos encanto la comida y el trato.',
            'Lugar recomendado para cenar en pareja.',
            'Una opcion muy buena para ir con amigos.',
            'Volveria sin duda, gran experiencia.',
        ];

        if ($categories->count() < 6) {
            $this->command?->warn('No hay suficientes categorías. Ejecuta CategoriesSeeder primero.');
            return;
        }

        if ($clients->isEmpty()) {
            $this->command?->warn('No hay clientes disponibles. Ejecuta UsersSeeder primero.');
            return;
        }

        $imageFiles = collect(File::files(public_path('storage/restaurants/images')))
            ->map(fn ($file) => 'restaurants/images/' . $file->getFilename())
            ->values();

        if ($imageFiles->count() < 4) {
            $this->command?->warn('Se necesitan al menos 4 imágenes en public/storage/restaurants/images.');
            return;
        }

        $menuFiles = collect(File::files(public_path('storage/restaurants/menus')))
            ->map(fn ($file) => 'restaurants/menus/' . $file->getFilename())
            ->sort()
            ->values();

        if ($menuFiles->count() < 3) {
            $this->command?->warn('Se necesitan al menos 3 menús en public/storage/restaurants/menus.');
            return;
        }

        $menuFiles = $menuFiles->take(3);

        for ($i = 1; $i <= 10; $i++) {
            $manager = User::firstOrCreate(
                ['email' => "manager{$i}@freetable.test"],
                [
                    'name' => "Manager {$i}",
                    'password' => 'password',
                    'role' => 'manager',
                ]
            );

            if ($manager->role !== 'manager') {
                $manager->update(['role' => 'manager']);
            }

            if (method_exists($manager, 'assignRole') && !$manager->hasRole('manager')) {
                $manager->assignRole('manager');
            }

            $restaurant = Restaurant::updateOrCreate(
                ['manager_id' => $manager->id],
                [
                    'name' => "Restaurante {$i}",
                    'description' => "Descripción inicial del Restaurante {$i}.",
                    'address' => "Calle Demo {$i}, Ciudad",
                    'phone' => '600000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                ]
            );

            $categoryIds = $categories
                ->random(rand(2, 6))
                ->pluck('id')
                ->all();

            $restaurant->categories()->sync($categoryIds);

            $restaurant->images()->delete();
            $restaurant->menus()->delete();
            $restaurant->comments()->delete();

            $selectedImages = $imageFiles->shuffle()->take(4)->values();

            RestaurantImage::create([
                'restaurant_id' => $restaurant->id,
                'path' => $selectedImages[0],
                'alt' => Str::of($restaurant->name)->append(' - Cover')->toString(),
                'is_cover' => true,
            ]);

            foreach ($selectedImages->slice(1) as $index => $imagePath) {
                RestaurantImage::create([
                    'restaurant_id' => $restaurant->id,
                    'path' => $imagePath,
                    'alt' => Str::of($restaurant->name)->append(' - Imagen ' . ($index + 1))->toString(),
                    'is_cover' => false,
                ]);
            }

            foreach ($menuFiles as $menuPath) {
                RestaurantMenu::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => Str::of(pathinfo($menuPath, PATHINFO_FILENAME))
                        ->replace('-', ' ')
                        ->title()
                        ->toString(),
                    'file_path' => $menuPath,
                ]);
            }

            $totalComments = rand(4, 10);

            for ($commentIndex = 0; $commentIndex < $totalComments; $commentIndex++) {
                $author = $clients->random();

                Comment::create([
                    'user_id' => $author->id,
                    'restaurant_id' => $restaurant->id,
                    'content' => $commentSamples[array_rand($commentSamples)],
                    'rating' => rand(30, 50) / 10,
                ]);
            }
        }
    }
}