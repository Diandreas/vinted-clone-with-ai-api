<?php
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\ConditionController;
use App\Http\Controllers\API\LiveController;
use App\Http\Controllers\API\StoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ConversationController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\ImageSearchController;
use App\Http\Controllers\API\FeedController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\AnalyticsController;

// Admin Controllers - Specific imports
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::prefix('v1')->group(function () {

    // File serving routes (must be before other routes)
    Route::get('files/{path}', [\App\Http\Controllers\FileController::class, 'serve'])->where('path', '.*');

    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
        Route::post('verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('resend-verification', [AuthController::class, 'resendVerification'])->name('verification.send');

        // Social auth (web + mobile token exchange) — Google only
        Route::get('social/google/redirect', [AuthController::class, 'redirectToProvider']);
        Route::match(['get', 'post'], 'social/google/callback', [AuthController::class, 'handleProviderCallback']);
    });

    // Public Routes (sans authentification)
    Route::group([], function () {
        // Categories
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);

        // Brands
        Route::get('brands', [BrandController::class, 'index']);
        Route::get('brands/{brand}', [BrandController::class, 'show']);

        // Conditions
        Route::get('conditions', [ConditionController::class, 'index']);

        // Products (lecture seule)
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show'])->where('product', '[0-9]+');
        Route::get('products/{product}/similar', [ProductController::class, 'similar'])->where('product', '[0-9]+');

        // Users (lecture seule)
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::get('users/{user}/products', [UserController::class, 'userProducts']);
        Route::get('users/{user}/followers', [UserController::class, 'followers']);
        Route::get('users/{user}/following', [UserController::class, 'following']);

        // Lives publics
        Route::get('lives', [LiveController::class, 'index']);
        Route::get('lives/{live}', [LiveController::class, 'show']);

        // Search
        Route::get('search', [SearchController::class, 'search']);
        Route::get('search/suggestions', [SearchController::class, 'suggestions']);
        
        // Image Search (public endpoint for testing)
        Route::post('search/image', [ImageSearchController::class, 'searchByImage']);
        Route::post('search/analyze', [ImageSearchController::class, 'analyzeImage']);

        // Explore
        Route::get('explore', [FeedController::class, 'explore']);
        Route::get('trending', [ProductController::class, 'trending']);
        
        // App Download
        Route::get('download/app', function() {
            $apkPath = public_path('app-debug.apk');
            
            if (!file_exists($apkPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'APK non disponible'
                ], 404);
            }
            
            return response()->download($apkPath, 'sellam-app.apk', [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Content-Disposition' => 'attachment; filename="sellam-app.apk"'
            ]);
        });
    });

    // Routes authentifiées
    Route::middleware('auth:sanctum')->group(function () {

        // Auth user routes
        Route::prefix('auth')->group(function () {
            Route::get('user', [AuthController::class, 'user']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::put('update-profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::delete('delete-account', [AuthController::class, 'deleteAccount']);
        });

        // User Routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::put('profile', [UserController::class, 'update']);
            Route::post('{user}/follow', [UserController::class, 'follow']);
            Route::delete('{user}/unfollow', [UserController::class, 'unfollow']);
            Route::get('my-followers', [UserController::class, 'myFollowers']);
            Route::get('my-following', [UserController::class, 'myFollowing']);
            Route::put('settings', [UserController::class, 'updateSettings']);
            Route::post('avatar', [UserController::class, 'updateAvatar']);
            Route::post('cover', [UserController::class, 'updateCover']);
        });

        // Product Routes
        Route::prefix('products')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            // Static routes MUST come before parameterized routes
            Route::get('my-products', [ProductController::class, 'myProducts']);
            Route::get('my-favorites', [ProductController::class, 'myFavorites']);
            Route::get('my-likes', [ProductController::class, 'myLikes']);
            Route::get('draft', [ProductController::class, 'draft']);
            Route::get('sold', [ProductController::class, 'sold']);
            Route::get('stats', [ProductController::class, 'stats']);
            // Parameterized routes come after
            Route::put('{product}', [ProductController::class, 'update']);
            Route::delete('{product}', [ProductController::class, 'destroy']);
            Route::post('{product}/like', [ProductController::class, 'like']);
            Route::post('{product}/favorite', [ProductController::class, 'favorite']);
            Route::get('{product}/like-status', [ProductController::class, 'getLikeStatus']);
            Route::get('{product}/favorite-status', [ProductController::class, 'getFavoriteStatus']);
            Route::post('{product}/comment', [ProductController::class, 'addComment']);
            Route::get('{product}/comments', [ProductController::class, 'getComments']);
            Route::put('{product}/boost', [ProductController::class, 'boost']);
            Route::post('{product}/share', [ProductController::class, 'share']);
            // Appointments
            Route::post('{product}/appointments', [ProductController::class, 'requestAppointment']);
        });
        // Appointment management
        Route::put('appointments/{appointment}', [ProductController::class, 'updateAppointmentStatus']);

        // Live Routes
        Route::prefix('lives')->group(function () {
            Route::post('/', [LiveController::class, 'store']);
            Route::put('{live}', [LiveController::class, 'update']);
            Route::delete('{live}', [LiveController::class, 'destroy']);
            Route::post('{live}/start', [LiveController::class, 'start']);
            Route::post('{live}/end', [LiveController::class, 'end']);
            Route::post('{live}/join', [LiveController::class, 'joinLive']);
            Route::post('{live}/leave', [LiveController::class, 'leaveLive']);
            Route::post('{live}/comment', [LiveController::class, 'addComment']);
            Route::get('{live}/comments', [LiveController::class, 'getComments']);
            Route::post('{live}/like', [LiveController::class, 'like']);
            Route::get('my-lives', [LiveController::class, 'myLives']);
            Route::get('following-lives', [LiveController::class, 'followingLives']);
        });

        // Story Routes
        Route::prefix('stories')->group(function () {
            Route::get('/', [StoryController::class, 'index']);
            Route::post('/', [StoryController::class, 'store']);
            Route::get('{story}', [StoryController::class, 'show']);
            Route::delete('{story}', [StoryController::class, 'destroy']);
            Route::post('{story}/view', [StoryController::class, 'view']);
            Route::get('{story}/viewers', [StoryController::class, 'viewers']);
            Route::get('my-stories', [StoryController::class, 'myStories']);
            Route::get('following-stories', [StoryController::class, 'followingStories']);
        });

        // Order Routes
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('{order}', [OrderController::class, 'show']);
            Route::put('{order}/status', [OrderController::class, 'updateStatus']);
            Route::post('{order}/cancel', [OrderController::class, 'cancel']);
            Route::post('{order}/dispute', [OrderController::class, 'dispute']);
            Route::get('purchases', [OrderController::class, 'purchases']);
            Route::get('sales', [OrderController::class, 'sales']);
            Route::get('pending', [OrderController::class, 'pending']);
        });

        // Conversation & Messages Routes
        Route::prefix('conversations')->group(function () {
            Route::get('/', [ConversationController::class, 'index']);
            Route::post('/', [ConversationController::class, 'store']);
            
            // Nouvelles routes pour conversations centrées produit (AVANT les routes avec paramètres)
            Route::get('my-product-discussions', [ConversationController::class, 'myProductDiscussions']);
            Route::get('my-products-with-buyers', [ConversationController::class, 'myProductsWithBuyers']);
            Route::get('my-product-interests', [ConversationController::class, 'myProductInterests']);
            Route::post('start/{product}', [ConversationController::class, 'startProductConversation']);
            Route::get('product/{product}/conversations', [ConversationController::class, 'getProductConversations']);
            
            // Routes avec paramètres (APRÈS les routes spécifiques)
            Route::get('{conversation}', [ConversationController::class, 'show']);
            Route::delete('{conversation}', [ConversationController::class, 'destroy']);
            Route::post('{conversation}/messages', [MessageController::class, 'store']);
            Route::get('{conversation}/messages', [MessageController::class, 'index']);
            Route::put('{conversation}/status', [ConversationController::class, 'updateStatus']);
            Route::put('messages/{message}/read', [MessageController::class, 'markAsRead']);
            Route::delete('messages/{message}', [MessageController::class, 'destroy']);
            Route::post('messages/{message}/report', [MessageController::class, 'report']);
        });

        // Review Routes
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('{review}', [ReviewController::class, 'update']);
            Route::delete('{review}', [ReviewController::class, 'destroy']);
            Route::get('received', [ReviewController::class, 'received']);
            Route::get('given', [ReviewController::class, 'given']);
        });

        // Notification Routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
            Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{notification}', [NotificationController::class, 'destroy']);
            Route::delete('clear-all', [NotificationController::class, 'clearAll']);
            Route::get('unread-count', [NotificationController::class, 'unreadCount']);
            Route::put('settings', [NotificationController::class, 'updateSettings']);
        });

        // Feed Routes
        Route::prefix('feed')->group(function () {
            Route::get('/', [FeedController::class, 'index']);
            Route::get('following', [FeedController::class, 'following']);
            Route::get('recommended', [FeedController::class, 'recommended']);
            Route::post('refresh', [FeedController::class, 'refresh']);
        });

        // Report Routes
        Route::prefix('reports')->group(function () {
            Route::post('/', [ReportController::class, 'store']);
            Route::get('my-reports', [ReportController::class, 'myReports']);
        });

        // Payment Routes
        Route::prefix('payments')->group(function () {
            Route::post('process', [PaymentController::class, 'process']);
            Route::get('history', [PaymentController::class, 'history']);
        });

        // Wallet Routes
        Route::prefix('wallet')->group(function () {
            Route::get('balance', [WalletController::class, 'balance']);
            Route::get('transactions', [WalletController::class, 'transactions']);
            Route::post('topup', [WalletController::class, 'topUp']);
        });

        // NotchPay payment initialization routes
        Route::prefix('notchpay')->group(function () {
            Route::post('initialize', [\App\Http\Controllers\NotchPayController::class, 'initializePayment']);
        });

        // Shipping Address Routes
        Route::prefix('addresses')->group(function () {
            Route::get('/', [UserController::class, 'getAddresses']);
            Route::post('/', [UserController::class, 'addAddress']);
            Route::put('{address}', [UserController::class, 'updateAddress']);
            Route::delete('{address}', [UserController::class, 'deleteAddress']);
            Route::post('{address}/default', [UserController::class, 'setDefaultAddress']);
        });

        // Analytics Routes (for sellers)
        Route::prefix('analytics')->group(function () {
            Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
            Route::get('products', [AnalyticsController::class, 'productsAnalytics']);
            Route::get('sales', [AnalyticsController::class, 'salesAnalytics']);
            Route::get('followers', [AnalyticsController::class, 'followersAnalytics']);
            Route::get('engagement', [AnalyticsController::class, 'engagementAnalytics']);
        });

        // Additional User Actions
        Route::prefix('me')->group(function () {
            Route::get('dashboard', [UserController::class, 'dashboard']);
            Route::get('stats', [UserController::class, 'stats']);
            Route::get('activity', [UserController::class, 'activity']);
            Route::get('earnings', [UserController::class, 'earnings']);
            Route::get('recent-views', [UserController::class, 'recentViews']);
        });
    });

    // Admin Routes (protected by admin middleware)
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);
            Route::get('{user}', [AdminUserController::class, 'show']);
            Route::put('{user}', [AdminUserController::class, 'update']);
            Route::put('{user}/verify', [AdminUserController::class, 'verify']);
            Route::put('{user}/ban', [AdminUserController::class, 'ban']);
            Route::put('{user}/unban', [AdminUserController::class, 'unban']);
            Route::delete('{user}', [AdminUserController::class, 'destroy']);
        });

        // Product Management
        Route::prefix('products')->group(function () {
            Route::get('/', [AdminProductController::class, 'index']);
            Route::get('pending', [AdminProductController::class, 'pending']);
            Route::put('{product}/approve', [AdminProductController::class, 'approve']);
            Route::put('{product}/reject', [AdminProductController::class, 'reject']);
            Route::put('{product}/feature', [AdminProductController::class, 'feature']);
            Route::delete('{product}', [AdminProductController::class, 'destroy']);
        });

        // Report Management
        Route::prefix('reports')->group(function () {
            Route::get('/', [AdminReportController::class, 'index']);
            Route::get('{report}', [AdminReportController::class, 'show']);
            Route::put('{report}/resolve', [AdminReportController::class, 'resolve']);
            Route::put('{report}/dismiss', [AdminReportController::class, 'dismiss']);
        });

        // Category Management
        Route::prefix('categories')->group(function () {
            Route::get('/', [AdminCategoryController::class, 'index']);
            Route::post('/', [AdminCategoryController::class, 'store']);
            Route::get('{category}', [AdminCategoryController::class, 'show']);
            Route::put('{category}', [AdminCategoryController::class, 'update']);
            Route::delete('{category}', [AdminCategoryController::class, 'destroy']);
        });

        // Product Management
        Route::prefix('products')->group(function () {
            Route::get('/', [AdminProductController::class, 'index']);
            Route::get('{product}', [AdminProductController::class, 'show']);
            Route::put('{product}', [AdminProductController::class, 'update']);
            Route::delete('{product}', [AdminProductController::class, 'destroy']);
        });

        // Brand Management
        Route::prefix('brands')->group(function () {
            Route::post('/', [AdminBrandController::class, 'store']);
            Route::put('{brand}', [AdminBrandController::class, 'update']);
            Route::delete('{brand}', [AdminBrandController::class, 'destroy']);
        });

        // Analytics & Statistics
        Route::prefix('analytics')->group(function () {
            Route::get('overview', [AdminAnalyticsController::class, 'overview']);
            Route::get('users', [AdminAnalyticsController::class, 'users']);
            Route::get('products', [AdminAnalyticsController::class, 'products']);
            Route::get('sales', [AdminAnalyticsController::class, 'sales']);
            Route::get('reports', [AdminAnalyticsController::class, 'reports']);
        });

        // System Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [AdminSettingsController::class, 'index']);
            Route::put('/', [AdminSettingsController::class, 'update']);
        });

        // Image Search Admin Routes
        Route::prefix('image-search')->group(function () {
            Route::post('process-products', [ImageSearchController::class, 'processExistingProducts']);
            Route::get('stats', [ImageSearchController::class, 'getStats']);
        });
    });

    // ✅ SUPPRIMÉ LA ROUTE DUPLIQUÉE ICI

});

