<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Reminder;
use Illuminate\Support\Collection;

class WatchReminderMatcher
{
    public static function available(Product $product): bool
    {
        return !$product->trashed() && (int) $product->p_qty > 0
            && in_array((int) $product->p_status, [0, 5], true);
    }

    public static function normalize(?string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($value ?? '')));
    }

    public static function size(?string $value): string
    {
        $value = preg_replace('/\s*mm\s*$/i', '', trim($value ?? ''));
        return is_numeric($value) ? (string) (float) $value : self::normalize($value);
    }

    public function matches(Reminder $reminder, Product $product): bool
    {
        if (!self::available($product)) return false;

        if ($reminder->category_id) {
            if ((int) $reminder->category_id !== (int) $product->category_id) return false;
            foreach (['watch_model' => 'p_model', 'watch_reference' => 'p_reference'] as $wanted => $actual) {
                if (self::normalize($reminder->$wanted)
                    && self::normalize($reminder->$wanted) !== self::normalize($product->$actual)) return false;
            }
            if (self::size($reminder->case_size)
                && self::size($reminder->case_size) !== self::size($product->p_casesize)) return false;
            if ($reminder->dial_color && self::normalize($reminder->dial_color) !== self::normalize($product->p_color)) return false;
        } else {
            // Older free-text reminders still work, with whole phrases/references only.
            $criteria = preg_replace('/(\d)\s*mm\b/i', '$1 mm', self::normalize($reminder->criteria));
            foreach ([$product->categories?->category_name, $product->p_model, $product->p_reference, self::size($product->p_casesize).' mm'] as $value) {
                $value = self::normalize($value);
                if (!$value || $value === 'mm' || !preg_match('/(?<![\pL\pN])'.preg_quote($value, '/').'(?![\pL\pN])/u', $criteria)) return false;
            }
        }

        $conditions = array_map('strval', $reminder->preferences('product_condition'));
        if ($conditions && !in_array((string) $product->p_condition, $conditions, true)) return false;
        $accessories = $reminder->preferences('boxpapers');
        if (in_array('Box', $accessories, true) && (int) $product->p_box !== 1) return false;
        if (in_array('Papers', $accessories, true) && (int) $product->p_papers !== 1) return false;
        return true;
    }

    public function recordMatches(Product $product): Collection
    {
        $matches = collect();
        if (!self::available($product)) return $matches;

        // Keep notifying until staff marks the customer contacted. This also covers
        // duplicate inventory entries added after the first matching item.
        Reminder::whereIn('status', [Reminder::WATCHING, Reminder::MATCHED])
            ->whereNull('contacted_at')
            ->where(fn ($query) => $query->where('category_id', $product->category_id)->orWhereNull('category_id'))
            ->each(function (Reminder $reminder) use ($product, $matches) {
                if (!$this->matches($reminder, $product)) return;
                // Do not reopen a reminder that staff marked contacted during matching.
                $updated = Reminder::whereKey($reminder->id)
                    ->whereIn('status', [Reminder::WATCHING, Reminder::MATCHED])
                    ->whereNull('contacted_at')
                    ->update([
                    'status' => Reminder::MATCHED,
                    'matched_product_id' => $product->id,
                    'matched_at' => now(),
                    'contacted_at' => null,
                ]);
                if ($updated) $matches->push($reminder->fresh());
            });
        return $matches;
    }

    public function checkExistingStock(Reminder $reminder): void
    {
        if ($reminder->status !== Reminder::WATCHING) return;
        foreach (Product::where('p_qty', '>', 0)->whereIn('p_status', [0, 5])
            ->when($reminder->category_id, fn ($query) => $query->where('category_id', $reminder->category_id))->cursor() as $product) {
            if ($this->matches($reminder, $product)) {
                $this->recordMatches($product);
                break;
            }
        }
    }
}


