<?php

namespace App\Jobs;

use Throwable;

/**
 * Rebuild an existing eBay listing from the latest local product data.
 *
 * Dispatch this job with the same payload as AutomateEbayPost:
 * AutomateEbayUpdate::dispatch(['ids' => [$productId]]);
 */
class AutomateEbayUpdate extends AutomateEbayPost
{
    public $tries = 1;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->onQueue('ebay-updates');
    }

    public function handle()
    {

        \Log::info('AutomateEbayUpdate started.', ['product_ids' => $this->productIds]);
        parent::handle();
        \Log::info('AutomateEbayUpdate completed.', ['product_ids' => $this->productIds]);
    }

    public function failed(Throwable $exception): void
    {
        \Log::error('AutomateEbayUpdate failed.', [
            'product_ids' => $this->productIds,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function isRevision(): bool
    {
        return true;
    }
}
