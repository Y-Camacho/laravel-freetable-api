<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\RestaurantResource;
use App\Models\Category;
use App\Models\Restaurant;
use App\Services\RestaurantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function __construct(private readonly RestaurantService $restaurantService)
    {
    }

    public function index(Request $request)
    {
        $restaurants = $this->restaurantService->list($request->only([
            'search',
            'category_id',
            'per_page',
        ]));

        return RestaurantResource::collection($restaurants);
    }

    public function nearby(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|gt:0|max:1000',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parámetros inválidos',
                'errors' => $validator->errors(),
            ], 400);
        }

        $restaurants = $this->restaurantService->nearby($validator->validated());

        return RestaurantResource::collection($restaurants);
    }

    public function show(Restaurant $restaurant): RestaurantResource
    {
        $restaurant = $this->restaurantService->findById($restaurant->id);

        return new RestaurantResource($restaurant);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_id' => 'nullable|exists:users,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $restaurant = $this->restaurantService->create($data, $user);

        return response()->json([
            'message' => 'Restaurante creado correctamente',
            'data' => new RestaurantResource($restaurant),
        ], 201);
    }

    public function update(Request $request, Restaurant $restaurant): JsonResponse
    {
        if (!$this->canManageRestaurant($request, $restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_id' => 'sometimes|nullable|exists:users,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if (!$request->user()->isAdmin()) {
            unset($data['manager_id']);
        }

        $updated = $this->restaurantService->update($restaurant, $data);

        return response()->json([
            'message' => 'Restaurante actualizado correctamente',
            'data' => new RestaurantResource($updated),
        ]);
    }

    public function destroy(Request $request, Restaurant $restaurant): JsonResponse
    {
        if (!$this->canManageRestaurant($request, $restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $this->restaurantService->delete($restaurant);

        return response()->json(['message' => 'Restaurante eliminado correctamente']);
    }

    public function syncCategories(Request $request, Restaurant $restaurant): JsonResponse
    {
        if (!$this->canManageRestaurant($request, $restaurant)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $updated = $this->restaurantService->syncCategories($restaurant, $data['category_ids']);

        return response()->json([
            'message' => 'Categorias actualizadas correctamente',
            'data' => new RestaurantResource($updated),
        ]);
    }

    public function categories()
    {
        return CategoryResource::collection(Category::query()->orderBy('name')->get());
    }

    private function canManageRestaurant(Request $request, Restaurant $restaurant): bool
    {
        $user = $request->user();

        return $user->isAdmin() || ($user->isManager() && (int) $restaurant->manager_id === (int) $user->id);
    }
}


