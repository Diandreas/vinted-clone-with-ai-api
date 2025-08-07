<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    public function show(Brand $brand)
    {
        $brand->load(['products' => function($query) {
            $query->active()->latest()->limit(20);
        }]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $brand,
                'stats' => [
                    'total_products' => $brand->products()->active()->count(),
                    'is_premium' => $brand->is_premium,
                ]
            ]
        ]);
    }
}