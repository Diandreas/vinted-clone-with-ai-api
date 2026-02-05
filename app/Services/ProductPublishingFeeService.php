<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductPublishingFeeService
{
    const BASE_FEE = 100; // 100 FCFA per listing
    const PERCENTAGE_FEE = 0.0; // No percentage fee
    
    /**
     * Calculate the publishing fee for a single product
     * Fee = 500 FCFA per product
     */
    public function calculateSingleProductFee(float $productPrice): float
    {
        $baseFee = self::BASE_FEE;
        $percentageFee = $productPrice * self::PERCENTAGE_FEE;
        
        return $baseFee + $percentageFee;
    }
    
    /**
     * Calculate the total fee for multiple products in a package
     */
    public function calculatePackageFee(array $productPrices): array
    {
        $totalFee = 0;
        $feeBreakdown = [];
        
        foreach ($productPrices as $index => $price) {
            $productFee = $this->calculateSingleProductFee($price);
            $totalFee += $productFee;
            
            $feeBreakdown[] = [
                'product_index' => $index + 1,
                'product_price' => $price,
                'base_fee' => self::BASE_FEE,
                'percentage_fee' => $price * self::PERCENTAGE_FEE,
                'total_fee' => $productFee,
                'formatted_price' => number_format($price, 0, ',', ' ') . ' FCFA',
                'formatted_fee' => number_format($productFee, 0, ',', ' ') . ' FCFA'
            ];
        }
        
        return [
            'total_fee' => $totalFee,
            'formatted_total_fee' => number_format($totalFee, 0, ',', ' ') . ' FCFA',
            'product_count' => count($productPrices),
            'fee_breakdown' => $feeBreakdown,
            'base_fee_per_product' => self::BASE_FEE,
            'percentage_rate' => self::PERCENTAGE_FEE * 100 . '%'
        ];
    }
    
    /**
     * Calculate estimated fee for a package based on estimated total value
     */
    public function calculateEstimatedPackageFee(int $productCount, float $estimatedTotalValue): array
    {
        $averagePrice = $productCount > 0 ? $estimatedTotalValue / $productCount : 0;
        
        // Create an array of estimated prices
        $estimatedPrices = array_fill(0, $productCount, $averagePrice);
        
        $calculation = $this->calculatePackageFee($estimatedPrices);
        
        return [
            'estimated_total_fee' => $calculation['total_fee'],
            'formatted_estimated_fee' => $calculation['formatted_total_fee'],
            'product_count' => $productCount,
            'estimated_total_value' => $estimatedTotalValue,
            'formatted_estimated_value' => number_format($estimatedTotalValue, 0, ',', ' ') . ' FCFA',
            'average_price_per_product' => $averagePrice,
            'formatted_average_price' => number_format($averagePrice, 0, ',', ' ') . ' FCFA',
            'base_fee_total' => self::BASE_FEE * $productCount,
            'percentage_fee_total' => $estimatedTotalValue * self::PERCENTAGE_FEE,
            'explanation' => [
                'base_fee' => self::BASE_FEE . ' FCFA × ' . $productCount . ' produits = ' . number_format(self::BASE_FEE * $productCount, 0, ',', ' ') . ' FCFA',
                'percentage_fee' => number_format(self::PERCENTAGE_FEE * 100, 0) . '% × ' . number_format($estimatedTotalValue, 0, ',', ' ') . ' FCFA = ' . number_format($estimatedTotalValue * self::PERCENTAGE_FEE, 0, ',', ' ') . ' FCFA',
                'total' => 'Frais de publication = ' . number_format($calculation['total_fee'], 0, ',', ' ') . ' FCFA'
            ]
        ];
    }
    
    /**
     * Validate that user can afford the publishing fees
     */
    public function canUserAffordFees(User $user, float $totalFee): array
    {
        $userBalance = $user->wallet_balance ?? 0;
        $canAfford = $userBalance >= $totalFee;
        
        return [
            'can_afford' => $canAfford,
            'user_balance' => $userBalance,
            'formatted_balance' => number_format($userBalance, 0, ',', ' ') . ' FCFA',
            'required_amount' => $totalFee,
            'formatted_required' => number_format($totalFee, 0, ',', ' ') . ' FCFA',
            'shortfall' => $canAfford ? 0 : $totalFee - $userBalance,
            'formatted_shortfall' => $canAfford ? '0 FCFA' : number_format($totalFee - $userBalance, 0, ',', ' ') . ' FCFA'
        ];
    }
    
    /**
     * Get fee structure information
     */
    public function getFeeStructure(): array
    {
        return [
            'base_fee' => self::BASE_FEE,
            'formatted_base_fee' => number_format(self::BASE_FEE, 0, ',', ' ') . ' FCFA',
            'percentage_rate' => self::PERCENTAGE_FEE * 100,
            'formatted_percentage_rate' => number_format(self::PERCENTAGE_FEE * 100, 1) . '%',
            'description' => 'Frais de publication = ' . self::BASE_FEE . ' FCFA par produit',
            'examples' => [
                [
                    'product_price' => 1000,
                    'fee' => $this->calculateSingleProductFee(1000),
                    'formatted_price' => '1 000 FCFA',
                    'formatted_fee' => number_format($this->calculateSingleProductFee(1000), 0, ',', ' ') . ' FCFA'
                ],
                [
                    'product_price' => 5000,
                    'fee' => $this->calculateSingleProductFee(5000),
                    'formatted_price' => '5 000 FCFA',
                    'formatted_fee' => number_format($this->calculateSingleProductFee(5000), 0, ',', ' ') . ' FCFA'
                ],
                [
                    'product_price' => 10000,
                    'fee' => $this->calculateSingleProductFee(10000),
                    'formatted_price' => '10 000 FCFA',
                    'formatted_fee' => number_format($this->calculateSingleProductFee(10000), 0, ',', ' ') . ' FCFA'
                ]
            ]
        ];
    }
    
    /**
     * Create payment data for Lygos gateway
     */
    public function createPaymentData(User $user, array $feeCalculation, string $orderId = null): array
    {
        $orderId = $orderId ?? 'PUB-' . time() . '-' . $user->id;
        
        return [
            'amount' => (int) round($feeCalculation['total_fee']),
            'shop_name' => config('services.lygos.shop_name', 'RIKEAA'),
            'order_id' => $orderId,
            'message' => 'Frais de publication pour ' . $feeCalculation['product_count'] . ' produit(s)',
            'success_url' => config('app.url') . '/api/v1/payment/publishing/success',
            'failure_url' => config('app.url') . '/api/v1/payment/publishing/failure'
        ];
    }
    
    /**
     * Log fee calculation for audit purposes
     */
    public function logFeeCalculation(User $user, array $calculation, string $type = 'single'): void
    {
        Log::info('Product publishing fee calculated', [
            'user_id' => $user->id,
            'calculation_type' => $type,
            'total_fee' => $calculation['total_fee'] ?? $calculation['estimated_total_fee'],
            'product_count' => $calculation['product_count'] ?? 1,
            'calculation_details' => $calculation
        ]);
    }
}
