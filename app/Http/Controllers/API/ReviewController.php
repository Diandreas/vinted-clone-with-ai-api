<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reviewee_id' => 'required|exists:users,id|different:' . Auth::id(),
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'type' => 'required|in:seller,buyer'
        ]);

        // Verify the order exists and user is part of it
        $order = Order::findOrFail($request->order_id);
        
        $canReview = false;
        if ($request->type === 'seller' && $order->buyer_id === Auth::id() && $order->seller_id === $request->reviewee_id) {
            $canReview = true;
        } elseif ($request->type === 'buyer' && $order->seller_id === Auth::id() && $order->buyer_id === $request->reviewee_id) {
            $canReview = true;
        }

        if (!$canReview) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review users from completed orders'
            ], 403);
        }

        // Check if review already exists
        $existingReview = Review::where([
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $request->reviewee_id,
            'order_id' => $request->order_id,
            'type' => $request->type
        ])->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this user for this order'
            ], 400);
        }

        $review = Review::create([
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $request->reviewee_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'type' => $request->type
        ]);

        $review->load(['reviewer', 'reviewee', 'order.product']);

        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Review created successfully'
        ], 201);
    }

    public function update(Review $review, Request $request)
    {
        if ($review->reviewer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        $review->load(['reviewer', 'reviewee', 'order.product']);

        return response()->json([
            'success' => true,
            'data' => $review,
            'message' => 'Review updated successfully'
        ]);
    }

    public function destroy(Review $review)
    {
        if ($review->reviewer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    public function received()
    {
        $reviews = Auth::user()->reviewsReceived()
            ->with(['reviewer', 'order.product'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function given()
    {
        $reviews = Auth::user()->reviewsGiven()
            ->with(['reviewee', 'order.product'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}