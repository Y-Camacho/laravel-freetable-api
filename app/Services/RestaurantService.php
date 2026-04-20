<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RestaurantService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Restaurant::query()->with(['manager', 'categories', 'coverImage']);

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        $perPage = max(1, min(50, (int) ($filters['per_page'] ?? 10)));

        return $query->latest()->paginate($perPage);
    }

    public function nearby(array $filters): LengthAwarePaginator
    {
        $latitude = (float) $filters['lat'];
        $longitude = (float) $filters['lng'];
        $radius = max(0.1, (float) ($filters['radius'] ?? 10));
        $perPage = max(1, min(50, (int) ($filters['per_page'] ?? 10)));

        $distanceExpression = '(6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude))))';

        $baseQuery = DB::table('restaurants')
            ->select('id')
            ->selectRaw("{$distanceExpression} AS distance", [$latitude, $longitude, $latitude])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Compute the Haversine distance in SQL, then filter and sort in the outer query.
        $nearbyQuery = DB::query()
            ->fromSub($baseQuery, 'nearby_restaurants')
            ->select('id', 'distance')
            ->where('distance', '<=', $radius)
            ->orderBy('distance');

        /** @var Paginator $paginator */
        $paginator = $nearbyQuery->paginate($perPage, ['*'], 'page', Paginator::resolveCurrentPage())
            ->appends([
                'lat' => $latitude,
                'lng' => $longitude,
                'radius' => $radius,
                'per_page' => $perPage,
            ]);

        $restaurants = $this->mapNearbyRestaurants($paginator->getCollection());
        $paginator->setCollection($restaurants);

        return $paginator;
    }

    public function findById(int|string $id): Restaurant
    {
        return Restaurant::with(['manager', 'categories', 'coverImage', 'images', 'menus'])
            ->findOrFail($id);
    }

    public function create(array $data, User $actor): Restaurant
    {
        if ($actor->isManager()) {
            $data['manager_id'] = $actor->id;
        }

        $restaurant = Restaurant::create($data);

        if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
            $restaurant->categories()->sync($data['category_ids']);
        }

        return $restaurant->load(['manager', 'categories', 'coverImage']);
    }

    public function update(Restaurant $restaurant, array $data): Restaurant
    {
        $restaurant->update($data);

        if (array_key_exists('category_ids', $data) && is_array($data['category_ids'])) {
            $restaurant->categories()->sync($data['category_ids']);
        }

        return $restaurant->load(['manager', 'categories', 'coverImage']);
    }

    public function delete(Restaurant $restaurant): void
    {
        $restaurant->delete();
    }

    public function syncCategories(Restaurant $restaurant, array $categoryIds): Restaurant
    {
        $restaurant->categories()->sync($categoryIds);

        return $restaurant->load(['manager', 'categories', 'coverImage']);
    }

    private function mapNearbyRestaurants(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $restaurantIds = $rows->pluck('id')->all();

        $restaurants = Restaurant::query()
            ->with(['manager', 'categories', 'coverImage'])
            ->whereIn('id', $restaurantIds)
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($restaurants) {
            $restaurant = $restaurants->get($row->id);

            if (!$restaurant) {
                return null;
            }

            $restaurant->distance = round((float) $row->distance, 3);

            return $restaurant;
        })->filter()->values();
    }
}
