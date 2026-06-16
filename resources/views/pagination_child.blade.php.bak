<?php $productDiscount = array() ?>
@if ($discount)
    <?php if (is_array($discount))  
        $productDiscount = array_column($discount,'item') ;
    else
        $productDiscount=unserialize($discount->product);
     ?>
    
@endif

<div id="hits">
    <div class="ais-Hits">
        <ol class="ais-Hits-list">
        @foreach ($products as $product)
        <li class="ais-Hits-item">
            <div class="product-area">
                <div class="thumbnail" data-id="{{ $product->id }}">
                    @if (strpos(url()->current(),'chrono24')>0)
                        <?php $path = 'chrono24/watches/'.$product->slug ?>
                    
                    @else
                        <?php $path = 'new-unworn-certified-pre-owned-watches/'.$product->slug ?>
                    
                    @endif

                    @if (@count($product->images))
                    
                    <a href="/{{$path}}">
                    <?php $image = $product->images->first() ?>
                        @if (!file_exists(base_path(). '/public/images/thumbs/' . $image->location) || strpos($image->location,'snapshot') > 0)
                        <img src="/images/no-image.jpg" alt="">
                        @else
                        <img title="{{ $product->title }}" alt="{{ $product->title }}" src="{{ URL::to('/images/thumbs') .  '/' . $image->location }}" alt="">
                        @endif
                    @else
                        <img src="/images/no-image.jpg" alt="">
                    @endif
                    </a>

                    <?php 
                        if ($product->p_qty==0) {
                            $status = 'SOLD';
                            $color = "red;font-weight:bold";
                        } elseif ($product->p_status==3 || $product->p_status==9) {
                            $status = 'In Stock';
                            $color = 'green';
                        } else {
                            $status =$product->p_status==0 ? 'In Stock' : Status()->get($product->p_status );
                            $color = ($product->p_status ==0 ? 'green' : 'red;font-weight:600');
                        }
                    ?>

                    <span class="sticker-wrapper top-left"><span class="sticker new" style="color:{{$color}}">{{ $status }}</span></span>
                    <button class="btn btn-secondary btn-sm" onclick="window.location.href='/{{$path}}'" title="View details about {{ $product->title }}" aria-pressed="false" autocomplete="off" style="width: 100%">View Details</button>
                </div>
            
                <div class="caption">
                    <?php if (isset($product->categories->category_name)) { ?>
                        <a href="/{{$path}}">{{Conditions()->get($product->p_condition). ' '. $product->title}}</a>
                    <?php } else { ?>
                        <a href="/{{$path}}">{{Conditions()->get($product->p_condition). ' '. $product->p_model . ' ' . $product->p_reference}}</a>
                    <?php } ?>
                </div>
            
                <div class="price-area">
                    @if (isset($lpath) && $lpath=="withmarkups")
                        @if ($product->p_price3P>0)
                            <span class="price">${{ number_format($product->p_price3P,2) }}</span>
                        @else <span class="price">Call Us</span>
                        @endif
                    @else
                        @if ($product->p_newprice>0)
                            @if (Auth::guard('customer')->check())
                            <span class="price">${{ number_format($product->p_newprice,2) }}</span>
                            @else
                                @include ('price',['id'=>$product->id,'discount'=>$discount,'productDiscount'=>$productDiscount,'class'=>'price'])
                            @endif

                        @else
                            <span class="price" style="color:red">Call Us</span>
                        @endif
                    @endif
                </div>
            </div> 
        </li>

        @endforeach
    </ol>
</div>
</div>
@if (!$products->isEmpty())
    @include('toolbar')
@endif


<!-- <script>
    window.onpopstate = function(event) {
        location.reload();
    };
</script> -->