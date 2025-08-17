<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\ProductInterest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // For backward compatibility, we can return both product-based and legacy conversations
        $conversations = Conversation::forUser($user)
            ->with(['product', 'buyer', 'seller', 'lastMessage'])
            ->latest('last_message_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations,
            'message' => 'Use /conversations/my-product-discussions for product-centered view'
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
            
            // Use the new product-centered conversation system
            $conversation = Conversation::findOrCreateForProduct($buyer, $seller, $product);
        } else {
            // Legacy system: find or create conversation without product
            $lookup = [
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'product_id' => null,
            ];
            $conversation = Conversation::firstOrCreate($lookup, [
                'is_archived' => false,
                'last_message_at' => now(),
            ]);
        }

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

    /**
     * Get product discussions for buyer (products they are interested in).
     */
    public function myProductDiscussions()
    {
        $user = Auth::user();
        $conversations = Conversation::getProductConversationsForBuyer($user);

        return response()->json([
            'success' => true,
            'data' => $conversations,
            'message' => 'Product discussions retrieved successfully'
        ]);
    }

    /**
     * Get products with buyers for seller (grouped by product).
     */
    public function myProductsWithBuyers()
    {
        $user = Auth::user();
        $conversationsByProduct = Conversation::getProductConversationsForSeller($user);
        
        // Transform to include product info and conversation count
        $productsWithConversations = [];
        foreach ($conversationsByProduct as $productId => $conversations) {
            $firstConversation = $conversations->first();
            if ($firstConversation && $firstConversation->product) {
                $productsWithConversations[] = [
                    'product' => $firstConversation->product,
                    'conversations' => $conversations,
                    'conversation_count' => $conversations->count(),
                    'unread_count' => $conversations->sum(function($conv) use ($user) {
                        return $conv->getUnreadCount($user);
                    }),
                    'last_activity' => $conversations->max('last_message_at')
                ];
            }
        }

        // Sort by last activity
        usort($productsWithConversations, function($a, $b) {
            return $b['last_activity'] <=> $a['last_activity'];
        });

        return response()->json([
            'success' => true,
            'data' => $productsWithConversations,
            'message' => 'Products with conversations retrieved successfully'
        ]);
    }

    /**
     * Start a conversation for a specific product.
     */
    public function startProductConversation(Request $request, Product $product)
    {
        \Log::info('🔵 startProductConversation appelé', [
            'product_id' => $product->id,
            'product_title' => $product->title,
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email
        ]);

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $buyer = Auth::user();

        \Log::info('👤 Utilisateur authentifié', [
            'buyer_id' => $buyer->id,
            'buyer_name' => $buyer->name,
            'product_owner_id' => $product->user_id
        ]);

        // Check if user is not the owner
        if ($product->user_id === $buyer->id) {
            \Log::warning('❌ Utilisateur essaie de contacter son propre produit', [
                'user_id' => $buyer->id,
                'product_id' => $product->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Cannot start conversation with your own product'
            ], 403);
        }

        $seller = $product->user;

        \Log::info('🏪 Vendeur trouvé', [
            'seller_id' => $seller->id,
            'seller_name' => $seller->name
        ]);

        // Create or get conversation
        \Log::info('🔧 Création/récupération de la conversation...');
        $conversation = Conversation::findOrCreateForProduct($buyer, $seller, $product);
        
        \Log::info('💬 Conversation trouvée/créée', [
            'conversation_id' => $conversation->id,
            'is_new' => $conversation->wasRecentlyCreated
        ]);

        // Create first message
        \Log::info('📝 Création du message...');
        $message = $conversation->messages()->create([
            'sender_id' => $buyer->id,
            'content' => $request->message,
            'type' => 'text',
            'product_id' => $product->id,
        ]);

        \Log::info('✅ Message créé', [
            'message_id' => $message->id,
            'content' => $message->content
        ]);

        $conversation->load(['product', 'seller', 'lastMessage']);

        \Log::info('🎉 Conversation démarrée avec succès', [
            'conversation_id' => $conversation->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversation,
            'message' => 'Conversation started successfully'
        ], 201);
    }

    /**
     * Get conversations for a specific product (seller view).
     */
    public function getProductConversations(Product $product)
    {
        $user = Auth::user();

        // Check if user is the product owner
        if ($product->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $conversations = Conversation::where('product_id', $product->id)
            ->with(['buyer', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'conversations' => $conversations,
                'total_conversations' => $conversations->count(),
                'unread_count' => $conversations->sum(function($conv) use ($user) {
                    return $conv->getUnreadCount($user);
                })
            ]
        ]);
    }

    /**
     * Get user's product interests with conversation status.
     */
    public function myProductInterests()
    {
        $user = Auth::user();
        
        $interests = ProductInterest::where('user_id', $user->id)
            ->with(['product', 'conversation.lastMessage'])
            ->active()
            ->orderBy('last_interaction_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $interests,
            'message' => 'Product interests retrieved successfully'
        ]);
    }

    /**
     * Update conversation/interest status.
     */
    public function updateStatus(Request $request, Conversation $conversation)
    {
        $request->validate([
            'status' => 'required|in:interested,negotiating,cancelled,purchased'
        ]);

        $user = Auth::user();

        // Check if user is participant
        if (!($conversation->buyer_id === $user->id || $conversation->seller_id === $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Update product interest status
        if ($conversation->product_id) {
            $interest = ProductInterest::where('product_id', $conversation->product_id)
                ->where('user_id', $conversation->buyer_id)
                ->first();

            if ($interest) {
                $interest->update(['status' => $request->status]);
                $interest->updateInteraction();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}