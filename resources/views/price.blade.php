@if (Session::has('exchange_rate'))
    <?php 
     $rate = session('exchange_rate')['rate'];
     $symbol = session('exchange_rate')['symbol'].' '; ?>
@else
    <?php $rate = 1; $symbol = "$ "; ?>
@endif

@if (is_array($discount))
    <?php $indx = array_search($product->id,$productDiscount) ?>
    @if ($discount && $discount[$indx]['action'] == 4)
    <?php 
        $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin())); 
        $webprice = ceil($webprice - ($webprice * ($discount[$indx]['amount']/100))); 
    ?>
    <span class="{{ $class }} product_sale">{{$symbol. number_format($webprice,2) }}</span>
    @elseif ($discount && $discount[$indx]['action'] == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount))

    <?php 
        $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()));
        $webprice = ceil($webprice - ($webprice * ($discount[$indx]['amount']/100)))+.55;
    ?>
    <span class="{{ $class }} product_sale">{{$symbol. number_format($webprice,2) }}</span>
    @else
    <?php
        $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()))
    ?>
    <?php $precentOff = 0;$percentOffText=''; ?>
    @if ($product->p_retail>0)
    <?php 
        $retail = $product->p_retail;
        if ($webprice<$retail)
            $precentOff = number_format(abs(1 - ($webprice / $retail))*100,0);
        
        if ($precentOff>0)
            $percentOffText = '<span class="percentoff" style="color: green"> ('.$precentOff.'% off)</span>';
    ?>
    @endif
    
    <span class="{{ $class }}">{{$symbol. number_format($webprice*$rate,2) }}</span>{!! $percentOffText !!}
    @endif
@else
    @if ($discount && $discount->action == 4)
    <?php 
        $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin())); 
        $webprice = ceil($webprice - ($webprice * ($discount->amount/100))); 
    ?>
    <span class="{{ $class }} product_sale">{{ $symbol. number_format($webprice,2) }}</span>
    @elseif ($discount && $discount->action == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount))

    <?php 
        $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()));
        $webprice = ceil($webprice - ($webprice * ($discount->amount/100)))+.55;
    ?>
    <span class="{{ $class }} product_sale">{{ $symbol. number_format($webprice,2) }}</span>
    @else
    <?php
        $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()))
    ?>
    <?php $precentOff = 0;$percentOffText=''; ?>
    @if ($product->p_retail>0)
    <?php 
        $retail = $product->p_retail;
        if ($webprice<$retail)
            $precentOff = number_format(abs(1 - ($webprice / $retail))*100,0);
        
        if ($precentOff>0)
            $percentOffText = '<span class="percentoff hidden md:block text-xs"> ('.$precentOff.'% off)</span>';
    ?>
    @endif
    
    <span class="{{ $class }}">{!! $webprice == 0 ? "Call For Price" : $symbol.number_format($webprice*$rate,2) !!}</span>{!! $percentOffText !!}
    @endif
@endif