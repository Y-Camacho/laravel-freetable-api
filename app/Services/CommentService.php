<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public function listByRestaurant(Restaurant $restaurant, int $perPage = 10): LengthAwarePaginator
    {
        return Comment::with(['user'])
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->paginate(max(1, min(50, $perPage)));
    }

    public function create(Restaurant $restaurant, User $user, array $data): Comment
    {
        $comment = Comment::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'content' => $data['content'],
            'rating' => (float) $data['rating'],
        ]);

        return $comment->load(['user', 'restaurant']);
    }

    public function ratingSummaryByRestaurant(Restaurant $restaurant): array
    {
        $summary = Comment::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereNotNull('rating')
            ->selectRaw('COUNT(*) as ratings_count, AVG(rating) as average_rating')
            ->first();

        $average = $summary?->average_rating !== null
            ? round((float) $summary->average_rating, 1)
            : null;

        return [
            'average_rating' => $average,
            'ratings_count' => (int) ($summary?->ratings_count ?? 0),
        ];
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
