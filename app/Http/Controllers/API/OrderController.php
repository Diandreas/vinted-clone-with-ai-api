<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders_as_buyer()
            ->with(['product', 'seller'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'shipping_address' => 'required|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        if ($product->status !== Product::STATUS_ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available'
            ], 400);
        }

        $order = Order::create([
            'buyer_id' => auth()->id(),
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'total_amount' => $product->price,
            'product_price' => $product->price,
            'shipping_address' => $request->shipping_address,
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order created successfully'
        ], 201);
    }

    public function show(Order $order)
    {
        if ($order->buyer_id !== auth()->id() && $order->seller_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load(['product', 'buyer', 'seller']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function updateStatus(Order $order, Request $request)
    {
        if ($order->seller_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:confirmed,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $order->fresh(),
            'message' => 'Order status updated successfully'
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->buyer_id !== auth()->id() && $order->seller_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->cancel();

        return response()->json([
            'success' => true,
            'data' => $order->fresh(),
            'message' => 'Order cancelled successfully'
        ]);
    }

    public function dispute(Order $order)
    {
        if ($order->buyer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Handle dispute logic here
        return response()->json([
            'success' => true,
            'message' => 'Dispute opened successfully'
        ]);
    }

    public function purchases()
    {
        $orders = auth()->user()->orders_as_buyer()
            ->with(['product', 'seller'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function sales()
    {
        $orders = auth()->user()->orders_as_seller()
            ->with(['product', 'buyer'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function pending()
    {
        $orders = Order::where(function($query) {
                $query->where('buyer_id', auth()->id())
                      ->orWhere('seller_id', auth()->id());
            })
            ->pending()
            ->with(['product', 'buyer', 'seller'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}