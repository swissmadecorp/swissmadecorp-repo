<?php

namespace App\Services;

use App\Models\Category;

class LegacyReminderDetails
{
    /** Extract only details present in the original "Brand Model 39mm Reference" request. */
    public function fromCriteria(?string $criteria): array
    {
        $criteria = trim($criteria ?? '');
        $details = [];
        $description = $criteria;

        // Longest first prevents a shorter brand from consuming part of the model.
        foreach (Category::get(['id', 'category_name'])->sortByDesc(fn ($brand) => mb_strlen(trim($brand->category_name))) as $brand) {
            $name = preg_quote(trim($brand->category_name), '/');
            $name = preg_replace('/\s+/u', '\\s+', $name);
            if (preg_match('/^'.$name.'(?:\s+|$)/iu', $criteria, $match)) {
                $details['category_id'] = $brand->id;
                $description = trim(substr($criteria, strlen($match[0])));
                break;
            }
        }

        if (preg_match('/(?<![\pL\pN.])(\d+(?:\.\d+)?)\s*mm\b/iu', $description, $size, PREG_OFFSET_CAPTURE)) {
            $details['case_size'] = WatchReminderMatcher::size($size[1][0]);
            $model = trim(substr($description, 0, $size[0][1]));
            $reference = trim(substr($description, $size[0][1] + strlen($size[0][0])));
            $reference = preg_replace('/^(?:ref(?:erence)?\.?\s*[:#]?\s*)/iu', '', $reference);
            // A single reference token is safe to extract; do not put notes into this field.
            if (preg_match('/^(?=.*\d)[\pL\pN.\/-]+$/u', $reference)) {
                $details['watch_reference'] = $reference;
            }
        } else {
            $model = $description;
        }

        // Without a known brand, the description cannot reliably be split into a model.
        if (isset($details['category_id']) && $model !== '') {
            $details['watch_model'] = $model;
        }

        return $details;
    }
}
