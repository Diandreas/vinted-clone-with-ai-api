<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::with(['user'])
            ->where('expires_at', '>', now())
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $stories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:image,video,text,product',
            'content' => 'required|string',
        ]);

        $story = Story::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'content' => $request->content,
            'expires_at' => now()->addDay(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $story,
            'message' => 'Story created successfully'
        ], 201);
    }

    public function show(Story $story)
    {
        $story->load(['user']);

        return response()->json([
            'success' => true,
            'data' => $story
        ]);
    }

    public function destroy(Story $story)
    {
        if ($story->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $story->delete();

        return response()->json([
            'success' => true,
            'message' => 'Story deleted successfully'
        ]);
    }

    public function view(Story $story)
    {
        // Record view
        return response()->json([
            'success' => true,
            'message' => 'View recorded'
        ]);
    }

    public function viewers(Story $story)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function myStories()
    {
        $stories = auth()->user()->stories()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $stories
        ]);
    }

    public function followingStories()
    {
        $user = auth()->user();
        $followingIds = $user->following()->pluck('following_id');

        $stories = Story::with(['user'])
            ->whereIn('user_id', $followingIds)
            ->where('expires_at', '>', now())
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $stories
        ]);
    }
}