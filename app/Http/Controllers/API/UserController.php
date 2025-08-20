<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
            })
            ->when($request->verified, function($query) {
                $query->verified();
            })
            ->when($request->is_live, function($query) {
                $query->live();
            })
            ->recentlyActive()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function show(User $user)
    {
        $user->load(['products' => function($query) {
            $query->active()->latest()->limit(6);
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => [
                    'products_count' => $user->products()->active()->count(),
                    'followers_count' => $user->followers_count,
                    'following_count' => $user->following_count,
                    'average_rating' => $user->average_rating,
                ],
                'is_following' => Auth::check() ? $user->isFollowing(Auth::user()) : false,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'string|max:255',
            'bio' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only([
            'name', 'bio', 'website', 'location', 'phone'
        ]));

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'Profile updated successfully'
        ]);
    }

    public function follow(User $user)
    {
        $currentUser = Auth::user();
        
        if ($currentUser->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        $result = $currentUser->follow($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => $result,
                'followers_count' => $user->fresh()->followers_count
            ],
            'message' => $result ? 'User followed successfully' : 'Already following this user'
        ]);
    }

    public function unfollow(User $user)
    {
        $currentUser = Auth::user();
        
        $result = $currentUser->unfollow($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => false,
                'followers_count' => $user->fresh()->followers_count
            ],
            'message' => 'User unfollowed successfully'
        ]);
    }

    public function followers(User $user)
    {
        $followers = $user->followers()
            ->with('follower')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $followers
        ]);
    }

    public function following(User $user)
    {
        $following = $user->following()
            ->with('following')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $following
        ]);
    }

    public function myFollowers()
    {
        $user = Auth::user();
        
        $followers = $user->followers()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $followers
        ]);
    }

    public function myFollowing()
    {
        $user = Auth::user();
        
        $following = $user->following()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $following
        ]);
    }

    public function userProducts(User $user)
    {
        $products = $user->products()
            ->with(['images', 'mainImage', 'category', 'brand', 'condition'])
            ->active()
            ->latest()
            ->paginate(20);

        // Ajouter les accesseurs d'images pour chaque produit
        $products->getCollection()->transform(function ($product) {
            $productData = $product->toArray();
            $productData['main_image_url'] = $product->main_image_url;
            $productData['image_urls'] = $product->image_urls;
            return (object) $productData;
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();
        
        // Delete old avatar if exists
        if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        // Store new avatar
        $filename = time() . '_' . $request->file('avatar')->getClientOriginalName();
        $request->file('avatar')->storeAs('public/avatars', $filename);

        $user->update(['avatar' => $filename]);

        return response()->json([
            'success' => true,
            'data' => [
                'avatar_url' => $user->fresh()->avatar_url
            ],
            'message' => 'Avatar updated successfully'
        ]);
    }

    public function updateCover(Request $request)
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $user = Auth::user();
        
        // Delete old cover if exists
        if ($user->cover_image && Storage::exists('public/covers/' . $user->cover_image)) {
            Storage::delete('public/covers/' . $user->cover_image);
        }

        // Store new cover
        $filename = time() . '_' . $request->file('cover')->getClientOriginalName();
        $request->file('cover')->storeAs('public/covers', $filename);

        $user->update(['cover_image' => $filename]);

        return response()->json([
            'success' => true,
            'data' => [
                'cover_image_url' => $user->fresh()->cover_image_url
            ],
            'message' => 'Cover image updated successfully'
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'array',
            'notification_settings' => 'array',
            'privacy_settings' => 'array',
        ]);

        $user = Auth::user();
        
        $user->update([
            'settings' => array_merge($user->settings ?? [], $request->settings ?? []),
            'notification_settings' => array_merge($user->notification_settings ?? [], $request->notification_settings ?? []),
            'privacy_settings' => array_merge($user->privacy_settings ?? [], $request->privacy_settings ?? []),
        ]);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'Settings updated successfully'
        ]);
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => [
                    'total_products' => $user->products()->count(),
                    'active_products' => $user->products()->where('status', 'active')->count(),
                    'sold_products' => $user->products()->where('status', 'sold')->count(),
                    'total_sales' => $user->sales()->where('status', 'delivered')->sum('total_amount') ?? 0,
                    'followers_count' => $user->followers()->count(),
                    'following_count' => $user->following()->count(),
                    'average_rating' => 5.0,
                ],
                'recent_activity' => [
                    'recent_products' => $user->products()->latest()->limit(5)->get(),
                    'recent_orders' => $user->orders()->latest()->limit(5)->get(),
                    'recent_sales' => $user->sales()->latest()->limit(5)->get(),
                ]
            ]
        ]);
    }

    public function stats()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'products' => [
                    'total' => $user->products()->count(),
                    'active' => $user->products()->where('status', 'active')->count(),
                    'sold' => $user->products()->where('status', 'sold')->count(),
                    'draft' => $user->products()->where('status', 'draft')->count(),
                ],
                'sales' => [
                    'total_earnings' => $user->sales()->where('status', 'delivered')->sum('total_amount') ?? 0,
                    'total_orders' => $user->sales()->count(),
                    'completed_orders' => $user->sales()->where('status', 'delivered')->count(),
                    'pending_orders' => $user->sales()->where('status', 'pending')->count(),
                ],
                'social' => [
                    'followers_count' => $user->followers()->count(),
                    'following_count' => $user->following()->count(),
                    'average_rating' => 5.0, // TODO: Calculer la vraie moyenne
                    'total_reviews' => 0, // TODO: Implémenter le système de reviews
                ]
            ]
        ]);
    }

    public function activity()
    {
        $user = Auth::user();
        
        // This would typically come from an activity log
        return response()->json([
            'success' => true,
            'data' => [
                'recent_logins' => [],
                'recent_actions' => [],
                'recent_views' => [],
            ]
        ]);
    }

    public function earnings()
    {
        $user = Auth::user();
        
        $earnings = $user->sales()
            ->where('status', 'delivered')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as earnings')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $user->sales()->where('status', 'delivered')->sum('total_amount') ?? 0,
                'this_month' => $user->sales()->where('status', 'delivered')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount') ?? 0,
                'daily_earnings' => $earnings,
            ]
        ]);
    }

    public function recentViews()
    {
        $user = Auth::user();
        
        // This would come from a views tracking system
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    // Address management methods
    public function getAddresses()
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function addAddress(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ], 201);
    }

    public function updateAddress($address, Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function deleteAddress($address)
    {
        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    public function setDefaultAddress($address)
    {
        return response()->json([
            'success' => true,
            'message' => 'Default address set successfully'
        ]);
    }

    /**
     * Search users by name, username, or email
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $perPage = $request->get('per_page', 20);

            if (empty(trim($query))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $users = User::query()
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('username', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->select([
                    'id', 'name', 'username', 'email', 'avatar', 
                    'location', 'created_at', 'is_verified', 'is_live'
                ])
                ->withCount(['products as products_count' => function($query) {
                    $query->where('status', 'active');
                }])
                ->latest()
                ->limit($perPage)
                ->get();

            // Add computed properties that the frontend expects
            $users = $users->map(function($user) {
                $user->avatar_url = $user->avatar_url;
                $user->followers_count = $user->followers_count;
                $user->following_count = $user->following_count;
                return $user;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'total' => $users->count()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('User search error: ' . $e->getMessage(), [
                'query' => $request->get('q'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching users',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}