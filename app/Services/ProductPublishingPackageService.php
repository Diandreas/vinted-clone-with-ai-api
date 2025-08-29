<?php

namespace App\Services;

use App\Models\User;
use App\Models\ProductPublishingPackage;
use App\Services\ProductPublishingFeeService;
use App\Services\LygosPaymentService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductPublishingPackageService
{
    protected ProductPublishingFeeService $feeService;
    protected LygosPaymentService $lygosService;

    public function __construct(
        ProductPublishingFeeService $feeService,
        LygosPaymentService $lygosService
    ) {
        $this->feeService = $feeService;
        $this->lygosService = $lygosService;
    }

    /**
     * Create a new publishing package with estimated pricing
     */
    public function createEstimatedPackage(
        User $user, 
        int $productCount, 
        float $estimatedTotalValue
    ): array {
        if ($productCount < 1) {
            throw new \InvalidArgumentException('Product count must be at least 1');
        }

        if ($estimatedTotalValue < 0) {
            throw new \InvalidArgumentException('Estimated total value cannot be negative');
        }

        // Calculate fees
        $feeCalculation = $this->feeService->calculateEstimatedPackageFee(
            $productCount, 
            $estimatedTotalValue
        );

        // Generate unique package ID
        $packageId = ProductPublishingPackage::generatePackageId($user->id);

        // Create package
        $package = ProductPublishingPackage::create([
            'user_id' => $user->id,
            'package_id' => $packageId,
            'product_count' => $productCount,
            'estimated_total_value' => $estimatedTotalValue,
            'total_fee' => $feeCalculation['estimated_total_fee'],
            'fee_breakdown' => $feeCalculation,
            'status' => ProductPublishingPackage::STATUS_PENDING,
            'expires_at' => now()->addHours(24), // 24 hours to complete payment
        ]);

        // Create payment gateway
        try {
            $paymentData = $this->feeService->createPaymentData($user, $feeCalculation, $packageId);
            $gatewayResponse = $this->lygosService->createPaymentGateway($paymentData);

            $package->update([
                'payment_gateway_id' => $gatewayResponse['id'],
                'payment_link' => $gatewayResponse['link'],
                'payment_details' => $gatewayResponse,
            ]);

            $this->feeService->logFeeCalculation($user, $feeCalculation, 'estimated_package');

            return [
                'package' => $package->getSummary(),
                'payment_link' => $gatewayResponse['link'],
                'fee_calculation' => $feeCalculation,
            ];

        } catch (\Exception $e) {
            // If payment gateway creation fails, delete the package
            $package->delete();
            
            Log::error('Failed to create payment gateway for publishing package', [
                'user_id' => $user->id,
                'package_id' => $packageId,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to create payment gateway: ' . $e->getMessage());
        }
    }

    /**
     * Create a package with exact product prices
     */
    public function createExactPackage(User $user, array $productPrices): array
    {
        if (empty($productPrices)) {
            throw new \InvalidArgumentException('At least one product price is required');
        }

        // Validate all prices are numeric and positive
        foreach ($productPrices as $price) {
            if (!is_numeric($price) || $price < 0) {
                throw new \InvalidArgumentException('All product prices must be numeric and non-negative');
            }
        }

        // Calculate fees
        $feeCalculation = $this->feeService->calculatePackageFee($productPrices);

        // Generate unique package ID
        $packageId = ProductPublishingPackage::generatePackageId($user->id);

        // Create package
        $package = ProductPublishingPackage::create([
            'user_id' => $user->id,
            'package_id' => $packageId,
            'product_count' => count($productPrices),
            'estimated_total_value' => array_sum($productPrices),
            'total_fee' => $feeCalculation['total_fee'],
            'fee_breakdown' => $feeCalculation,
            'status' => ProductPublishingPackage::STATUS_PENDING,
            'expires_at' => now()->addHours(24),
        ]);

        // Create payment gateway
        try {
            $paymentData = $this->feeService->createPaymentData($user, $feeCalculation, $packageId);
            $gatewayResponse = $this->lygosService->createPaymentGateway($paymentData);

            $package->update([
                'payment_gateway_id' => $gatewayResponse['id'],
                'payment_link' => $gatewayResponse['link'],
                'payment_details' => $gatewayResponse,
            ]);

            $this->feeService->logFeeCalculation($user, $feeCalculation, 'exact_package');

            return [
                'package' => $package->getSummary(),
                'payment_link' => $gatewayResponse['link'],
                'fee_calculation' => $feeCalculation,
            ];

        } catch (\Exception $e) {
            $package->delete();
            
            Log::error('Failed to create payment gateway for exact package', [
                'user_id' => $user->id,
                'package_id' => $packageId,
                'product_prices' => $productPrices,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to create payment gateway: ' . $e->getMessage());
        }
    }

    /**
     * Get user's active packages
     */
    public function getUserActivePackages(User $user): array
    {
        $packages = ProductPublishingPackage::where('user_id', $user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return $packages->map(function ($package) {
            return $package->getSummary();
        })->toArray();
    }

    /**
     * Get user's pending packages
     */
    public function getUserPendingPackages(User $user): array
    {
        $packages = ProductPublishingPackage::where('user_id', $user->id)
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();

        return $packages->map(function ($package) {
            return array_merge($package->getSummary(), [
                'payment_link' => $package->payment_link,
            ]);
        })->toArray();
    }

    /**
     * Get user's package history
     */
    public function getUserPackageHistory(User $user, int $limit = 20): array
    {
        $packages = ProductPublishingPackage::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $packages->map(function ($package) {
            return $package->getSummary();
        })->toArray();
    }

    /**
     * Find package by ID
     */
    public function findPackage(string $packageId): ?ProductPublishingPackage
    {
        return ProductPublishingPackage::where('package_id', $packageId)->first();
    }

    /**
     * Mark package as paid (webhook handler)
     */
    public function markPackageAsPaid(string $packageId, array $paymentDetails = []): ProductPublishingPackage
    {
        $package = $this->findPackage($packageId);
        
        if (!$package) {
            throw new \Exception('Package not found: ' . $packageId);
        }

        if ($package->status === ProductPublishingPackage::STATUS_PAID) {
            return $package; // Already paid
        }

        $package->markAsPaid($paymentDetails);

        Log::info('Publishing package marked as paid', [
            'package_id' => $packageId,
            'user_id' => $package->user_id,
            'total_fee' => $package->total_fee,
            'payment_details' => $paymentDetails
        ]);

        return $package;
    }

    /**
     * Use a package slot for product publishing
     */
    public function usePackageSlot(User $user, string $packageId): ProductPublishingPackage
    {
        $package = ProductPublishingPackage::where('package_id', $packageId)
            ->where('user_id', $user->id)
            ->first();

        if (!$package) {
            throw new \Exception('Package not found or not owned by user');
        }

        if (!$package->can_be_used) {
            throw new \Exception('Package cannot be used: ' . 
                ($package->is_expired ? 'expired' : 'no available slots'));
        }

        $package->useSlot();

        Log::info('Package slot used', [
            'package_id' => $packageId,
            'user_id' => $user->id,
            'used_slots' => $package->used_slots,
            'available_slots' => $package->available_slots
        ]);

        return $package;
    }

    /**
     * Cancel a pending package
     */
    public function cancelPackage(User $user, string $packageId): bool
    {
        $package = ProductPublishingPackage::where('package_id', $packageId)
            ->where('user_id', $user->id)
            ->where('status', ProductPublishingPackage::STATUS_PENDING)
            ->first();

        if (!$package) {
            throw new \Exception('Package not found, not owned by user, or cannot be cancelled');
        }

        $package->cancel();

        Log::info('Publishing package cancelled', [
            'package_id' => $packageId,
            'user_id' => $user->id
        ]);

        return true;
    }

    /**
     * Clean up expired packages
     */
    public function cleanupExpiredPackages(): int
    {
        $expiredCount = ProductPublishingPackage::where('status', ProductPublishingPackage::STATUS_PENDING)
            ->where('expires_at', '<=', now())
            ->update(['status' => ProductPublishingPackage::STATUS_EXPIRED]);

        if ($expiredCount > 0) {
            Log::info('Cleaned up expired publishing packages', [
                'expired_count' => $expiredCount
            ]);
        }

        return $expiredCount;
    }

    /**
     * Get package statistics for a user
     */
    public function getUserPackageStats(User $user): array
    {
        $stats = ProductPublishingPackage::where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_packages,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_packages,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_packages,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid_packages,
                SUM(CASE WHEN status = ? THEN total_fee ELSE 0 END) as total_fees_paid,
                SUM(CASE WHEN status = ? THEN used_slots ELSE 0 END) as total_slots_used,
                SUM(CASE WHEN status = ? THEN product_count ELSE 0 END) as total_slots_purchased
            ', [
                ProductPublishingPackage::STATUS_PAID,
                ProductPublishingPackage::STATUS_PENDING,
                ProductPublishingPackage::STATUS_PAID,
                ProductPublishingPackage::STATUS_PAID,
                ProductPublishingPackage::STATUS_PAID,
                ProductPublishingPackage::STATUS_PAID
            ])
            ->first();

        return [
            'total_packages' => (int) $stats->total_packages,
            'active_packages' => (int) $stats->active_packages,
            'pending_packages' => (int) $stats->pending_packages,
            'paid_packages' => (int) $stats->paid_packages,
            'total_fees_paid' => (float) $stats->total_fees_paid,
            'formatted_total_fees' => number_format($stats->total_fees_paid, 0, ',', ' ') . ' FCFA',
            'total_slots_used' => (int) $stats->total_slots_used,
            'total_slots_purchased' => (int) $stats->total_slots_purchased,
            'available_slots' => (int) ($stats->total_slots_purchased - $stats->total_slots_used),
        ];
    }
}