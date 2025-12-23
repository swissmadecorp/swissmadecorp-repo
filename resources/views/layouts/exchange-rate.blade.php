@if (Session::has('exchange_rate'))
    <?php
    $exchangeRate = session('exchange_rate');
    $current_rate = $exchangeRate['rate'];
    $currencyName = $exchangeRate['currency_name'];
    $description = $exchangeRate['description']; ?>

    @foreach ($rates as $rate)
        @if ($rate->currency_name == $currencyName )
            <?php if($currencyName == 'USD') 
                    $image_name = 'us'; 
                else $image_name = $exchangeRate['image_name']; 
            ?>
            
            <div class='active-currency'>
                <a href=''><img src='/assets/{{$image_name}}.png' data-id='{{$currencyName}}' alt='{{$description}}'></a>
            </div>
        @endif
    @endforeach

    <div class='currency-selection'>
    @foreach ($rates as $rate)
        @if ($rate->currency_name != $currencyName)
            <a href='#'><img src='/assets/{{$rate->image_name}}.png' data-id='{{$rate->currency_name}}' alt='{{$rate->description}}'></a>
        @endif
        
    @endforeach
    @if ($currencyName != 'USD')
    <a href='#'><img src='/assets/us.png' data-id='' alt='USA currency'></a>
    @endif
    
    </div>
@else
<div class="active-currency">
    <a href=""><img src="/assets/us.png" data-id="USD" alt="usa currency"></a>
</div>

<div class='currency-selection'>
    @foreach ($rates as $rate)
        <?php $imageName = $rate->image_name;
        $currency_name = $rate->currency_name;
        $description = $rate->description; 
        if($currency_name == 'USD') $imageName = 'us'; ?>

        @if ($rate->currency_name != 'USD')
            <a href='#'><img src='/assets/{{$imageName}}.png' data-id='{{$currency_name}}' alt='{{$description}}'></a>
        @endif
    @endforeach

    </div>

@endif