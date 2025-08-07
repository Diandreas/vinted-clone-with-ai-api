<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Condition;

class ConditionController extends Controller
{
    public function index()
    {
        $conditions = Condition::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conditions
        ]);
    }

    public function show(Condition $condition)
    {
        $condition->load(['products' => function($query) {
            $query->active()->latest()->limit(20);
        }]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'condition' => $condition,
                'stats' => [
                    'total_products' => $condition->products()->active()->count(),
                ]
            ]
        ]);
    }
}