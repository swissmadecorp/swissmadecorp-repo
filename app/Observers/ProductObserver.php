<?php

namespace App\Observers;

use App\Models\Product;
use App\Events\ProductUpdateEvent;

class ProductObserver
{
    /**
     * Handle the products "created" event.
     */
    public function created(Product $product): void
    {
        //
        \Log::info('Product created: ' . $product->id . ' by user: ' . (auth()->user()?->username ?? 'system'));
        $this->notifyInventoryAfterCommit($product);
    }

    /**
     * Handle the products "updated" event.
     */
    public function updated(Product $product): void
    {
        \Log::info('Product updated: ' . $product->id . ' by user: ' . (auth()->user()?->username ?? 'system'));
        $this->notifyInventoryAfterCommit($product);
    }

    /**
     * Handle the products "deleted" event.
     */
    public function deleted(Product $product): void
    {
        \Log::info('Product deleted: ' . $product->id . ' by user: ' . (auth()->user()?->username ?? 'system'));
        $this->notifyInventoryAfterCommit($product);
    }

    /**
     * Handle the products "restored" event.
     */
    public function restored(Product $product): void
    {
        \Log::info('Product restored: ' . $product->id . ' by user: ' . (auth()->user()?->username ?? 'system'));
        $this->notifyInventoryAfterCommit($product);
    }

    /**
     * Handle the products "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        \Log::info('Product force deleted: ' . $product->id . ' by user: ' . (auth()->user()?->username ?? 'system'));
        // Eloquent also fires deleted for force deletes, which notifies once above.
    }

    private function notifyInventoryAfterCommit(Product $product): void
    {
        // Invoice/return writes must be visible to the public API before notifying.
        // Laravel discards this callback on rollback; without a transaction it runs now.
        $product->getConnection()->afterCommit(static function (): void {
            try {
                ProductUpdateEvent::dispatch();
            } catch (\Throwable $exception) {
                // A notification failure must not turn a committed sale into an error.
                // The storefront's periodic reconciliation remains the fallback.
                report($exception);
            }
        });
    }
}
