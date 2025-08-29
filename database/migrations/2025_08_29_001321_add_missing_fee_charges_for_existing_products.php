<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductFeeCharge;
use App\Models\PlatformFee;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the listing fee
        $listingFee = PlatformFee::where('code', 'listing_fee')->where('active', true)->first();
        
        if (!$listingFee) {
            // Create default listing fee if it doesn't exist
            $listingFee = PlatformFee::create([
                'code' => 'listing_fee',
                'name' => 'Listing Fee',
                'type' => 'fixed',
                'amount' => 0.5,
                'percentage' => 0,
                'active' => true,
            ]);
        }
        
        // Get all products with pending_payment status that don't have fee charges
        $productsWithoutFees = Product::where('status', 'pending_payment')
            ->whereDoesntHave('feeCharges', function($query) use ($listingFee) {
                $query->where('fee_id', $listingFee->id);
            })
            ->get();
        
        echo "Found " . $productsWithoutFees->count() . " products without fee charges\n";
        
        foreach ($productsWithoutFees as $product) {
            // Calculate fee amount
            $amount = $listingFee->type === 'percentage'
                ? round(($listingFee->percentage / 100) * (float) $product->price, 2)
                : (float) $listingFee->amount;
            
            // Create fee charge
            ProductFeeCharge::create([
                'product_id' => $product->id,
                'user_id' => $product->user_id,
                'fee_id' => $listingFee->id,
                'amount' => $amount,
                'status' => 'pending',
            ]);
            
            echo "Created fee charge for product {$product->id} ({$product->title}): {$amount} XAF\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration adds data, so we can't easily reverse it
        // The fee charges will remain in the database
    }
};
