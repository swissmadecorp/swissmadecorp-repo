<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\WatchReminderMatcher;

class WatchReminderObserver
{
    public function saved(Product $product): void
    {
        if ($product->wasRecentlyCreated || $product->wasChanged([
            'category_id', 'p_model', 'p_reference', 'p_casesize', 'p_color',
            'p_condition', 'p_box', 'p_papers', 'p_qty', 'p_status', 'deleted_at',
        ])) {
            app(WatchReminderMatcher::class)->recordMatches($product);
        }
    }
}
