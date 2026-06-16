<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DiscountRule extends Model
{
    private static ?Collection $pricingRuleCache = null;

    protected $guarded = [];
    public $timestamps = true;
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function setProductAttribute($value) {
        $this->attributes['product'] = $this->serializeSelection($value);
    }

    public function setBrandIdsAttribute($value)
    {
        $this->attributes['brand_ids'] = $this->serializeSelection($value);
    }

    public function scopeCurrentlyActive($query)
    {
        $today = now()->toDateString();

        return $query
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('is_active', 1);
    }

    public static function currentPricingRules(): Collection
    {
        if (static::$pricingRuleCache instanceof Collection) {
            return static::$pricingRuleCache;
        }

        static::$pricingRuleCache = static::query()
            ->currentlyActive()
            ->whereIn('action', [4, 5])
            ->orderBy('id')
            ->get();

        return static::$pricingRuleCache;
    }

    public static function currentPricingRuleForProduct(Product $product): ?self
    {
        return static::currentPricingRules()->first(
            fn (self $rule) => $rule->appliesPercentDiscountToProduct($product)
        );
    }

    public function selectedProductIds(): array
    {
        return $this->normalizeIds($this->deserializeSelection($this->product));
    }

    public function selectedBrandIds(): array
    {
        return $this->normalizeIds($this->deserializeSelection($this->brand_ids ?? null));
    }

    public function usesBrandScope(): bool
    {
        return ($this->scope_type ?? 'product') === 'brand';
    }

    public function usesProductScope(): bool
    {
        return ! $this->usesBrandScope();
    }

    public function usesTargeting(): bool
    {
        return in_array((int) $this->action, [2, 3, 5], true);
    }

    public function hasTargets(): bool
    {
        return count($this->selectedProductIds()) > 0 || count($this->selectedBrandIds()) > 0;
    }

    public function appliesToAllProducts(): bool
    {
        return (int) $this->action === 4 || ($this->usesTargeting() && ! $this->hasTargets());
    }

    public function matchesProduct(Product|int $product): bool
    {
        if ($this->appliesToAllProducts()) {
            return true;
        }

        if (is_numeric($product)) {
            $product = Product::select('id', 'category_id')->find($product);
        }

        if (! $product) {
            return false;
        }

        if ($this->usesBrandScope()) {
            return in_array((int) $product->category_id, $this->normalizeIds($this->selectedBrandIds()), true);
        }

        return in_array((int) $product->id, $this->normalizeIds($this->selectedProductIds()), true);
    }

    public function appliesPercentDiscountToProduct(Product|int $product): bool
    {
        return in_array((int) $this->action, [4, 5], true) && $this->matchesProduct($product);
    }

    public function applyPercentDiscount(float|int $amount): float
    {
        return ceil($amount - ($amount * ((float) $this->amount / 100)));
    }

    public function calculateCartDiscountAmount(iterable $cartProducts): float
    {
        $cartProducts = collect($cartProducts);
        $subtotal = 0;

        if ((int) $this->action === 0) {
            return (float) $this->amount;
        }

        if ((int) $this->action === 1) {
            $subtotal = $cartProducts->sum(fn ($product) => ((float) ($product['webprice'] ?? 0)) * ((int) ($product['qty'] ?? 1)));

            return $subtotal * ((float) $this->amount / 100);
        }

        if ((int) $this->action === 2 || (int) $this->action === 5) {
            foreach ($cartProducts as $cartProduct) {
                $productId = (int) ($cartProduct['id'] ?? 0);

                if (! $productId || ! $this->matchesProduct($productId)) {
                    continue;
                }

                $subtotal += ((float) ($cartProduct['webprice'] ?? 0)) * ((int) ($cartProduct['qty'] ?? 1));
            }

            return $subtotal * ((float) $this->amount / 100);
        }

        if ((int) $this->action === 3) {
            foreach ($cartProducts as $cartProduct) {
                $productId = (int) ($cartProduct['id'] ?? 0);

                if (! $productId || ! $this->matchesProduct($productId)) {
                    continue;
                }

                $qty = (int) ($cartProduct['qty'] ?? 1);
                $lineSubtotal = ((float) ($cartProduct['webprice'] ?? 0)) * $qty;
                $subtotal += min($lineSubtotal, ((float) $this->amount) * $qty);
            }

            return $subtotal;
        }

        return 0;
    }

    private function serializeSelection($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $values = array_values(array_unique(array_map(
            'strval',
            array_filter(Arr::wrap($value), fn ($item) => $item !== null && $item !== '')
        )));

        return count($values) ? serialize($values) : null;
    }

    private function deserializeSelection($value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = @unserialize($value);

        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function normalizeIds(array $ids): array
    {
        return array_map('intval', $ids);
    }
}
