<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestaurantImageResource;
use App\Http\Resources\RestaurantMenuResource;
use App\Models\Restaurant;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(private readonly FileService $fileService)
    {
    }

    public function uploadImage(Request $request, Restaurant $restaurant): JsonResponse
    {
        if (!$this->canManageRestaurant($request, $restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'image' => 'required|image|max:2048',
            'is_cover' => 'boolean',
            'alt' => 'nullable|string|max:255',
        ]);

        $image = $this->fileService->uploadImage(
            $restaurant,
            $request->file('image'),
            (bool) $request->boolean('is_cover'),
            $request->input('alt')
        );

        return response()->json([
            'message' => 'Imagen subida correctamente',
            'data' => new RestaurantImageResource($image),
        ], 201);
    }    

    public function uploadMenu(Request $request, Restaurant $restaurant): JsonResponse
    {
        if (!$this->canManageRestaurant($request, $restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'file' => 'required|mimes:pdf|max:5120',
            'name' => 'required|string'
        ]);

        $menu = $this->fileService->uploadMenu(
            $restaurant,
            $request->file('file'),
            $request->input('name')
        );

        return response()->json([
            'message' => 'Menu subido correctamente',
            'data' => new RestaurantMenuResource($menu),
        ], 201);
    }

    private function canManageRestaurant(Request $request, Restaurant $restaurant): bool
    {
        $user = $request->user();

        return $user->isAdmin() || ($user->isManager() && (int) $restaurant->manager_id === (int) $user->id);
    }
}
