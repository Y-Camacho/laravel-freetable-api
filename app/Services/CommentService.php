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
        ]);

        return $comment->load(['user', 'restaurant']);
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
