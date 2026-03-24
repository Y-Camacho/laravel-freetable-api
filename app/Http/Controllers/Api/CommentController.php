<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Restaurant;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    public function indexByRestaurant(Request $request, Restaurant $restaurant)
    {
        $perPage = (int) $request->input('per_page', 10);
        $comments = $this->commentService->listByRestaurant($restaurant, $perPage);
        $ratingSummary = $this->commentService->ratingSummaryByRestaurant($restaurant);

        return CommentResource::collection($comments)->additional([
            'meta' => $ratingSummary,
        ]);
    }

    public function store(Request $request, Restaurant $restaurant): JsonResponse
    {
        $data = $request->validate([
            'content' => 'required|string|min:2|max:2000',
            'rating' => ['required', 'regex:/^(?:[1-4](?:\\.5)?|5(?:\\.0)?)$/'],
        ]);

        $comment = $this->commentService->create($restaurant, $request->user(), $data);

        return response()->json([
            'message' => 'Comentario creado correctamente',
            'data' => new CommentResource($comment),
        ], 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        $canDeleteOwn = (int) $comment->user_id === (int) $user->id;
        $canDeleteAsAdmin = $user->isAdmin();
        $canDeleteAsManager = $user->isManager() && (int) $comment->restaurant->manager_id === (int) $user->id;

        if (!$canDeleteOwn && !$canDeleteAsAdmin && !$canDeleteAsManager) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $this->commentService->delete($comment);

        return response()->json(['message' => 'Comentario eliminado correctamente']);
    }
}
