<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RestaurantController;

use Illuminate\Support\Facades\Route;

Route::get("/health", function() {
    return response()->json([
            'message' => 'Esto es freetable'
        ]);
});

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

});

Route::get('/categories', [RestaurantController::class, 'categories']);
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{restaurant}/comments', [CommentController::class, 'indexByRestaurant']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::match(['put', 'patch'], '/restaurants/{restaurant}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
    Route::put('/restaurants/{restaurant}/categories', [RestaurantController::class, 'syncCategories']);

    Route::post('/restaurants/{restaurant}/images', [FileController::class, 'uploadImage']);
    Route::post('/restaurants/{restaurant}/menus', [FileController::class, 'uploadMenu']);

    Route::post('/restaurants/{restaurant}/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/me', [ReservationController::class, 'indexMine']);
    Route::get('/restaurants/{restaurant}/reservations', [ReservationController::class, 'indexByRestaurant']);
    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);

    Route::post('/restaurants/{restaurant}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});