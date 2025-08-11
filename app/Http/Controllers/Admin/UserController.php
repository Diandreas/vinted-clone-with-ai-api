<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['followers', 'following'])
            ->withCount(['products', 'orders', 'followers', 'following']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'unverified') {
                $query->where('is_verified', false);
            }
        }
        
        // Filter by admin status
        if ($request->filled('admin')) {
            $query->where('is_admin', $request->admin === 'true');
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $users = $query->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }
    
    public function show(User $user)
    {
        $user->load(['products', 'orders', 'followers', 'following']);
        
        $stats = [
            'total_products' => $user->products()->count(),
            'active_products' => $user->products()->where('status', 'active')->count(),
            'total_orders' => $user->orders()->count(),
            'completed_orders' => $user->orders()->where('status', 'completed')->count(),
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => $stats
            ]
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['user', 'admin', 'manager', 'analyst', 'moderator'])],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:dashboard:view,users:manage,products:moderate,lives:moderate,orders:view,analytics:view',
            'is_admin' => 'boolean',
            'is_verified' => 'boolean',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_verified'] = $validated['is_verified'] ?? true;
        $validated['is_admin'] = $validated['is_admin'] ?? false;
        
        // Set default permissions based on role
        if (empty($validated['permissions'])) {
            $validated['permissions'] = $this->getDefaultPermissions($validated['role']);
        }
        
        $user = User::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user
        ], 201);
    }
    
    public function update(User $user, Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'role' => ['sometimes', 'required', Rule::in(['user', 'admin', 'manager', 'analyst', 'moderator'])],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:dashboard:view,users:manage,products:moderate,lives:moderate,orders:view,analytics:view',
            'is_admin' => 'boolean',
            'is_verified' => 'boolean',
            'status' => ['sometimes', Rule::in(['active', 'suspended', 'banned'])],
        ]);
        
        // Prevent admin from removing their own admin status
        if (auth()->id() === $user->id && isset($validated['is_admin']) && !$validated['is_admin']) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas retirer vos propres droits d\'administrateur'
            ], 422);
        }
        
        // Update permissions if role changed
        if (isset($validated['role']) && $validated['role'] !== $user->role) {
            if (empty($validated['permissions'])) {
                $validated['permissions'] = $this->getDefaultPermissions($validated['role']);
            }
        }
        
        $user->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $user
        ]);
    }
    
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 422);
        }
        
        // Check if user has products or orders
        if ($user->products()->count() > 0 || $user->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un utilisateur qui a des produits ou des commandes'
            ], 422);
        }
        
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
    
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:verify,unverify,activate,suspend,ban,delete',
            'role' => 'nullable|string|in:user,admin,manager,analyst,moderator',
            'permissions' => 'nullable|array',
        ]);
        
        $users = User::whereIn('id', $validated['user_ids'])->get();
        
        foreach ($users as $user) {
            switch ($validated['action']) {
                case 'verify':
                    $user->update(['is_verified' => true]);
                    break;
                case 'unverify':
                    $user->update(['is_verified' => false]);
                    break;
                case 'activate':
                    $user->update(['status' => 'active']);
                    break;
                case 'suspend':
                    $user->update(['status' => 'suspended']);
                    break;
                case 'ban':
                    $user->update(['status' => 'banned']);
                    break;
                case 'delete':
                    if ($user->products()->count() === 0 && $user->orders()->count() === 0) {
                        $user->delete();
                    }
                    break;
            }
            
            if (isset($validated['role'])) {
                $user->update(['role' => $validated['role']]);
            }
            
            if (isset($validated['permissions'])) {
                $user->update(['permissions' => $validated['permissions']]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Utilisateurs mis à jour avec succès'
        ]);
    }
    
    private function getDefaultPermissions(string $role): array
    {
        return match($role) {
            'admin' => ['dashboard:view', 'users:manage', 'products:moderate', 'lives:moderate', 'orders:view', 'analytics:view'],
            'manager' => ['dashboard:view', 'products:moderate', 'lives:moderate', 'orders:view', 'analytics:view'],
            'analyst' => ['dashboard:view', 'analytics:view'],
            'moderator' => ['dashboard:view', 'products:moderate', 'lives:moderate'],
            default => []
        };
    }
}