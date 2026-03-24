<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurant_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();

            $table->string('path'); // ruta del archivo
            $table->string('alt')->nullable();

            $table->boolean('is_cover')->default(false); // imagen principal

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_images');
    }
};
