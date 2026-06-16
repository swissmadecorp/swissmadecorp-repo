@if (Session::has('exchange_rate'))
    <?php
        $rate = session('exchange_rate')['rate'];
        $symbol = session('exchange_rate')['symbol'] . ' ';
    ?>
@else
    <?php $rate = 1; $symbol = '$ '; ?>
@endif

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
    $percentOffText = '';

    if ($product->p_retail > 0 && $webprice < $product->p_retail) {
        $precentOff = number_format(abs(1 - ($webprice / $product->p_retail)) * 100, 0);
        if ($precentOff > 0) {
            $percentOffText = '<span class="percentoff hidden md:block text-xs"> (' . $precentOff . '% off)</span>';
        }
    }
?>

<span class="{{ $class }}{{ $activeDiscount ? ' product_sale' : '' }}">{!! $webprice == 0 ? 'Call For Price' : $symbol . number_format($webprice * $rate, 2) !!}</span>{!! $percentOffText !!}
