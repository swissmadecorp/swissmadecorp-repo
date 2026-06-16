<?php
    if (!isset($product) && isset($id)) {
        $product = \App\Models\Product::find($id);
    }

    if (!isset($product) || !$product) {
        return;
    }

    $basePrice = ceil($product->p_newprice + ($product->p_newprice * CCMargin()));
    $activeDiscount = \App\Models\DiscountRule::currentPricingRuleForProduct($product);
    $webprice = $activeDiscount ? $activeDiscount->applyPercentDiscount($basePrice) : $basePrice;
?>

<span class="{{ $class }}{{ $activeDiscount ? ' product_sale' : '' }}">${{ number_format($webprice, 2) }}</span>
