<?php $productDiscount = array() ?>

@if ($discount)
    <?php $productDiscount=array_column($discount,'item') ?>
@endif

@foreach ($products as $product)
    <div class="col-sm-6 col-md-4 col-lg-4 col-xl-2 custom-item-width">
        <div class="thumbnail" data-id="{{ $product->id }}">
            
            @if (strpos(url()->current(),'chrono24')>0)
                <?php $path = 'chrono24/watches/'.$product->slug ?>
            @elseif ($product->p_condition==3) 
                <?php $path = 'certified-pre-owned-watches/'.$product->slug ?>
            @else
                <?php $path = $product->slug ?>
            @endif

            @if (@count($product->images))
            <?php $image = $product->images->first() ?>
                @if (!file_exists(base_path(). '/public/images/thumbs/' . $image->location) || strpos($image->location,'snapshot') > 0)
                <a href="/{{$path}}"><img style="width: 225px" src="/public/images/no-image.jpg" alt=""></a>
                @else
                <a href="/{{$path}}"><img style="width: 225px" title="{{ $product->title }}" alt="{{ $product->title }}" src="{{ URL::to('/public/images/thumbs') .  '/' . $image->location }}" alt=""></a>
                @endif
            @else
                <a href="/{{$path}}"><img style="width: 225px" src="/public/images/no-image.jpg" alt=""></a>
            @endif
            
            <?php 
                if ($product->p_qty==0) {
                    $status = 'SOLD';
                    $color = "red;font-weight:bold";
                } elseif ($product->p_status==3) {
                    $status = 'Available';
                    $color = 'green';
                } else {
                    $status = Status()->get($product->p_status );
                    $color = ($product->p_status ==0 ? 'green' : '#000;font-weight:600');
                }
            ?>
            <span class="sticker-wrapper top-left">Status: <span class="sticker new" style="color:{{$color}}">{{ $status }}</span></span>
            <hr>
            <button class="btn btn-secondary btn-sm" onclick="window.location.href='/{{$path}}'" title="View details about {{ $product->title }}" aria-pressed="false" autocomplete="off" style="width: 100%">View Details</button>
            
            <div class="caption">
                <?php if (isset($product->categories->category_name)) { ?>
                    <a href="/{{$path}}">{{$product->title}}</a>
                <?php } else { ?>
                    <a href="/{{$path}}">{{$product->p_model . ' ' . $product->p_reference}}</a>
                <?php } ?>
            </div>

            <div style="margin-top: 4px">
                <hr>
                <div class="float-left">
                    <?php $s_discount = array() ?>
                    @if ($productDiscount)
                        <?php   
                            $indx=array_search($product->id,$productDiscount);
                            if ($indx) {
                                $s_discount = $discount[$indx];
                            }
                        ?>
                        @if ($s_discount)
                            @if ($s_discount['action'] == 4)
                                <span class="product_sale">Sale Price</span>
                            @elseif ($s_discount['action'] == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount))
                                <span class="product_sale">Sale Price</span>
                            @endif
                        @endif
                    @else
                        <span class="price">Price</span>
                    @endif
                </div>

                <div class="float-right">
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
                                @include ('prices',['id'=>$product->id,'discount'=>$s_discount,'productDiscount'=>$productDiscount,'class'=>'price'])
                            @endif

                        @else
                            <span class="price" style="color:red">Call Us</span>
                        @endif
                    @endif
                </div>
            </div>    
        </div>
    </div>
@endforeach

