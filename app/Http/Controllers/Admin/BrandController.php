<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::withCount('products');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDir = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $brands = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $brands->items(),
            'meta' => [
                'current_page' => $brands->currentPage(),
                'last_page'    => $brands->lastPage(),
                'per_page'     => $brands->perPage(),
                'total'        => $brands->total(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string|max:1000',
            'website'     => 'nullable|url|max:255',
            'is_active'   => 'boolean',
            'is_premium'  => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $validated['slug']       = Str::slug($validated['name']);
        $validated['is_active']  = $validated['is_active']  ?? true;
        $validated['is_premium'] = $validated['is_premium'] ?? false;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $brand = Brand::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Marque créée avec succès',
            'data'    => $brand
        ], 201);
    }

    public function update(Brand $brand, Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string|max:1000',
            'website'     => 'nullable|url|max:255',
            'is_active'   => 'boolean',
            'is_premium'  => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Marque mise à jour avec succès',
            'data'    => $brand
        ]);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une marque qui contient des produits'
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Marque supprimée avec succès'
        ]);
    }
}
