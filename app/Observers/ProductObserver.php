<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Conversation;
use App\Models\ProductInterest;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Si le produit passe à vendu ou est désactivé, archiver les conversations
        if ($product->isDirty('status')) {
            if (in_array($product->status, [Product::STATUS_SOLD, Product::STATUS_REMOVED])) {
                Conversation::archiveForProduct($product);
                
                // Marquer les intérêts comme annulés si produit supprimé
                if ($product->status === Product::STATUS_REMOVED) {
                    ProductInterest::where('product_id', $product->id)
                        ->whereIn('status', [ProductInterest::STATUS_INTERESTED, ProductInterest::STATUS_NEGOTIATING])
                        ->update(['status' => ProductInterest::STATUS_CANCELLED]);
                }
                
                // Marquer les intérêts comme achetés si produit vendu
                if ($product->status === Product::STATUS_SOLD) {
                    ProductInterest::where('product_id', $product->id)
                        ->whereIn('status', [ProductInterest::STATUS_INTERESTED, ProductInterest::STATUS_NEGOTIATING])
                        ->update(['status' => ProductInterest::STATUS_PURCHASED]);
                }
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        // Archiver toutes les conversations de ce produit
        Conversation::archiveForProduct($product);
        
        // Marquer tous les intérêts comme annulés
        ProductInterest::where('product_id', $product->id)
            ->whereIn('status', [ProductInterest::STATUS_INTERESTED, ProductInterest::STATUS_NEGOTIATING])
            ->update(['status' => ProductInterest::STATUS_CANCELLED]);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
