<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    public const WATCHING = 0;
    public const MATCHED = 1;
    public const CONTACTED = 2;

    protected $guarded = [];
    protected $casts = ['matched_at' => 'datetime', 'contacted_at' => 'datetime', 'status' => 'integer'];
    use FullTextSearch;
    
    protected $searchable = [
        'criteria'
    ];

    public function setProductConditionAttribute($value) {
        $this->attributes['product_condition'] = serialize($value);
    }

    public function setBoxpapersAttribute($value) {
        $this->attributes['boxpapers'] = serialize($value);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function matchedProduct() {
        return $this->belongsTo(Product::class, 'matched_product_id');
    }

    // Keep compatibility with the serialized preferences on existing reminders.
    public function preferences(string $field): array {
        $value = $this->getRawOriginal($field) ?? $this->getAttributes()[$field] ?? null;
        if (is_array($value)) return $value;
        if (!$value) return [];
        $decoded = @unserialize($value, ['allowed_classes' => false]);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    public function contactSummary(): string {
        return implode(' · ', array_filter([
            $this->customer_name ?: $this->assigned_to,
            $this->customer_phone,
            $this->customer_email,
        ]));
    }
}
