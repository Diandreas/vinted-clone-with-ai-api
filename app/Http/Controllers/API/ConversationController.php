<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['participants', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:users,id|different:' . Auth::id(),
            'message' => 'required|string|max:1000',
        ]);

        $participant = User::findOrFail($request->participant_id);

        // Check if conversation already exists
        $existingConversation = Auth::user()->conversations()
            ->whereHas('participants', function($query) use ($participant) {
                $query->where('user_id', $participant->id);
            })
            ->first();

        if ($existingConversation) {
            return response()->json([
                'success' => true,
                'data' => $existingConversation->load(['participants', 'messages' => function($query) {
                    $query->latest()->limit(10);
                }]),
                'message' => 'Conversation already exists'
            ]);
        }

        // Create new conversation
        $conversation = Conversation::create([
            'created_by' => Auth::id(),
        ]);

        // Add participants
        $conversation->participants()->attach([Auth::id(), $participant->id]);

        // Create first message
        $conversation->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->message,
            'type' => 'text'
        ]);

        $conversation->load(['participants', 'messages' => function($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $conversation,
            'message' => 'Conversation created successfully'
        ], 201);
    }

    public function show(Conversation $conversation)
    {
        // Check if user is participant
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $conversation->load(['participants', 'messages' => function($query) {
            $query->with('user')->latest()->limit(50);
        }]);

        // Mark messages as read
        $conversation->messages()
            ->where('user_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    public function destroy(Conversation $conversation)
    {
        // Check if user is participant
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Soft delete for the current user only
        $conversation->participants()
            ->where('user_id', Auth::id())
            ->update(['deleted_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }
}