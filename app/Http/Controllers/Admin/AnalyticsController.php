<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Live;
use App\Models\Order;
use App\Models\Story;

class AnalyticsController extends Controller
{
    public function overview()
    {
        $data = [
            'users_total' => User::count(),
            'users_verified' => User::where('is_verified', true)->count(),
            'products_total' => Product::count(),
            'products_active' => Product::where('status', 'active')->count(),
            'lives_total' => Live::count(),
            'lives_active' => Live::where('status', 'live')->count(),
            'orders_total' => Order::count(),
            'stories_total' => Story::count(),
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function users()
    {
        $data = [
            'by_day' => User::selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->orderBy('d')->get(),
            'verified' => User::where('is_verified', true)->count(),
            'recently_active' => User::where('last_seen_at', '>=', now()->subMinutes(15))->count(),
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function products()
    {
        $data = [
            'by_status' => Product::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c','status'),
            'by_day' => Product::selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->orderBy('d')->get(),
            'avg_price' => (float) Product::where('status', 'active')->avg('price'),
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function sales()
    {
        $data = [
            'orders_by_day' => Order::selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->orderBy('d')->get(),
            'revenue' => (float) Order::sum('total_amount'),
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function reports()
    {
        return response()->json(['success' => true, 'data' => []]);
    }
}