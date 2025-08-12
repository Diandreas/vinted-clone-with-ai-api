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
        $user = Auth::user();
        $conversations = Conversation::forUser($user)
            ->with(['buyer', 'seller', 'lastMessage'])
            ->latest('last_message_at')
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
            'product_id' => 'nullable|exists:products,id'
        ]);

        $current = Auth::user();
        $participant = User::findOrFail($request->participant_id);

        // Determine buyer/seller
        $buyer = $current;
        $seller = $participant;

        $product = null;
        if ($request->product_id) {
            $product = \App\Models\Product::findOrFail($request->product_id);
            $seller = $product->user;
            if ($seller->id === $current->id) {
                $buyer = $participant;
            }
        }

        // Find or create conversation
        $lookup = [
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product?->id,
        ];
        $conversation = Conversation::firstOrCreate($lookup, [
            'is_archived' => false,
            'last_message_at' => now(),
        ]);

        // First message
        $conversation->messages()->create([
            'sender_id' => $current->id,
            'content' => $request->message,
            'type' => 'text',
            'product_id' => $product?->id,
        ]);

        $conversation->load(['buyer', 'seller', 'lastMessage']);

        return response()->json([
            'success' => true,
            'data' => $conversation,
            'message' => 'Conversation created successfully'
        ], 201);
    }

    public function show(Conversation $conversation)
    {
        // Check if user is participant (buyer or seller)
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $conversation->load(['buyer', 'seller', 'messages' => function($query) {
            $query->with('sender')->latest()->limit(50);
        }]);

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
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
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Soft delete conversation
        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }
}