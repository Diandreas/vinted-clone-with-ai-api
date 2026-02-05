<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Live;
use App\Models\Order;
use App\Models\Story;
use App\Models\Payment;
use App\Models\PlatformFee;
use App\Models\ProductFeeCharge;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function overview()
    {
        $listingFeeId = PlatformFee::where('code', 'listing_fee')->value('id');
        $listingFees = ProductFeeCharge::when($listingFeeId, function ($query) use ($listingFeeId) {
            $query->where('fee_id', $listingFeeId);
        });

        $notchpayListingPayments = Payment::where('payment_method', 'notchpay')
            ->whereNotNull('metadata->product_id');
        $paymentsCompleted = (clone $notchpayListingPayments)->where('status', 'completed');
        $paymentsCompletedCount = (clone $paymentsCompleted)->count();
        $paymentsRevenue = (float) (clone $paymentsCompleted)->sum('amount');
        $feesRevenue = (float) (clone $listingFees)->where('status', 'paid')->sum('amount');
        $publishingRevenue = $paymentsRevenue > 0 ? $paymentsRevenue : $feesRevenue;
        $publishingPaidCount = $paymentsCompletedCount > 0
            ? $paymentsCompletedCount
            : (clone $listingFees)->where('status', 'paid')->count();

        $today = now()->toDateString();
        $visitorsTotal = (int) DB::table('site_visits')->count();
        $visitorsToday = (int) DB::table('site_visits')->where('visited_on', $today)->count();

        $data = [
            'users_total' => User::count(),
            'users_verified' => User::where('is_verified', true)->count(),
            'products_total' => Product::count(),
            'products_active' => Product::where('status', 'active')->count(),
            'products_pending_payment' => Product::where('status', 'pending_payment')->count(),
            'lives_total' => Live::count(),
            'lives_active' => Live::where('status', 'live')->count(),
            'orders_total' => Order::count(),
            'stories_total' => Story::count(),
            'visitors_total' => $visitorsTotal,
            'visitors_today' => $visitorsToday,
            'publishing_fee_revenue' => $publishingRevenue,
            'publishing_fee_paid_count' => $publishingPaidCount,
            'publishing_fee_pending_count' => (clone $listingFees)->where('status', 'pending')->count(),
            'publishing_fee_failed_count' => (clone $listingFees)->where('status', 'failed')->count(),
            'payments_completed' => (clone $notchpayListingPayments)->where('status', 'completed')->count(),
            'payments_pending' => (clone $notchpayListingPayments)->where('status', 'pending')->count(),
            'payments_failed' => (clone $notchpayListingPayments)->where('status', 'failed')->count(),
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

    public function payments()
    {
        $listingFeeId = PlatformFee::where('code', 'listing_fee')->value('id');

        $feesBase = ProductFeeCharge::when($listingFeeId, function ($query) use ($listingFeeId) {
            $query->where('fee_id', $listingFeeId);
        });

        $paymentsBase = Payment::where('payment_method', 'notchpay')
            ->whereNotNull('metadata->product_id');

        $data = [
            'payments_by_status' => (clone $paymentsBase)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status'),
            'payments_by_day' => (clone $paymentsBase)
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
                ->groupBy('d')
                ->orderBy('d')
                ->get(),
            'fee_revenue_by_day' => (clone $feesBase)
                ->where('status', 'paid')
                ->selectRaw('DATE(created_at) as d, SUM(amount) as s')
                ->groupBy('d')
                ->orderBy('d')
                ->get(),
            'publishing_fee_total' => (float) (clone $feesBase)->where('status', 'paid')->sum('amount'),
            'publishing_fee_counts' => (clone $feesBase)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status'),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function visitors()
    {
        $data = [
            'by_day' => DB::table('site_visits')
                ->selectRaw('visited_on as d, COUNT(*) as c')
                ->groupBy('visited_on')
                ->orderBy('visited_on')
                ->get(),
            'total' => (int) DB::table('site_visits')->count(),
            'today' => (int) DB::table('site_visits')->where('visited_on', now()->toDateString())->count(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function reports()
    {
        return response()->json(['success' => true, 'data' => []]);
    }
}
