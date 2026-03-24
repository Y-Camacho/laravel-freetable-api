<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Italiana',
            'Mediterránea',
            'Japonesa',
            'Sushi',
            'Mexicana',
            'Peruana',
            'Asiática',
            'Vegetariana',
            'Vegana',
            'Brasería',
            'Hamburguesería',
            'Pizzería',
            'Marisquería',
            'Tapas',
            'Fusión',
            'India',
            'Libanesa',
            'Argentina',
            'China',
            'Postres',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}