<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Conversation;
use App\Services\MessageCacheService;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected $cacheService;

    public function __construct(MessageCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index(Request $request, Conversation $conversation)
    {
        // Check if user is participant (buyer or seller)
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 50);

        // Get messages from cache (Redis) or database (MySQL)
        $result = $this->cacheService->getMessages($conversation->id, $page, $perPage);

        // Set user online
        $this->cacheService->setUserOnline(Auth::id());

        // Get unread count for this user
        $unreadCount = $this->cacheService->getUnreadCount($conversation->id, Auth::id());

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => $result['pagination'] ?? null,
            'meta' => [
                'from_cache' => $result['from_cache'],
                'unread_count' => $unreadCount,
            ]
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        // Check if user is participant (buyer or seller)
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
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
            'sender_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'product_id' => $request->product_id,
        ]);

        // Update conversation timestamp
        $conversation->touch();

        $message->load('sender:id,name,avatar,username', 'product');

        // Add message to Redis cache for instant delivery
        $this->cacheService->addMessageToCache($message);

        // Update unread count for other participant
        $otherUserId = $conversation->buyer_id === Auth::id()
            ? $conversation->seller_id
            : $conversation->buyer_id;

        $newUnreadCount = $this->cacheService->getUnreadCount($conversation->id, $otherUserId) + 1;
        $this->cacheService->updateUnreadCount($conversation->id, $otherUserId, $newUnreadCount);

        // Broadcast message via Pusher for real-time delivery
        broadcast(new MessageSent($message))->toOthers();

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
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Can't mark own messages as read
        if ($message->sender_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot mark own message as read'
            ], 400);
        }

        $message->update(['read_at' => now()]);

        // Update unread count in cache
        $unreadCount = $this->cacheService->getUnreadCount($conversation->id, Auth::id());
        $this->cacheService->updateUnreadCount($conversation->id, Auth::id(), max(0, $unreadCount - 1));

        // Invalidate cache to refresh with updated read status
        $this->cacheService->invalidateCache($conversation->id);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * Set user typing indicator.
     */
    public function typing(Conversation $conversation)
    {
        // Check if user is participant
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Set typing indicator in Redis (expires in 10s)
        $this->cacheService->setTyping($conversation->id, Auth::id());

        // Broadcast typing event
        broadcast(new \App\Events\UserTyping($conversation->id, Auth::id()))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Typing indicator set'
        ]);
    }

    /**
     * Get users currently typing.
     */
    public function getTyping(Conversation $conversation)
    {
        // Check if user is participant
        if (!($conversation->buyer_id === Auth::id() || $conversation->seller_id === Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $typingUserIds = $this->cacheService->getTypingUsers($conversation->id);

        return response()->json([
            'success' => true,
            'data' => $typingUserIds
        ]);
    }

    /**
     * Check if user is online.
     */
    public function checkOnline(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $isOnline = $this->cacheService->isUserOnline($request->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $request->user_id,
                'is_online' => $isOnline
            ]
        ]);
    }

    /**
     * Set current user online.
     */
    public function setOnline()
    {
        $this->cacheService->setUserOnline(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Online status updated'
        ]);
    }

    /**
     * Get cache statistics (admin only).
     */
    public function cacheStats()
    {
        $stats = $this->cacheService->getCacheStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function destroy(Message $message)
    {
        // Check if user owns the message
        if ($message->sender_id !== Auth::id()) {
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