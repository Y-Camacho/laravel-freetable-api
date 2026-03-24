<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
