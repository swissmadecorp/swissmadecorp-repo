<?php

namespace App\Jobs;

/**
 * Rebuild an existing eBay listing from the latest local product data.
 *
 * Dispatch this job with the same payload as AutomateEbayPost:
 * AutomateEbayUpdate::dispatch(['ids' => [$productId]]);
 */
class AutomateEbayUpdate extends AutomateEbayPost
{
    protected function isRevision(): bool
    {
        return true;
    }
}