// Webhook Routes (for payment providers, etc.) - PUBLIC
Route::prefix('webhooks')->group(function () {
    Route::post('stripe', [PaymentController::class, 'stripeWebhook']);
    Route::post('paypal', [PaymentController::class, 'paypalWebhook']);
    Route::post('fapshi', [PaymentController::class, 'fapshiWebhook']);
    Route::post('notchpay', [\App\Http\Controllers\NotchPayController::class, 'handleWebhook']);
    // NotchPay callback handler (GET request from browser redirect)
    Route::get('notchpay', [\App\Http\Controllers\NotchPayController::class, 'handleCallback']);
    Route::post('mobile-money/mtn_momo', function() { return response()->json(['ok' => true]); });
    Route::post('mobile-money/orange_money', function() { return response()->json(['ok' => true]); });
});

// V1 Webhook Routes (for backward compatibility and specific providers)
Route::prefix('v1/webhooks')->group(function () {
    Route::post('notchpay', [\App\Http\Controllers\NotchPayController::class, 'handleWebhook']);
    // NotchPay callback handler (GET request from browser redirect) - V1 path
    Route::get('notchpay', [\App\Http\Controllers\NotchPayController::class, 'handleCallback']);
});

// NotchPay callback routes (PUBLIC)
Route::prefix('payment')->group(function () {
    Route::get('callback', [\App\Http\Controllers\NotchPayController::class, 'handleCallback'])->name('payment.callback');
    Route::get('/', function() {
        return redirect('/payment');
    });
});

