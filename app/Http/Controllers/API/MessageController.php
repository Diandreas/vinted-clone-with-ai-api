<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Conversation $conversation)
    {
        // Check if user is participant
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        // Check if user is participant
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'type' => 'in:text,image,product',
            'product_id' => 'nullable|exists:products,id'
        ]);

        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'product_id' => $request->product_id,
        ]);

        // Update conversation timestamp
        $conversation->touch();

        $message->load('user', 'product');

        // Here you would trigger real-time events with Pusher
        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $message,
            'message' => 'Message sent successfully'
        ], 201);
    }

    public function markAsRead(Message $message)
    {
        // Check if user can read this message
        $conversation = $message->conversation;
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Can't mark own messages as read
        if ($message->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot mark own message as read'
            ], 400);
        }

        $message->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    public function destroy(Message $message)
    {
        // Check if user owns the message
        if ($message->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Soft delete
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function report(Message $message)
    {
        $request = request();
        $request->validate([
            'reason' => 'required|string|in:spam,harassment,inappropriate,other',
            'description' => 'nullable|string|max:500'
        ]);

        // Create report
        $message->reports()->create([
            'reported_by' => Auth::id(),
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message reported successfully'
        ]);
    }
}