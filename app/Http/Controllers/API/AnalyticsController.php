<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        $data = [
            'overview' => [
                'total_products' => $user->products()->count(),
                'active_products' => $user->products()->where('status', 'active')->count(),
                'sold_products' => $user->products()->where('status', 'sold')->count(),
                'total_sales' => $user->sales()->where('status', 'completed')->sum('total_amount'),
                'total_purchases' => $user->orders()->where('status', 'completed')->sum('total_amount'),
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
                'avg_rating' => $user->average_rating ?? 0,
            ],
            'this_month' => [
                'products_added' => $user->products()->where('created_at', '>=', $lastMonth)->count(),
                'sales_count' => $user->sales()->where('status', 'completed')->where('created_at', '>=', $lastMonth)->count(),
                'sales_amount' => $user->sales()->where('status', 'completed')->where('created_at', '>=', $lastMonth)->sum('total_amount'),
                'new_followers' => $user->followers()->where('created_at', '>=', $lastMonth)->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function productsAnalytics(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);

        $products = $user->products()
            ->with(['category', 'brand'])
            ->where('created_at', '>=', $startDate)
            ->get();

        $analytics = [
            'products_by_category' => $products->groupBy('category.name')->map->count(),
            'products_by_status' => $products->groupBy('status')->map->count(),
            'most_viewed' => $user->products()
                ->orderBy('views_count', 'desc')
                ->limit(10)
                ->get(['id', 'title', 'views_count', 'price']),
            'most_liked' => $user->products()
                ->orderBy('likes_count', 'desc')
                ->limit(10)
                ->get(['id', 'title', 'likes_count', 'price']),
            'price_distribution' => $this->getPriceDistribution($products),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    public function salesAnalytics(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);

        $sales = $user->sales()
            ->with(['product', 'buyer'])
            ->where('created_at', '>=', $startDate)
            ->get();

        $analytics = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'average_order_value' => $sales->avg('total_amount'),
            'sales_by_day' => $this->getSalesByDay($sales, $period),
            'top_selling_products' => $this->getTopSellingProducts($user, $period),
            'sales_by_category' => $sales->groupBy('product.category.name')->map->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    public function followersAnalytics(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);

        $followers = $user->followers()
            ->where('created_at', '>=', $startDate)
            ->get();

        $analytics = [
            'new_followers' => $followers->count(),
            'followers_by_day' => $this->getFollowersByDay($followers, $period),
            'follower_growth_rate' => $this->getFollowerGrowthRate($user, $period),
            'most_active_followers' => $this->getMostActiveFollowers($user),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    public function engagementAnalytics(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period);

        $products = $user->products();
        
        $analytics = [
            'total_views' => $products->sum('views_count'),
            'total_likes' => $products->sum('likes_count'),
            'total_favorites' => $products->sum('favorites_count'),
            'total_comments' => $products->sum('comments_count'),
            'engagement_rate' => $this->calculateEngagementRate($user),
            'top_engaging_products' => $this->getTopEngagingProducts($user),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    private function getPriceDistribution($products)
    {
        $ranges = [
            '0-10' => 0,
            '10-25' => 0,
            '25-50' => 0,
            '50-100' => 0,
            '100+' => 0
        ];

        foreach ($products as $product) {
            $price = $product->price;
            if ($price < 10) $ranges['0-10']++;
            elseif ($price < 25) $ranges['10-25']++;
            elseif ($price < 50) $ranges['25-50']++;
            elseif ($price < 100) $ranges['50-100']++;
            else $ranges['100+']++;
        }

        return $ranges;
    }

    private function getSalesByDay($sales, $period)
    {
        $days = [];
        for ($i = 0; $i < $period; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[$date] = 0;
        }

        foreach ($sales as $sale) {
            $date = $sale->created_at->format('Y-m-d');
            if (isset($days[$date])) {
                $days[$date]++;
            }
        }

        return array_reverse($days, true);
    }

    private function getFollowersByDay($followers, $period)
    {
        $days = [];
        for ($i = 0; $i < $period; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[$date] = 0;
        }

        foreach ($followers as $follower) {
            $date = $follower->pivot->created_at->format('Y-m-d');
            if (isset($days[$date])) {
                $days[$date]++;
            }
        }

        return array_reverse($days, true);
    }

    private function getTopSellingProducts($user, $period)
    {
        return $user->sales()
            ->with('product')
            ->where('created_at', '>=', Carbon::now()->subDays($period))
            ->where('status', 'completed')
            ->select('product_id', DB::raw('COUNT(*) as sales_count'))
            ->groupBy('product_id')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($sale) {
                return $sale->product;
            });
    }

    private function getFollowerGrowthRate($user, $period)
    {
        $currentPeriodFollowers = $user->followers()
            ->where('created_at', '>=', Carbon::now()->subDays($period))
            ->count();
        
        $previousPeriodFollowers = $user->followers()
            ->where('created_at', '>=', Carbon::now()->subDays($period * 2))
            ->where('created_at', '<', Carbon::now()->subDays($period))
            ->count();

        if ($previousPeriodFollowers == 0) return 100;

        return (($currentPeriodFollowers - $previousPeriodFollowers) / $previousPeriodFollowers) * 100;
    }

    private function getMostActiveFollowers($user)
    {
        // This would need more complex logic based on likes, comments, etc.
        return $user->followers()
            ->latest('follows.created_at')
            ->limit(10)
            ->get(['users.id', 'users.name', 'users.username', 'users.avatar']);
    }

    private function calculateEngagementRate($user)
    {
        $totalViews = $user->products()->sum('views_count');
        $totalEngagements = $user->products()->sum('likes_count') + 
                           $user->products()->sum('favorites_count') + 
                           $user->products()->sum('comments_count');

        return $totalViews > 0 ? ($totalEngagements / $totalViews) * 100 : 0;
    }

    private function getTopEngagingProducts($user)
    {
        return $user->products()
            ->select('*')
            ->selectRaw('(likes_count + favorites_count + comments_count) as engagement_score')
            ->orderBy('engagement_score', 'desc')
            ->limit(10)
            ->get();
    }
}