// Additional legacy routes for NotchPay
Route::get('/payment/callback', [\App\Http\Controllers\NotchPayController::class, 'handleCallback'])->name('payment.callback.legacy');
Route::post('/api/notchpay/webhook', [\App\Http\Controllers\NotchPayController::class, 'handleWebhook']);

// Authenticated legacy route
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/notchpay/initialize', [\App\Http\Controllers\NotchPayController::class, 'initializePayment']);
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});

// Rate limiting for API routes
Route::middleware(['throttle:api'])->group(function () {
    // All API routes are automatically rate limited
});

// Special rate limiting for auth routes
Route::middleware(['throttle:auth'])->prefix('v1/auth')->group(function () {
    // Auth routes with stricter rate limiting are handled above
});

/*
|--------------------------------------------------------------------------
| Route Model Bindings
|--------------------------------------------------------------------------
*/

// Custom route model bindings can be defined in RouteServiceProvider
// Example: Route::model('user', User::class);

/*
|--------------------------------------------------------------------------
| API Versioning
|--------------------------------------------------------------------------
*/

// v2 API routes (for future versions)
Route::prefix('v2')->group(function () {
    // Future API versions can be added here
    Route::get('status', function () {
        return response()->json([
            'version' => '2.0',
            'status' => 'development'
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Health Check & Status Routes
|--------------------------------------------------------------------------
*/

Route::prefix('health')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'status' => 'OK',
            'timestamp' => now(),
            'version' => config('app.version', '1.0.0')
        ]);
    });

    Route::get('database', function () {
        try {
            DB::connection()->getPdo();
            return response()->json(['database' => 'connected']);
        } catch (\Exception $e) {
            return response()->json(['database' => 'disconnected'], 500);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Documentation Route
|--------------------------------------------------------------------------
*/

Route::get('docs', function () {
    return response()->json([
        'message' => 'API Documentation',
        'version' => '1.0.0',
        'endpoints' => [
            'auth' => '/api/v1/auth',
            'products' => '/api/v1/products',
            'users' => '/api/v1/users',
            'orders' => '/api/v1/orders',
            'lives' => '/api/v1/lives',
            'stories' => '/api/v1/stories',
        ]
    ]);
});