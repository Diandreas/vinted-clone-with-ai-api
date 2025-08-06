<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['product.images', 'buyer', 'seller'])
            ->where(function ($query) use ($request) {
                $query->where('buyer_id', $request->user()->id)
                    ->orWhere('seller_id', $request->user()->id);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->type, function ($query, $type) use ($request) {
                if ($type === 'purchases') {
                    $query->where('buyer_id', $request->user()->id);
                } elseif ($type === 'sales') {
                    $query->where('seller_id', $request->user()->id);
                }
            })
            ->orderBy('created_at', 'desc')
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
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->user_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot buy your own product'
            ], 400);
        }

        if ($product->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available'
            ], 400);
        }

        $shippingCost = 5.99; // Calculer selon les règles
        $serviceFee = $product->price * 0.05; // 5% de frais
        $totalAmount = $product->price + $shippingCost + $serviceFee;

        $order = Order::create([
            'buyer_id' => $request->user()->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'shipping_cost' => $shippingCost,
            'service_fee' => $serviceFee,
            'total_amount' => $totalAmount,
            'shipping_address' => $request->shipping_address,
            'status' => 'pending',
        ]);

        // Marquer le produit comme réservé
        $product->update(['status' => 'reserved']);

        return response()->json([
            'success' => true,
            'data' => $order->load(['product.images', 'seller']),
            'message' => 'Order created successfully'
        ], 201);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['product.images', 'buyer', 'seller', 'reviews']);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate([
            'status' => 'required|in:paid,shipped,delivered,cancelled',
            'tracking_number' => 'required_if:status,shipped|string',
        ]);

        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number,
            'shipped_at' => $request->status === 'shipped' ? now() : $order->shipped_at,
            'delivered_at' => $request->status === 'delivered' ? now() : $order->delivered_at,
        ]);

        // Mettre à jour le statut du produit
        if ($request->status === 'delivered') {
            $order->product->update(['status' => 'sold']);
            $order->seller->increment('total_sales');
        } elseif ($request->status === 'cancelled') {
            $order->product->update(['status' => 'active']);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }
}

