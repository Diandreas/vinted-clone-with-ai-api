<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notifications\NewFollower;
use App\Jobs\ProcessKycDocument;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\KycSubmissionReceived;
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
                // Route publique : on tente une auth optionnelle via le guard Sanctum
                'is_following' => ($authUser = Auth::guard('sanctum')->user()) ? $authUser->isFollowing($user) : false,
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

        // Si le follow a réussi, l'utilisateur suit maintenant l'autre
        // Si le follow a échoué (déjà abonné), on vérifie l'état actuel
        $isFollowing = $result ? true : $currentUser->isFollowing($user);

        if ($result) {
            $user->notify(new NewFollower($currentUser));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => $isFollowing,
                'followers_count' => $user->fresh()->followers_count
            ],
            'message' => $result ? 'User followed successfully' : 'Already following this user'
        ]);
    }

    public function unfollow(User $user)
    {
        $currentUser = Auth::user();
        
        $result = $currentUser->unfollow($user);
        
        // Après un unfollow réussi, l'utilisateur ne suit plus l'autre
        // Si l'unfollow a échoué (pas abonné), on vérifie l'état actuel
        $isFollowing = $result ? false : $currentUser->isFollowing($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => $isFollowing,
                'followers_count' => $user->fresh()->followers_count
            ],
            'message' => $result ? 'User unfollowed successfully' : 'User was not being followed'
        ]);
    }

    public function followers(User $user)
    {
        // Récupérer les followers de l'utilisateur spécifié
        $followers = $user->followers()
            ->select('users.*')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $followers
        ]);
    }

    public function following(User $user)
    {
        // Récupérer les utilisateurs que l'utilisateur spécifié suit
        $following = $user->following()
            ->select('users.*')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $following
        ]);
    }

    public function reviews(User $user)
    {
        try {
            // Récupérer les avis reçus par l'utilisateur
            $reviews = $user->receivedReviews()
                ->with(['reviewer'])
                ->latest()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in reviews method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // En cas d'erreur, retourner un tableau vide
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0
                ]
            ]);
        }
    }

    public function myFollowers()
    {
        $user = Auth::user();
        
        $followers = $user->followers()->latest()->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $followers
        ]);
    }

    public function myFollowing()
    {
        $user = Auth::user();
        
        $following = $user->following()->latest()->paginate(20);
        
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
            $productData = $product->makeHidden(['mainImage', 'images'])->toArray();
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
        if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // Store new avatar
        $filename = time() . '_' . $request->file('avatar')->getClientOriginalName();
        $request->file('avatar')->storeAs('avatars', $filename, 'public');

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
        if ($user->cover_image && Storage::disk('public')->exists('covers/' . $user->cover_image)) {
            Storage::disk('public')->delete('covers/' . $user->cover_image);
        }

        // Store new cover
        $filename = time() . '_' . $request->file('cover')->getClientOriginalName();
        $request->file('cover')->storeAs('covers', $filename, 'public');

        $user->update(['cover_image' => $filename]);

        return response()->json([
            'success' => true,
            'data' => [
                'cover_image_url' => $user->fresh()->cover_image_url
            ],
            'message' => 'Cover image updated successfully'
        ]);
    }

    public function kycStatus()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $user->kyc_status,
                'document_type' => $user->kyc_document_type,
                'verified_at' => $user->kyc_verified_at,
                'rejection_reason' => $user->kyc_rejection_reason,
            ]
        ]);
    }

    public function submitKyc(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:cni,passport',
            'document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'selfie' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = Auth::user();

        if ($user->kyc_document_path && Storage::exists($user->kyc_document_path)) {
            Storage::delete($user->kyc_document_path);
        }
        if ($user->kyc_selfie_path && Storage::exists($user->kyc_selfie_path)) {
            Storage::delete($user->kyc_selfie_path);
        }

        $documentPath = $request->file('document')->store('kyc/documents');
        $selfiePath = $request->hasFile('selfie')
            ? $request->file('selfie')->store('kyc/selfies')
            : null;

        $user->update([
            'kyc_status' => 'pending',
            'kyc_document_type' => $request->document_type,
            'kyc_document_path' => $documentPath,
            'kyc_selfie_path' => $selfiePath,
            'kyc_verified_at' => null,
            'kyc_rejection_reason' => null,
        ]);

        User::query()
            ->get()
            ->filter(function (User $adminUser) use ($user) {
                if ($adminUser->id === $user->id) {
                    return false;
                }

                $permissions = $adminUser->permissions ?? [];
                if (!is_array($permissions)) {
                    $permissions = [];
                }

                return ($adminUser->is_admin ?? false)
                    || ($adminUser->role === 'admin')
                    || in_array('users:manage', $permissions, true);
            })
            ->each(function (User $adminUser) use ($user, $request) {
                $adminUser->notify(new KycSubmissionReceived($user, $request->document_type));
            });

        ProcessKycDocument::dispatch($user->id, $documentPath, $request->document_type);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $user->kyc_status,
            ],
            'message' => 'Documents envoyés. Vérification en cours.'
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
        
        \Log::info('🔵 Stats called for user: ' . $user->id);
        
        $productsTotal = $user->products()->count();
        $productsActive = $user->products()->where('status', 'active')->count();
        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        
        \Log::info('📊 User stats:', [
            'products_total' => $productsTotal,
            'products_active' => $productsActive,
            'followers_count' => $followersCount,
            'following_count' => $followingCount
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'products' => [
                    'total' => $productsTotal,
                    'active' => $productsActive,
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
                    'followers_count' => $followersCount,
                    'following_count' => $followingCount,
                    'average_rating' => 5.0, // TODO: Calculer la vraie moyenne
                    'total_reviews' => 0, // TODO: Implémenter le système de reviews
                ]
            ]
        ]);
    }

    public function activity()
    {
        $user = Auth::user();
        
        // Récupérer les activités récentes de l'utilisateur
        $recentActions = collect();
        
        try {
            // Activités de produits (création, modification, vente)
            $productActivities = $user->products()
                ->with(['category', 'brand'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => 'product_' . $product->id,
                        'type' => 'product',
                        'description' => "Produit \"{$product->title}\" créé",
                        'created_at' => $product->created_at,
                        'icon' => 'package',
                        'data' => $product
                    ];
                });
            
            $recentActions = $recentActions->concat($productActivities);
            
            // Activités de followers (vérifier qu'il y en a)
            $followers = $user->followers()->latest()->limit(3)->get();
            if ($followers->count() > 0) {
                $followerActivities = $followers->map(function ($follower) {
                    return [
                        'id' => 'follower_' . $follower->id,
                        'type' => 'follower',
                        'description' => "@{$follower->username} vous suit maintenant",
                        'created_at' => $follower->pivot->created_at ?? now(),
                        'icon' => 'user-plus',
                        'data' => $follower
                    ];
                });
                
                $recentActions = $recentActions->concat($followerActivities);
            }
            
            // Activités de likes reçus (vérifier qu'il y en a)
            $productsWithLikes = $user->products()
                ->withCount('likes')
                ->where('likes_count', '>', 0)
                ->latest()
                ->limit(3)
                ->get();
                
            if ($productsWithLikes->count() > 0) {
                $likeActivities = $productsWithLikes->map(function ($product) {
                    return [
                        'id' => 'like_' . $product->id,
                        'type' => 'like',
                        'description' => "Votre produit \"{$product->title}\" a reçu {$product->likes_count} like(s)",
                        'created_at' => $product->updated_at,
                        'icon' => 'heart',
                        'data' => $product
                    ];
                });
                
                $recentActions = $recentActions->concat($likeActivities);
            }
            
            // Activités de vues (vérifier qu'il y en a)
            $productsWithViews = $user->products()
                ->where('views_count', '>', 0)
                ->latest()
                ->limit(3)
                ->get();
                
            if ($productsWithViews->count() > 0) {
                $viewActivities = $productsWithViews->map(function ($product) {
                    return [
                        'id' => 'view_' . $product->id,
                        'type' => 'view',
                        'description' => "Votre produit \"{$product->title}\" a été vu {$product->views_count} fois",
                        'created_at' => $product->updated_at,
                        'icon' => 'eye',
                        'data' => $product
                    ];
                });
                
                $recentActions = $recentActions->concat($viewActivities);
            }
            
            // Trier par date et limiter
            $recentActions = $recentActions
                ->sortByDesc('created_at')
                ->take(10)
                ->values();
                
        } catch (\Exception $e) {
            \Log::error('Error in activity method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // En cas d'erreur, retourner des activités de base
            $recentActions = collect([
                [
                    'id' => 'welcome',
                    'type' => 'welcome',
                    'description' => 'Bienvenue sur votre profil !',
                    'created_at' => now(),
                    'icon' => 'star'
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'recent_actions' => $recentActions,
                'total_actions' => $recentActions->count(),
                'last_activity' => $recentActions->isNotEmpty() ? $recentActions->first()['created_at'] : null
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
