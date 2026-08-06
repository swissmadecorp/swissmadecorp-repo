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
        if ($request instanceof \Illuminate\Http\Request) {
            $request = $request->all();
        }

        if (is_numeric($request)) {
            $productIds = [(int) $request];
        } elseif (is_array($request)) {
            $productIds = $request['ids'] ?? [];

            if (!$productIds && isset($request['product_id'])) {
                $productIds = [$request['product_id']];
            }
        } else {
            $productIds = [];
        }

        $productIds = array_values(array_filter(array_map('intval', (array) $productIds)));

        if (!$productIds) {
            throw new \InvalidArgumentException(
                'AutomateEbayUpdate requires a product ID or an array containing ids or product_id.'
            );
        }

        parent::__construct(['ids' => $productIds]);
        $this->onConnection('sync');

        \Log::info('AutomateEbayUpdate constructed for dispatch.', [
            'product_ids' => $productIds,
        ]);
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
