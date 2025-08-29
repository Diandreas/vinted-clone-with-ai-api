<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductPublishingPackage;
use App\Services\ProductPublishingFeeService;
use App\Services\ProductPublishingPackageService;
use App\Services\LygosPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductPublishingController extends Controller
{
    protected ProductPublishingFeeService $feeService;
    protected ProductPublishingPackageService $packageService;
    protected LygosPaymentService $lygosService;

    public function __construct(
        ProductPublishingFeeService $feeService,
        ProductPublishingPackageService $packageService,
        LygosPaymentService $lygosService
    ) {
        $this->feeService = $feeService;
        $this->packageService = $packageService;
        $this->lygosService = $lygosService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get fee structure information
     */
    public function getFeeStructure(): JsonResponse
    {
        try {
            $structure = $this->feeService->getFeeStructure();

            return response()->json([
                'success' => true,
                'data' => $structure
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get fee structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate fee for a single product
     */
    public function calculateSingleFee(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productPrice = (float) $request->product_price;
            $fee = $this->feeService->calculateSingleProductFee($productPrice);

            return response()->json([
                'success' => true,
                'data' => [
                    'product_price' => $productPrice,
                    'formatted_price' => number_format($productPrice, 0, ',', ' ') . ' FCFA',
                    'publishing_fee' => $fee,
                    'formatted_fee' => number_format($fee, 0, ',', ' ') . ' FCFA',
                    'base_fee' => ProductPublishingFeeService::BASE_FEE,
                    'percentage_fee' => $productPrice * ProductPublishingFeeService::PERCENTAGE_FEE,
                    'percentage_rate' => (ProductPublishingFeeService::PERCENTAGE_FEE * 100) . '%'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate estimated package fee
     */
    public function calculateEstimatedPackageFee(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_count' => 'required|integer|min:1|max:50',
            'estimated_total_value' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productCount = (int) $request->product_count;
            $estimatedValue = (float) $request->estimated_total_value;

            $calculation = $this->feeService->calculateEstimatedPackageFee($productCount, $estimatedValue);
            $affordability = $this->feeService->canUserAffordFees(Auth::user(), $calculation['estimated_total_fee']);

            return response()->json([
                'success' => true,
                'data' => array_merge($calculation, [
                    'affordability' => $affordability
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate package fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate exact package fee
     */
    public function calculateExactPackageFee(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_prices' => 'required|array|min:1|max:50',
            'product_prices.*' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productPrices = array_map('floatval', $request->product_prices);
            
            $calculation = $this->feeService->calculatePackageFee($productPrices);
            $affordability = $this->feeService->canUserAffordFees(Auth::user(), $calculation['total_fee']);

            return response()->json([
                'success' => true,
                'data' => array_merge($calculation, [
                    'affordability' => $affordability
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate exact package fee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create estimated publishing package
     */
    public function createEstimatedPackage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_count' => 'required|integer|min:1|max:50',
            'estimated_total_value' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $productCount = (int) $request->product_count;
            $estimatedValue = (float) $request->estimated_total_value;

            $result = $this->packageService->createEstimatedPackage($user, $productCount, $estimatedValue);

            return response()->json([
                'success' => true,
                'message' => 'Publishing package created successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create estimated package', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create publishing package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create exact publishing package
     */
    public function createExactPackage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_prices' => 'required|array|min:1|max:50',
            'product_prices.*' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $productPrices = array_map('floatval', $request->product_prices);

            $result = $this->packageService->createExactPackage($user, $productPrices);

            return response()->json([
                'success' => true,
                'message' => 'Publishing package created successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create exact package', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create publishing package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's publishing packages
     */
    public function getUserPackages(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $type = $request->query('type', 'all'); // all, active, pending, history

            $data = [];

            switch ($type) {
                case 'active':
                    $data['packages'] = $this->packageService->getUserActivePackages($user);
                    break;
                case 'pending':
                    $data['packages'] = $this->packageService->getUserPendingPackages($user);
                    break;
                case 'history':
                    $limit = $request->query('limit', 20);
                    $data['packages'] = $this->packageService->getUserPackageHistory($user, $limit);
                    break;
                default:
                    $data['active_packages'] = $this->packageService->getUserActivePackages($user);
                    $data['pending_packages'] = $this->packageService->getUserPendingPackages($user);
                    $data['stats'] = $this->packageService->getUserPackageStats($user);
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific package details
     */
    public function getPackageDetails(string $packageId): JsonResponse
    {
        try {
            $user = Auth::user();
            $package = ProductPublishingPackage::where('package_id', $packageId)
                ->where('user_id', $user->id)
                ->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => array_merge($package->getSummary(), [
                    'payment_link' => $package->payment_link,
                    'fee_breakdown' => $package->fee_breakdown,
                    'payment_details' => $package->payment_details
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get package details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a pending package
     */
    public function cancelPackage(string $packageId): JsonResponse
    {
        try {
            $user = Auth::user();
            $this->packageService->cancelPackage($user, $packageId);

            return response()->json([
                'success' => true,
                'message' => 'Package cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel package',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Use a package slot for product publishing
     */
    public function usePackageSlot(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $packageId = $request->package_id;

            $package = $this->packageService->usePackageSlot($user, $packageId);

            return response()->json([
                'success' => true,
                'message' => 'Package slot used successfully',
                'data' => $package->getSummary()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to use package slot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment success callback
     */
    public function paymentSuccess(Request $request): JsonResponse
    {
        try {
            $packageId = $request->query('order_id');
            $gatewayId = $request->query('gateway_id');

            if (!$packageId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing package ID'
                ], 400);
            }

            // Verify payment with Lygos gateway
            if ($gatewayId) {
                $paymentDetails = $this->lygosService->getPaymentGateway($gatewayId);
            } else {
                $paymentDetails = $request->all();
            }

            $package = $this->packageService->markPackageAsPaid($packageId, $paymentDetails);

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully',
                'data' => $package->getSummary()
            ]);
        } catch (\Exception $e) {
            Log::error('Payment success callback error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment confirmation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment failure callback
     */
    public function paymentFailure(Request $request): JsonResponse
    {
        $packageId = $request->query('order_id');
        
        Log::warning('Payment failed for publishing package', [
            'package_id' => $packageId,
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment failed',
            'package_id' => $packageId
        ]);
    }

    /**
     * Get available packages for product publishing
     */
    public function getAvailablePackagesForPublishing(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $activePackages = $this->packageService->getUserActivePackages($user);
            
            // Filter packages that have available slots
            $availablePackages = array_filter($activePackages, function ($package) {
                return $package['available_slots'] > 0;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'available_packages' => array_values($availablePackages),
                    'total_available_slots' => array_sum(array_column($availablePackages, 'available_slots'))
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}