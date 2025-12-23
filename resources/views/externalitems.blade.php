@foreach ($products as $index => $product)
    <?php $image = ""; $caption = ""; $availability = ""; $price = ""; ?>
    @if (count($product)==10)
        <?php 
            $image = $product[1];
            $caption = $product[2];
            $availability = $product[4];
            $price = $product[9];
        ?>
    @else
        <?php 
            $image = $product[0];
            $caption = $product[1];
            if (isset($product[3]))
                $availability = $product[3];
            if (isset($product[8]))
                $price = $product[8];
        ?>
    @endif
    @if ($image)
        <li class="ais-Hits-item">
            <div class="product-area">
                <div class="thumbnail">
                    <a href=""><img src="{{$image}}" /></a>
                    <span class="sticker-wrapper top-left">
                        <span class="sticker">{{$availability}}</span>
                    </span>
                    <button class="btn btn-secondary btn-sm" onclick="window.location.href=''" 
                        title="" 
                        aria-pressed="false" autocomplete="off" style="width: 100%">View Details</button>
                </div>
                <div class="caption">
                    <a href="">{{$caption}}</a>
                </div>
                <div class="price-area">
                    <span class="price">${{ number_format($price,2)}}</span>
                </div>
            </div>
        </li>
    @endif
@endforeach