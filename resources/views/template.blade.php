@extends ("layouts.default1")

<?php 
    $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()));$new_webprice=0;
    $productDiscount = array();
    if ($discount)
        $webprice = ceil($webprice - ($webprice * ($discount->amount/100))); 

if ($discount) {
    $productDiscount=unserialize($discount->product);
}
?>

@section('title', $product->title)
@section('mata-description', 'Detailed information of '.$product->title . ' for only $' . number_format($webprice,2))
@section('mata-keywords', Conditions()->get($product->p_condition).','.str_replace(' ',',',$product->title))

@section ('header')
<link href="{{ asset('/public/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
<link href="{{ asset('/public/lightslider/css/lightslider.css') }}" rel="stylesheet">
<link href="{{ asset('/public/lightgallery/css/lightgallery.css') }}" rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection

@section ("canonicallink")
    <link rel="canonical" href="{{config('app.url'). $product->slug }}" />
@endsection

@section("content")

    <?php $imageMain=$product->images()->first();$isPreviousNoImage=false; ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">HOME</a></li>
        @if ($product->group_id == 0)
            <li class="breadcrumb-item"><a href="http://swissmadecorp.com/watches" title="">WATCHES</a></li>
        @elseif ($product->group_id == 1)
            <?php $jewelryType = JewelryType()->get($product->jewelry_type) ?>
            <li class="breadcrumb-item"><a href="http://swissmadecorp.com/jewelry" title="">JEWELRY</a></li>
            <li class="breadcrumb-item"><a href="http://swissmadecorp.com/jewelry/{{strtolower($jewelryType)}}" title="">{{ strtoupper($jewelryType)}}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page"><strong>{{strtoupper($product->title)}}</strong></li>
    </ol>
    </nav>

    <div class="row">
        <div class="col-md-5 col-sm-7 col-xl-4 image-zoom">
            <div class="image">
                <ul id="lightslider">
                <?php $img = '' ?>
                @if (count($product->images))
                    @foreach ($product->images as $image)
                    @if (strpos($image->location,'snapshot') > 0 || (strpos($image->location,'.com') > 0))
                        @if ($isPreviousNoImage==false && count($product->images)>=1)
                        @php $image = '/public/images/no-image.jpg' @endphp
                        <li>
                        <img style="width: 225px" src="/public/images/no-image.jpg" alt="">
                        <?php $isPreviousNoImage = true ?>
                        </li>
                        @endif
                    @else
                        <?php if (!$img) $img = '/public/images/'. $image->location ?>
                        <li>
                        <a class="image-item" href="/public/images/{{$image->location}}">
                            <img data-src="{{$image->location}}" title="{{ $product->title }}" src="/public/images/thumbs/{{$image->location}}" alt="{{ $image->title}}" />
                            <div class="demo-gallery-poster">
                                <img src="/public/images/static/zoom.png">
                            </div>
                        </a>
                        </li>
                    @endif
                        
                    @endforeach

                @else 
                    @php $image = '/public/images/no-image.jpg' @endphp
                    <li>
                        <img style="width: 225px" src="/public/images/no-image.jpg" alt="{{ $product->title}}">
                    </li>
                @endif


                </ul>
            </div>

            
        </div>

        <?php $title = $product->title; $condition = $product->p_condition== 1 || $product->p_condition == 2 ? 'New / Unworn' : Conditions()->get($product->p_condition); ?>
        <div class="col-md-7 col-sm-12 col-xl-6 m_bottom_14">
            <div class="product-details-short">
                <h1 class="title">{{ strtoupper($title) }}</h1>
                
                <?php 
                    if ($product->p_qty<1) {
                        $status = 'SOLD';
                        $color = "red;font-weight:bold";
                    } elseif ($product->p_status==3) {
                        $status = 'In Stock';
                        $color = 'green';
                    } else {
                        $status = $product->p_status == 0 ? 'In Stock' : Status()->get($product->p_status);
                        $color = ($product->p_qty > 0 ? 'green' : 'red');
                    }
                ?>

                <table style="width: 100%;" cellpadding="3">
                    <tr>
                    <th>Availability:</th>
                    <td><span style="color: {{ $color  }}">{{ $status  }}</span></td>
                    </tr>
                    <tr>
                        <th>Condition:</th>
                        <td><div class="condition">{{ $condition }}</div></td>
                    </tr>
                    @if (isset($lpath) && $lpath=="withmarkups")
                        <tr>
                        <?php $webprice = $product->p_price3P ?>
                        <th>Web Price:</th>
                        @if ($product->p_price3P>0)    
                            <td><span class="p_price">${{ number_format($webprice,2) }}</span></td>
                        @else
                            <td><span class="p_price">Call For Price</span></td>
                        @endif
                        </tr>
                    @else
                        <tr>
                        <?php $loggedIn = false ?>
                        @if (Auth::guard('customer')->check())
                            <?php $loggedIn = true ?>
                            @if ($product->p_newprice>0)
                            <td>Dealer Price:</td>
                            <td>
                                <span class="p_price">${{ number_format($product->p_newprice,2) }}</span>
                                <span style="font-weight: 600">
                                    @if ($product->discount>0 && $product->discount-(CCMargin()*100) > 0) 
                                        ({{ number_format($product->discount-(CCMargin()*100),0) }}% Off)
                                    @endif
                                </span>
                            </td>
                            @else
                            <th>Dealer Price:</th>
                            <td><span class="p_price">Call For Price</span></td>
                            @endif
                        @else
                            @if ($discount && $discount->action == 4)
                                <th class="product_sale">Sale Price</th>
                            @elseif ($discount && $discount->action == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount))
                                <th class="product_sale">Sale Price</th>
                            @else
                                <th>Price</th>
                            @endif
                            <td>
                                @if ($webprice)
                                <span class="p_price">${{ number_format($webprice,2) }}</span>
                                <span style="font-weight: 600">
                                    @if ($product->discount>0 && $product->discount-(CCMargin()*100) > 0) 
                                        ({{ number_format($product->discount-(CCMargin()*100),0) }}% Off)
                                    @endif
                                </span>
                                @else
                                    <span class="p_price">Call For Price</span>
                                @endif
                            </td>
                        @endif
                        </tr>
                    @endif
                    <tr>
                        <th>Retail Price:</th>
                        <td>@if ($product->p_retail>0)
                            <span class="p_retail p_price">${{ number_format($product->p_retail,2) }}</span>
                            @else
                            <span class="p_retail">Not Available</span>
                            @endif
                        </td>
                    </tr>
                    
                    @if ($status=="In Stock" && $webprice)
                    <tr style="border-top: 1px solid #e5e5e5;text-align: right;">
                        <td colspan="2" class="pt-3 pb-3">
                            <?php $location = "https://web.whatsapp.com/send?phone=19176990831&text=Hello, I am on your website and I am interested in " . str_replace("'",'',$product->title) . " (".$product->id.")" ?>
                            <button class="btn btn-sm btn-secondary whatsapp" onclick='window.open("<?=$location ?>")'  aria-pressed="false" autocomplete="off"><i class="fab fa-whatsapp"></i></button>
                            <button class="btn btn-sm btn-secondary inquire" aria-pressed="false" autocomplete="off">Inquire</button>
                            <button class="btn btn-sm btn-warning add-to-cart">
                                <i class="fa fa-shopping-cart"></i>
                                &nbsp;Add to Cart
                            </button>
                            <div class='cart-anim'></div>
                        <td>
                    </tr>
                    @endif
                </table>
            </div>   
        </div>
        
        <div class="col-md-12 pt-2">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab" aria-controls="product" aria-selected="true">Product Details</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="return-policy-tab" data-toggle="tab" href="#return-policy" role="tab" aria-controls="return-policy" aria-selected="false">Return Policy</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" style="padding: 16px" id="product" role="tabpanel" aria-labelledby="product-tab">            
                    <div class="product-details">
                        <!-- <h1 class="title">Product Details</h1> -->
                        
                        <div class="attributes">
                            <ul>
                                <li>
                                    <span>Stock No:</span>
                                    <span>{{ $product->id }}</span>
                                </li>                      
                                <li>
                                    <span>Brand:</span>
                                    <?php if (isset($product->categories->category_name)) { ?>
                                        <span>{{ $product->categories->category_name }}</span>
                                    <?php } else { ?>
                                        <span>N/A</span>
                                    <?php } ?>
                                </li>
                                @if ($product->p_model)
                                <li>
                                    <span>Model:</span>
                                    <span>{{ $product->p_model }}</span>
                                </li> 
                                @endif
                                @if ($product->p_casesize)
                                <li>
                                    <span>Case Size:</span>
                                    <span>{{ $product->p_casesize }}</span>
                                </li>
                                @endif                        
                                @if ($product->p_reference)
                                <li>
                                    <span>Reference:</span>
                                    <span>{{ $product->p_reference }}</span>
                                </li>
                                @endif
                                @if ($product->serial_code)
                                <li>
                                    <span>Serial</span>
                                    <span>{{ $product->serial_code }}</span>
                                </li>
                                @endif                        
                                @if ($product->p_color)
                                <li>
                                    <span>Face Color:</span>
                                    <span>{{ $product->p_color }}</span>
                                </li>
                                @endif
                                @if (($product->p_box==0 || $product->p_box==1) && $product->group_id == 0)
                                <li>
                                    <span>Box:</span>
                                    <span>{{ $product->p_box==1 ? "Yes" : "No" }}</span>
                                </li>
                                @endif
                                @if (($product->p_papers==0 || $product->p_papers==1) && $product->group_id == 0)
                                <li>
                                    <span>Papers:</span>
                                    <span>{{ $product->p_papers==1 ? "Yes" : "No" }}</span>
                                </li>
                                @endif
                                @if ($product->p_strap>0)
                                <li>
                                    <span>Strap/Band:</span>
                                    <span>{{ Strap()->get($product->p_strap) }}</span>
                                </li>
                                @endif
                                @if ($product->p_clasp>0)
                                <li>
                                    <span>Clasp Type:</span>
                                    <span>{{ Clasps()->get($product->p_clasp) }}</span>
                                </li>
                                @endif                        
                                @if ($product->p_material>0)
                                <li>
                                    @if ($product->group_id == 0)
                                    <span>Case Material:</span>
                                    <span>{{ Materials()->get($product->p_material) }}</span>
                                    @elseif ($product->group_id == 1)
                                    <span> Material:</span>
                                    <span>{{ MetalMaterial()->get($product->p_material) }}</span>
                                    @endif
                                </li>
                                @endif
                                @if ($product->p_bezelmaterial>0)
                                <li>
                                    <span>Bezel Material:</span>
                                    <span>@if ($product->group_id == 0)
                                            {{BezelMaterials()->get($product->p_bezelmaterial) }}
                                        @elseif ($product->group_id == 1)
                                            {{ BezelMetalMaterial()->get($product->p_bezelmaterial) }}
                                        @endif
                                    </span>
                                </li>
                                @endif
                                @if ($product->water_resistance) 
                                    <li>
                                        <span>Water Resistance:</span>
                                        <span>{{ $product->water_resistance }}</span>
                                    </li>
                                @endif
                                @if ($product->movement>-1) 
                                <li>
                                    <span>Movement:</span>
                                    <span>{{ Movement()->get($product->movement) }}</span>
                                </li>
                                @endif
                                @if(!empty($custom_columns))
                                    @foreach ($custom_columns as $column)
                                        @if ($product->$column)
                                            <li>
                                                <span>{{ucwords(str_replace('-', ' ', $column))}}</span>
                                                <span>{{$product->$column}}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                @endif
                                
                            </ul>
                        </div>
                    </div> 
                </div>
                <div class="tab-pane fade" style="padding: 16px" id="return-policy" role="tabpanel" aria-labelledby="return-policy">
                    @if ($product->categories->category_name=="Rolex")
                        @if ($condition=="New / Unworn")
                            Due to the nature of certain conditions, because this is a new Rolex watch, the sale will be final and it's not eligible for a return for any reason.
                        @else
                        <h5>If you are not entirely satisfied with your purchase, we're here to help.</h5>

                        <ul class='return-policy-text'>
                            <li>We offer a 14 calendar days to return this item from the date you received it.</li>
                            <li>This item must have its original packaging that includes but not limited to a watch which was customized, 
                        engraved, resized, damaged, scratched, missing stickers, tags, plastic wraps, and box/or papers.</li>
                        <li>If any item is missing or is tempered with, the watch will <b>NOT</b> be accepted for return. </li>
                        <li>Depending on the condition of the watch, a minimim 5% restocking fee will apply.</li>
                        <li>All shipping charges are the sole responsibility of the customer.</li>
                        <li>All watches will be inspected before a refund is issued.</li>
                        </ul>
                        <p>Due to the nature of certain conditions, all <i><b>NEW ROLEX</b></i> sales are final and are not eligible for returns.</p>
                        @endif
                    @else
                        <h5>If you are not entirely satisfied with your purchase, we're here to help.</h5>

                        <ul class='return-policy-text'>
                            <li>We offer a 14 calendar days to return this item from the date you received it.</li>
                            <li>This item must have its original packaging that includes but not limited to a watch which was customized, 
                        engraved, resized, damaged, scratched, missing stickers, tags, plastic wraps, and box/or papers.</li>
                        <li>If any item is missing or is tempered with, the watch will <b>NOT</b> be accepted for return. </li>
                        <li>Depending on the condition of the watch, a minimim 5% restocking fee will apply.</li>
                        <li>All shipping charges are the sole responsibility of the customer.</li>
                        <li>All watches will be inspected before a refund is issued.</li>
                        </ul>
                        <p>Due to the nature of certain conditions, all <i><b>NEW ROLEX</b></i> sales are final and are not eligible for returns.</p>
                    @endif
                </div>
            </div>
        </div> 

        @if (isset($relatedProducts) && count($relatedProducts))
        <div class="col-md-12 pt-2">
            <h1 class="title">Related Products</h1>
            <div class="h-50">
            <ul id="relatedSlider" style="background: #fff">
                @foreach ($relatedProducts as $related)
                    @if ($related->product)
                    <li class="related-images"><a href="/{{$related->product->slug}}"><img src="/public/images/thumbs/{{ $related->product->images[0]->location }}"></a>
                        <div>
                            ${{ number_format(ceil($related->product->p_newprice+($related->product->p_newprice*CCMargin())),2) }}
                        </div> 
                    </li>
                    @endif
                @endforeach
            </ul>
            </div>
        </div>
        @endif


        <div id="product-inquiry" style="max-width: 900px;display:none">
            <div class="popup-header">
                <h3 style="padding: 12px; text-align: left">Product Inquiry</h3>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-3 img-panel">
                        <img src="" style="width:170px" class="mt-1" />
                        <div class="caption"></div>
                        <div class="price"></div>
                        <div class="retail"></div>
                    </div>
                    <div class="col-md-9 form-panel">
                        
                        <div class="pb-2">Send an inquiry by filling out the form below</div>

                        {{ Form::open(array('route' => 'watch.inquiry', 'data-parsley-validate', 'class' => 'inquiry-form')) }}
                            <input type="hidden" value="{{ $product->id }}" name="product_id" id="product_id">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('contact_name', 'Your Name') }}
                                        {{ Form::text('contact_name', null, array('class' => 'form-control')) }}
                                    </div>
                                    <div class="form-group" id="company-group">
                                        {{ Form::label('company_name', 'Company Name') }}
                                        {{ Form::text('company_name', null, array(
                                                'class' => 'form-control',
                                                'required' => 'required',
                                                'data-parsley-required-message' => 'Company Name is required',
                                                'data-parsley-trigger'          => 'change focusout',
                                                'data-parsley-class-handler'    => '#company-group',
                                                'data-parsley-minlength'        => '2')) 
                                        }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('email', 'Email Address') }}
                                        {{ Form::text('email', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('phone', 'Phone Number') }}
                                        {{ Form::text('phone', null, 
                                                [
                                                'class' => 'form-control',
                                                'required' => 'required',
                                                'data-parsley-required-message' => 'Phone Number is required',
                                                'data-parsley-trigger'          => 'change focusout',
                                                'data-parsley-class-handler'    => '#company-group',
                                                ]) 
                                        }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{ Form::label('notes', 'Additional Notes') }}
                                        {{ Form::textarea('notes', null, ['class' => 'form-control','rows' => 4, 'cols' => 40]) }}
                                    </div>
                                    <div class="g-recaptcha" data-sitekey="{{config('recapcha.key_v2') }}"></div>
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="invalid-feedback" style="display: block;">
                                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                        </span>
                                    @endif
                                    <div class="pb-3 float-right">
                                        {{ Form::submit('Send Inquiry', array('class' => 'btn btn-primary submit-inquiry')) }}
                                    </div>
                                </div>
                                
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section ('footer')

<!-- PARSLEY -->
    <script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    <script src="{{ asset('/public/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/public/lightgallery/js/lightgallery-all.min.js') }}"></script>
    <script src="{{ asset('/public/lightgallery/js/lg-thumbnail.min.js') }}"></script>
    <script src="{{ asset('/public/js/parsley.js') }}"></script>
    <script src="{{ asset('/public/lightslider/js/lightslider.js') }}"></script>
@endsection

@section ("jquery")

<script>
    
    $(document).ready( function() {

        // $('.categories').css('height',$('.inquire').offset().top-150)
        // $( window ).resize(function() {
        //     $('.categories').css('height',$('.inquire').offset().top-150)
        // })
        var winWidth = $('.add-to-cart').width();

        resizeAnimateButton()
        $(window).resize(function(){
            
            resizeAnimateButton()
        });

        function resizeAnimateButton() {
            if ($('.add-to-cart').length) {
                newWinWidth = $('.add-to-cart').width();
                $('.cart-anim').width(newWinWidth);
                var pos = $('.add-to-cart').position();
                var page = Math.floor(Math.abs(pos.left)/winWidth);
                var offset = winWidth - newWinWidth;
                var leftPos = pos.left + (offset * page) + 'px';
                $('.cart-anim').css({left: leftPos, top: pos.top});
                winWidth = newWinWidth;
            }
        }

        var slider = $('#lightslider').lightSlider({
            item: 1,
            mode: "slide",
            enableTouch:false,
            enableDrag:true,
            freeMove:true,
            swipeThreshold: 40,
        });


        var relatedSlider = $('#relatedSlider').lightSlider({
            item:4,
            slideMove:1,
            enableTouch:true,
            responsive : [
            
            {
                breakpoint:768,
                settings: {
                    item:3,
                    slideMove:1,
                    slideMargin:6,
                  }
            },
            {
                breakpoint:480,
                settings: {
                    item:2,
                    slideMove:1
                  }
            }
        ]
        });

        $('#lightslider').lightGallery({
            selector: '.image-item',
            mode: 'lg-fade',
            mousewheel: true,
            download: false,
            share: false,
            fullScreen: false,
            thumbnail:true,
            animateThumb: false,
            showThumbByDefault: false,
            index: 0
        })

        $('.add-to-cart').click( function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                data: {id: '{{ $product->id }}'},
                url: "{{route('add.to.cart')}}",
                success: function (result) {
                    if (isMobile())
                        document.location.href = '/cart';
                    else {
                        if ($('.cart-anim').length>0) {
                            $('html,body').animate({ scrollTop: 0 }, 'slow'); 
                            $('.cart-anim').addClass('move-to-cart')
                            
                            setTimeout(function(){ window.location.reload(); }, 500);
                        }
                    }
                }
            })
        })

        $('.inquire').click( function () {
            var _this = $(this);
            $('.inquiry-form')[0].reset();

            $.fancybox.open({
                src: "#product-inquiry",
                type: 'inline',
                beforeShow: function() {
                    $('.img-panel img').attr('src', $('.image img').attr('src'));
                    $('.img-panel .caption').text($('.title').text());
                    $('.img-panel .price').text('Price: '+$('.p_price').text());
                    if ($('.p_retail').length > 0)
                        $('.img-panel .retail').text('Retail: '+$('.p_retail').text());
                    else $('.img-panel .retail').hide();
                }
            });
        })


        Parsley.on('form:submit', function() {
            $.ajax ( {
                type: 'post',
                dataType: 'json',
                url: $('.inquiry-form').attr('action'),
                data: {inquiry: $('.inquiry-form').serialize(),_token: "{{csrf_token()}}"},
                success: function(response) {
                    // if ($.isEmptyObject(response.error)) {
                    if (response.error=='success') {
                        $.fancybox.close();
                        $.fancybox.open({
                            src: "<div><p style='padding: 30px 20px;width: 90%'>Your inquiry has been submitted successfully. Someone will get back to you as soon as possible.</p></div>",
                            type: 'html',
                        });
                    } else {
                        build = '';
                        for (i=0;i<response.error.length;i++) {
                            build = build+'<p style="margin:7px 18px 10px">'+response.error[i]+'</p>'
                        }
                        $.fancybox.open({
                            src: '<div style="padding: 30px 20px;width: 300px"><div class="popup-header"><h3 style="padding: 12px; text-align: left">There was an error</h3></div>'+build+'</div>',
                            type: 'html',
                        });
                    }
                }
            })

            return false;
        });

        
    })
</script>

@endsection