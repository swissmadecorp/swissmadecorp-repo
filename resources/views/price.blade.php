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
    $showOriginal = isset($showOriginal) ? (bool) $showOriginal : false;
    $percentOffText = '';

    if ($product->p_retail > 0 && $webprice < $product->p_retail) {
        $precentOff = number_format(abs(1 - ($webprice / $product->p_retail)) * 100, 0);
        if ($precentOff > 0) {
            $percentOffText = '<span class="percentoff hidden md:block text-xs"> (' . $precentOff . '% off)</span>';
        }
    }
?>

@if ($webprice == 0)
    <span class="{{ $class }}">Call For Price</span>
@elseif ($showOriginal && $activeDiscount && $webprice < $basePrice)
    <span class="inline-flex flex-wrap items-baseline gap-3">
        <span class="font-medium text-stone-400 line-through">
            {{ $symbol . number_format($basePrice * $rate, 2) }}
        </span>
        <span class="{{ $class }} product_sale text-green-600">
            {{ $symbol . number_format($webprice * $rate, 2) }}
        </span>
    </span>
@else
    <span class="{{ $class }}{{ $activeDiscount ? ' product_sale' : '' }}">{!! $symbol . number_format($webprice * $rate, 2) !!}</span>
@endif
{!! $percentOffText !!}
