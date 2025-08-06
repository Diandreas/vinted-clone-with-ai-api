<?php
// app/Http/Controllers/API/LiveController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\LiveComment;
use App\Models\LiveLike;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveController extends Controller
{
    public function index(Request $request)
    {
        $lives = Live::with(['user'])
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            }, function($query) {
                $query->whereIn('status', ['scheduled', 'live']);
            })
            ->when($request->user_id, function($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('status', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $lives
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $live = Live::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $request->user()->id,
            'stream_key' => Str::random(32),
            'status' => $request->scheduled_at ? 'scheduled' : 'live',
            'scheduled_at' => $request->scheduled_at,
            'started_at' => $request->scheduled_at ? null : now(),
        ]);

        // Mettre à jour le statut de l'utilisateur
        if (!$request->scheduled_at) {
            $request->user()->update(['is_live' => true]);
        }

        return response()->json([
            'success' => true,
            'data' => $live->load('user'),
            'message' => 'Live created successfully'
        ], 201);
    }

    public function show(Live $live)
    {
        $live->load(['user', 'comments' => function($query) {
            $query->with('user')->latest()->limit(50);
        }]);

        return response()->json([
            'success' => true,
            'data' => $live
        ]);
    }

    public function update(Request $request, Live $live)
    {
        $this->authorize('update', $live);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:scheduled,live,ended,cancelled',
        ]);

        $live->update($request->all());

        // Gérer les changements de statut
        if ($request->status === 'live' && $live->status !== 'live') {
            $live->update([
                'started_at' => now(),
                'status' => 'live'
            ]);
            $live->user->update(['is_live' => true]);
        } elseif (in_array($request->status, ['ended', 'cancelled'])) {
            $live->update([
                'ended_at' => now(),
                'status' => $request->status
            ]);
            $live->user->update(['is_live' => false]);
        }

        return response()->json([
            'success' => true,
            'data' => $live,
            'message' => 'Live updated successfully'
        ]);
    }

    public function addComment(Request $request, Live $live)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = LiveComment::create([
            'live_id' => $live->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        $live->increment('comments_count');

        return response()->json([
            'success' => true,
            'data' => $comment->load('user'),
            'message' => 'Comment added successfully'
        ], 201);
    }

    public function like(Request $request, Live $live)
    {
        $user = $request->user();

        $existingLike = LiveLike::where('live_id', $live->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $live->decrement('likes_count');
            $message = 'Live unliked';
        } else {
            LiveLike::create([
                'live_id' => $live->id,
                'user_id' => $user->id,
            ]);
            $live->increment('likes_count');
            $message = 'Live liked';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function joinLive(Live $live)
    {
        if ($live->status === 'live') {
            $live->increment('viewers_count');

            // Mettre à jour le max_viewers si nécessaire
            if ($live->viewers_count > $live->max_viewers) {
                $live->update(['max_viewers' => $live->viewers_count]);
            }
        }

        return response()->json([
            'success' => true,
            'viewers_count' => $live->viewers_count
        ]);
    }

    public function leaveLive(Live $live)
    {
        if ($live->status === 'live' && $live->viewers_count > 0) {
            $live->decrement('viewers_count');
        }

        return response()->json([
            'success' => true,
            'viewers_count' => $live->viewers_count
        ]);
    }
}
