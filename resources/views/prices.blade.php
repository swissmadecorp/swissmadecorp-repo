@if ($discount && $discount['action'] == 4)
<?php 
    $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin())); 
    $webprice = ceil($webprice - ($webprice * ($discount['amount']/100))); 
?>
<span class="{{ $class }} product_sale">${{ number_format($webprice,2) }}</span>
@elseif ($discount && $discount['action'] == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount))
<?php 
    $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()));
    $webprice = ceil($webprice - ($webprice * ($discount['amount']/100)));
?>
<span class="{{ $class }} product_sale">${{ number_format($webprice,2) }}</span>
@else
<?php
    $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()))
?>
<span class="{{ $class }}">${{ number_format($webprice,2) }}</span>
@endif