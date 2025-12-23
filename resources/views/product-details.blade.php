@extends ("layouts.default1")

<?php 
    $webprice = ceil($product->p_newprice+($product->p_newprice*CCMargin()));$new_webprice=0;
    $productDiscount = array();
    if ($discount) {
        $webprice = ceil($webprice - ($webprice * ($discount->amount/100))); 
        $productDiscount=unserialize($discount->product);
    }
?>

@if ($product->p_metatitle)
    @section('meta-title', $product->p_metatitle)
@endif

@section('title', $product->title)

@if ($product->p_metadescription)
    @section('meta-description', $product->p_metadescription)
@else
    @section('meta-description', 'Detailed information of '.$product->title . ' for only $' . number_format($webprice,2))
@endif

@if ($product->p_keywords)
    @section('meta-keywords', $product->p_keywords)
@else
    @section('meta-keywords', Conditions()->get($product->p_condition).','.str_replace(' ',',',$product->title))
@endif

@section ('header')
<link href='/fancybox/jquery.fancybox.min.css' rel="stylesheet">
<!-- <link href='/powerful-calendar/style.css' rel="stylesheet"> -->
<!-- <link href='/powerful-calendar/theme.css' rel="stylesheet"> -->
<!-- <link href='/powerful-calendar/page.css' rel="stylesheet"> -->
<!-- <link href='/datetimepicker-master/build/jquery.datetimepicker.min.css' rel="stylesheet"> -->
<link href='/calendar-master/dist/css/pignose.calendar.min.css' rel="stylesheet">
<link href='/lightslider/css/lightslider.css' rel="stylesheet">
<link href='/lightgallery/css/lightgallery.css' rel="stylesheet">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection

@section ("canonicallink")
    <link rel="canonical" href="{{config('app.url'). $product->slug }}" />
@endsection

@section("content")

    <input type="hidden" name="rates" value="<?php print_r(session('exchange_rate')) ?>">

    <?php $imageMain=$product->images()->first();$isPreviousNoImage=false; ?>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">HOME</a></li>
        @if ($product->group_id == 0)
            <li class="breadcrumb-item"><a href="{{ (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '/watches' }}" title="">WATCHES</a></li>
        @elseif ($product->group_id == 1)
            <?php $jewelryType = JewelryType()->get($product->jewelry_type) ?>
            <li class="breadcrumb-item"><a href="/jewelry" title="">JEWELRY</a></li>
            <li class="breadcrumb-item"><a href="/jewelry/{{strtolower($jewelryType)}}" title="">{{ strtoupper($jewelryType)}}</a></li>
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
                    
                        <?php if (!$img) $img = '/images/'. $image->location ?>
                        <li>
                        <a class="image-item" href="/images/{{$image->location}}">
                            <img data-src="{{$image->location}}" title="{{ $product->title }}" src="/images/thumbs/{{$image->location}}" alt="{{ $image->title}}" />
                            <div class="demo-gallery-poster">
                                <img src="/images/static/zoom.png">
                            </div>
                        </a>
                        </li>
                    
                        
                    @endforeach

                @else 
                    @php $image = '/images/no-image.jpg' @endphp
                    <li>
                        <img style="width: 225px" src="/images/no-image.jpg" alt="{{ $product->title}}">
                    </li>
                @endif


                </ul>
            </div>

            
        </div>

        <?php $title = $product->title; $condition = $product->p_condition== 1 || $product->p_condition == 2 ? 'New / Unworn' : Conditions()->get($product->p_condition); ?>

        <div class="col-md-6 col-sm-12 col-xl-4 m_bottom_14 anim-resizer">
            <div class="product-details-short">
                <h1 class="title">{{ strtoupper($title) }}</h1>
                
                <?php 
                    $status = Status()->get($product->p_status);
                    if ($product->p_qty<1 || $product->p_status == 8) {
                        $status = 'SOLD';
                        $color = "red;font-weight:bold";
                    } elseif ($product->p_status == 7) {
                        $status = 'UNAVAILABLE';
                        $color = "red;font-weight:bold";
                    } elseif ($product->p_status==3 || $product->p_status==9) {
                        $status = "In Stock";
                        $color = 'green';
                    } elseif ($product->p_status == '1') {
                        $color = 'red';
                    } else {
                        $status = $product->p_status == 0 ? 'In Stock' : Status()->get($product->p_status);
                        $color = ($product->p_qty > 0 ? 'green' : 'red');
                    }
                ?>

                @if ($product->p_qty > 0 && $product->p_status == 0 ) 
                <table style="width: 100%" colpadding="3">
                    <tr>
                        <td>
                            Want to see this watch in person? Click the button to make an appointment with us.<br>
                        </td>
                        <td>
                            <div style="height: 40px">
                                <a class="scheduling" href="#"><img border="none" src="https://storage.googleapis.com/full-assets/setmore/images/1.0/Settings/book-now-blue.png" alt="Book an appointment using Setmore" /></a>
                            </div>
                        </td>
                    </tr>
                </table>
                <hr><br><hr>
                @endif
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
                            <th>Dealer Price:</th>
                            <td>
                                <span class="p_price">${{ number_format($product->p_newprice,2) }}</span>
                                <span style="font-weight: 600">
                                    @if ($product->percent>0 && $product->percent-(CCMargin()*100) > 0) 
                                        ({{ number_format($product->percent-(CCMargin()*100),0) }}% Off)
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
                                    @include ('price',['product'=>$product,'discount'=>$discount,'productDiscount'=>$productDiscount,'class'=>'p_price mainprice'])
                                @else
                                    <span class="p_price">Call For Price</span>
                                @endif
                            </td>
                        @endif
                        </tr>
                    @endif
                    <!-- <tr>
                        <th>Your Price:</th>
                        <td><input type="text" name="auction" class="form-control" id="auction" /></td>
                    </tr> -->
                    <tr>
                        <th>Retail Price:</th>
                        <td>@if ($product->p_retail>0)
                            <span class="p_retail p_price">${{ number_format($product->p_retail,2) }}</span>
                            @else
                            <span class="p_retail">Not Available</span>
                            @endif
                        </td>
                    </tr>
                    @if ($product->p_qty > 1)
                    <tr>
                        <th>Qty:</th>
                        <td>
                            <input type="text" name="order_qty" class="form-control" id="order_qty" value="1" />
                            
                        </td>
                    </tr>
                    @endif
                    <tr>@if (Session::has('exchange_rate'))
                            <?php 
                            $rate = session('exchange_rate')['rate'];
                            $symbol = session('exchange_rate')['symbol'].' '; ?>
                        @else
                            <?php $rate = 1; $symbol = "$"; ?>
                        @endif

                        <?php $wire_price = $product->p_newprice; ?>
                        
                        <?php  if ($wire_price > 1 && $status == 'In Stock' && $product->wire_discount) { ?>
                        <td colspan="2">Save an additional <b style="color:red"><?= $symbol.$product->web_price-$wire_price ?></b> when you pay with <a style="color: blue" href="\wire-transfer-guide">Bank Wire</a> during checkout. You pay <b style="color:red"><?= $symbol.number_format($wire_price,2) ?></b>.</td>
                        <?php } ?>
                    </tr>
                    <tr style="border-top: 1px solid #e5e5e5;text-align: right;">
                        <td colspan="2" class="pt-3 pb-3">
                            <?php $location = "https://web.whatsapp.com/send?phone=19176990831&text=Hello, I am on your website and I am interested in " . str_replace("'",'',$product->title) . " (".$product->id.")" ?>
                            
                            <button class="btn btn-sm btn-secondary whatsapp" aria-label="Contact us via whatsapp" onclick='window.open("<?=$location ?>")' autocomplete="off"><i class="fab fa-whatsapp"></i></button>
                            <button class="btn btn-sm btn-secondary inquire" autocomplete="off">Inquire</button>
                            @if ($status=="In Stock" && $product->p_price3P>0)
                            <button class="btn btn-sm btn-success offer" autocomplete="off">Make Offer</button>
                            <button class="btn btn-sm btn-warning add-to-cart">
                                <i class="fa fa-shopping-cart"></i>
                                &nbsp;Add to Cart
                            </button>
                            @endif
                            <div class='cart-anim'></div>
                        <td>
                    </tr>
                    
                </table>
            </div>   
        </div>
        

        @if ($product->p_longdescription)
        <div class="col-md-12 col-sm-12 col-xl-4 anim-resizer">
            <ul class="nav nav-tabs" id="description" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="product" aria-selected="true">Description</a>
                </li>
            </ul>
            <div class="tab-content long_description">
                <div class="tab-pane fade show active" style="padding: 15px 15px 0 15px" id="long_description" role="tabpanel" aria-labelledby="description-tab">
                    @if ($product->p_longdescription)
                        <p>{!! $product->p_longdescription !!}</p>
                    @endif
                    @if ($product->p_smalldescription)
                        <p><em>{!! $product->p_smalldescription !!}</em></p>
                    @endif
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12 col-sm-12 anim-resizer">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="product-tab" data-toggle="tab" href="#product" role="tab" aria-controls="product" aria-selected="true">Product Details</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="return-policy-tab" data-toggle="tab" href="#return-policy" role="tab" aria-controls="return-policy" aria-selected="false">Return Policy</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="warranty-tab" data-toggle="tab" href="#warranty" role="tab" aria-controls="warranty" aria-selected="false">Warranty</a>
                </li>
            </ul>
            <div class="tab-content myTabContent">
                <div class="tab-pane fade show active" style="padding: 16px" id="product" role="tabpanel" aria-labelledby="product-tab">
                    <div class="product-details">
                        <!-- <h1 class="title">Product Details</h1> -->
                        
                        <div class="attributes">
                            <ul>
                                <li>
                                    <span>Availability:</span>
                                    <span>@if ($status == "SOLD") <span style="color:red">Out of Stock</span> @else {{ $status }} @endif</span>
                                </li>                                
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
                                @if ($product->p_year)
                                <li>
                                    <span>Production Year:</span>
                                    <span>{{ $product->p_year }}</span>
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
                                @if ($product->p_dial_style)
                                <li>
                                    <span>Dial Style:</span>
                                    <span>{{ DialStyle()->get($product->p_dial_style) }}</span>
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
                                                <span>{{ucwords(str_replace(['-','c_'], ' ', $column))}}</span>
                                                <span>{{$product->$column}}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                @endif
                                
                            </ul>
                        </div>
                    </div> 
                </div>
                @if ($product->categories)
                <div class="tab-pane fade" style="padding: 16px" id="warranty" role="tabpanel" aria-labelledby="warranty">
                    
                        @if ($product->categories->category_name=="Rolex")
                            @if ($condition=="New / Unworn")
                            <p>Swiss Made Corp. takes pride in providing discerning customers with an unparalleled selection of exquisite watches. As a dedicated reseller, we stand behind the quality and authenticity of every timepiece we offer. To demonstrate our unwavering commitment to customer satisfaction, Swiss Made Corp. provides a three-year warranty on all mechanical aspects of the watches we resell. This warranty serves as a testament to our dedication to ensuring that each watch maintains its exceptional performance and enduring value. Customers can trust in Swiss Made Corp.'s reputation for excellence and heritage in Swiss watchmaking, knowing that their investment is safeguarded by a warranty that reflects our commitment to upholding the highest standards in the industry.</p>
                            @else 
                                <p>
                                    Swiss Made Corp. takes pride in providing discerning customers with an unparalleled selection of exquisite pre-owned watches. As a dedicated reseller, we stand behind the quality and authenticity of every pre-owned timepiece we offer. To demonstrate our unwavering commitment to customer satisfaction, Swiss Made Corp. provides a one-year warranty on all mechanical aspects of the pre-owned watches we resell. This warranty serves as a testament to our dedication to ensuring that each pre-owned watch maintains its exceptional performance and enduring value. Customers can trust in Swiss Made Corp.'s reputation for excellence and heritage in Swiss watchmaking, knowing that their investment in a pre-owned timepiece is safeguarded by a warranty that reflects our commitment to upholding the highest standards in the industry.</p>
                            @endif
                        @elseif ($product->categories->category_name=="Breitling")
                            @if ($condition=="New / Unworn")
                            <p>Swiss Made Corp. takes pride in providing discerning customers with an unparalleled selection of exquisite watches. As a dedicated reseller, we stand behind the quality and authenticity of every timepiece we offer. To demonstrate our unwavering commitment to customer satisfaction, Swiss Made Corp. provides a five-year warranty on all mechanical aspects of the watches we resell. This warranty serves as a testament to our dedication to ensuring that each watch maintains its exceptional performance and enduring value. Customers can trust in Swiss Made Corp.'s reputation for excellence and heritage in Swiss watchmaking, knowing that their investment is safeguarded by a warranty that reflects our commitment to upholding the highest standards in the industry.</p>
                            @else 
                                <p>
                                    Swiss Made Corp. takes pride in providing discerning customers with an unparalleled selection of exquisite pre-owned watches. As a dedicated reseller, we stand behind the quality and authenticity of every pre-owned timepiece we offer. To demonstrate our unwavering commitment to customer satisfaction, Swiss Made Corp. provides a one-year warranty on all mechanical aspects of the pre-owned watches we resell. This warranty serves as a testament to our dedication to ensuring that each pre-owned watch maintains its exceptional performance and enduring value. Customers can trust in Swiss Made Corp.'s reputation for excellence and heritage in Swiss watchmaking, knowing that their investment in a pre-owned timepiece is safeguarded by a warranty that reflects our commitment to upholding the highest standards in the industry.</p>
                            @endif
                        @else
                            <!-- <p>Swiss Made Corp provides with 1 year warranty for all new / pre-owned watches that have mechanical issues only and more than 1 year for Rolex and Breitling watches.</p> -->
                            <p>Swiss Made Corp. takes pride in providing discerning customers with an unparalleled selection of exquisite pre-owned watches. As a dedicated reseller, we stand behind the quality and authenticity of every pre-owned timepiece we offer. To demonstrate our unwavering commitment to customer satisfaction, Swiss Made Corp. provides a one-year warranty on all mechanical aspects of the pre-owned watches we resell. This warranty serves as a testament to our dedication to ensuring that each pre-owned watch maintains its exceptional performance and enduring value. Customers can trust in Swiss Made Corp.'s reputation for excellence and heritage in Swiss watchmaking, knowing that their investment in a pre-owned timepiece is safeguarded by a warranty that reflects our commitment to upholding the highest standards in the industry.</p>
                        @endif
                    
                </div>
                
                
                <div class="tab-pane fade" style="padding: 16px" id="return-policy" role="tabpanel" aria-labelledby="return-policy">
                
                    @if ($product->categories->category_name=="Rolex")
                        @if ($condition=="New / Unworn")
                            <p>Due to the unique nature of certain conditions associated with the Rolex watch, we regret to inform you that all sales of this new timepiece will 
                                be considered final and are not eligible for return under any circumstances.</p>
                            <p>At Rolex, we take utmost pride in the craftsmanship and precision that goes into each of our timepieces, ensuring that they meet the highest standards 
                                of quality and luxury. As a result of the meticulous attention to detail and the exclusive nature of these watches, we must uphold a strict final sale policy.</p>
                            <p>We understand that selecting a Rolex watch is a significant decision, and we encourage you to take your time in considering your purchase. Our knowledgeable 
                                staff is available to provide you with all the necessary information to make an informed choice. Additionally, we offer comprehensive warranties to ensure that your 
                                investment is protected and that your Rolex watch will continue to perform flawlessly for generations to come.</p>
                            <p>We appreciate your understanding of our final sale policy, which enables us to maintain the integrity and exclusivity of the Rolex brand. Should you have any inquiries 
                                or require assistance, please do not hesitate to reach out to our dedicated customer service team. We are committed to ensuring your satisfaction and providing you with an 
                                exceptional experience throughout your ownership of a genuine Rolex watch.</p>
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
                @endif
            </div>
        </div> 

        @if (isset($relatedProducts) && count($relatedProducts))
        <div class="col-md-12 pt-2">
            <h1 class="title">You may also like</h1>
            <div class="h-50">
            <ul id="relatedSlider" style="background: #fff">
                @foreach ($relatedProducts as $related)
                    @if ($related->product)
                    <li class="related-images"><a href="/new-unworn-certified-pre-owned-watches/{{$related->product->slug}}"><img src="/images/thumbs/{{ count($related->product->images) ? $related->product->images[0]->location : 'no-image.jpg' }}"></a>
                        <div>
                            <div style="height: 50px;overflow:hidden">{{strtoupper($product->title)}}</div>
                            @include ('price',['product'=>$related->product,'discount'=>$discount,'productDiscount'=>$productDiscount,'class'=>'p_price mainprice'])
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

                        <form method="POST" action="https://swissmadecorp.com/ajaxinquiry" accept-charset="UTF-8" data-parsley-validate="" class="inquiry-form" novalidate="">
                            @csrf
                            <input type="hidden" value="{{ $product->id }}" name="product_id" id="product_id">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="contact_name">Your Name</label>
                                        <input class="form-control" name="contact_name" type="text" >
                                    </div>
                                    <div class="form-group" id="company-group">
                                        <label for="company_name">Company Name</label>
                                        <input class="form-control" required="required" 
                                            data-parsley-required-message="Company Name is required" 
                                            data-parsley-trigger="change focusout" 
                                            data-parsley-class-handler="#company-group" 
                                            data-parsley-minlength="2" 
                                            name="company_name" 
                                            type="text" 
                                            id="company_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input class="form-control" name="email" type="text" id="email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input class="form-control" 
                                            required="required" 
                                            data-parsley-required-message="Phone Number is required" 
                                            data-parsley-trigger="change focusout" 
                                            data-parsley-class-handler="#company-group" 
                                            name="phone" 
                                            type="text" 
                                            id="phone">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes">Additional Notes</label>
                                        <textarea class="form-control" rows="4" cols="40" name="notes" id="notes"></textarea>
                                    </div>
                                    <div class="g-recaptcha" data-sitekey="{{config('recapcha.key_v2') }}"></div>
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="invalid-feedback" style="display: block;">
                                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                        </span>
                                    @endif
                                    <div class="pb-3 float-right">
                                    <input class="btn btn-primary submit-inquiry" type="submit" value="Send Inquiry">
                                    </div>
                                </div>
                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
        
        <div id="div_appointment">
            <!-- <input id="datetimepicker" type="text" > -->
            <div id="calendar_container">
                <div class="calendar"></div>
                <div class="selected_date"></div>
                <div class="time_selection"></div>
            </div>

            <div id="contact_container" style="display: none;padding: 12px">
                <h3>Contact Information</h3>

                <a href="#" class="mt-3 mb-3"><i class="fa fa-chevron-left"></i> Back to calendar</a>
                <form id="bookings_form" data-parsley-validate>
                <div class="form-group">
                    <label for="contactname">Contact name:</label>
                    <input type="text" 
                            data-parsley-required-message='Contact Name is required'
                            data-parsley-trigger='change focusout'
                            data-parsley-class-handler='#contact_container'
                            class="form-control" name="contactname" id="contactname" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="phone" class="form-control" 
                        data-parsley-required-message='Phone # is required'
                        data-parsley-trigger='change focusout'
                        data-parsley-class-handler='#contact_container'
                        name="phone" id="phone" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" 
                        data-parsley-required-message='Email address is required'
                        data-parsley-trigger='change focusout'
                        data-parsley-class-handler='#contact_container'
                        name="email" id="email" required>
                </div>

                <div id="appointment" class="p-3 m-1 row">
                    <div id="date" class="col-9"></div>
                    <input type="hidden" name="book_date" id="book_date">
                    <input type="hidden" name="book_time" id="book_time">
                    <div class="col-3">
                        <input class="btn btn-secondary" value="Book" type="submit">
                    </div>
                </div>
                </form>
                
            </div>
        </div>

        <div id="price-offer" style="max-width: 900px;display:none">
            <div class="popup-header">
                <h3 style="padding: 12px; text-align: left">Price offer</h3>
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
                        
                        <div class="pb-2">Send an offer by filling out the form below</div>
                        <form method="POST" action="https://swissmadecorp.com/ajaxpriceoffer" accept-charset="UTF-8" data-parsley-validate="" class="offer-form" novalidate="" siq_id="autopick_4636">
                            @csrf
                            <input type="hidden" value="{{ $product->id }}" name="product_offer_id" id="product_offer_id">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group" id="offer_fullname-group">
                                        <label for="offer_full_name">Contact name</label>
                                        <input class="form-control" 
                                            required="required" 
                                            data-parsley-required-message="Contact Name is required" 
                                            data-parsley-trigger="change focusout" 
                                            data-parsley-class-handler="#offer_fullname-group" 
                                            data-parsley-minlength="2" 
                                            name="offer_full_name" 
                                            type="text" id="offer_full_name" 
                                            data-parsley-id="27">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                    <label for="offer_email">Email address</label>
                                        <input class="form-control" required="required" 
                                            data-parsley-required-message="Email address is required" 
                                            data-parsley-trigger="change focusout" 
                                            data-parsley-class-handler="#offer_fullname-group" 
                                            name="offer_email" type="text" id="offer_email">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                    <label for="offer_amount">Price offer</label>
                                            <input class="form-control" 
                                                required="required" 
                                                data-parsley-required-message="Price offer is required" 
                                                data-parsley-trigger="change focusout" 
                                                data-parsley-class-handler="#offer_fullname-group" 
                                                name="offer_amount" 
                                                type="text" 
                                                id="offer_amount">
                                    </div>
                                    <div class="g-recaptcha" data-sitekey="{{config('recapcha.key_v2') }}"></div>
                                    @if ($errors->has('g-recaptcha-response'))
                                        <span class="invalid-feedback" style="display: block;">
                                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                        </span>
                                    @endif
                                    <div class="pb-3 float-right">
                                    <input class="btn btn-primary submit-offer" type="submit" value="Submit offer">
                                    </div>
                                </div>
                                
                            </div>
                        </form>
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
    <script src="/js/parsley.js"></script>
    <!-- <script src="/powerful-calendar/calendar.min.js"></script> -->
    <script src="/calendar-master/dist/js/pignose.calendar.full.min.js"></script>
    <script src="/fancybox/jquery.fancybox.min.js"></script>
    <script src="/lightgallery/js/lightgallery-all.min.js"></script>
    <script src="/lightgallery/js/lg-thumbnail.min.js"></script>
    <!-- <script src="{{-- asset('/js/keyframes.js') --}}"></script> -->
    <script src="/lightslider/js/lightslider.js"></script>
    <!-- <script src="/datetimepicker-master/build/jquery.datetimepicker.full.min.js"></script> -->
@endsection

@section ("jquery")

<script>
    
    $(document).ready( function() {

        var slider = $('#lightslider').lightSlider({
            item: 1,
            mode: "slide",
            enableTouch:false,
            enableDrag:true,
            freeMove:true,
            swipeThreshold: 40,
        });


        var relatedSlider = $('#relatedSlider').lightSlider({
            item:5,
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
                url: "{{route('add.to.cart')}}",
                data: {'id': {{$product->id}}, 'qty': $('#order_qty').val()},
                success: function (result) {
                    //if (isMobile()) {
                        document.location.href = '/cart';
                    // else {
                    //     if ($('.cart-anim').length>0) {
                    //         $('html,body').animate({ scrollTop: 0 }, 'slow'); 
                             //$('.cart-anim').addClass('move-to-cart')
                            
                        //     setTimeout(function(){ window.location.reload(); }, 500);
                        // }
                    //}
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
                    $('.img-panel .price').text('Price: '+$('.mainprice').text());
                    if ($('.p_retail').length > 0)
                        $('.img-panel .retail').text('Retail: '+$('.p_retail').text());
                    else $('.img-panel .retail').hide();
                }
            });
        })
        
        // current gold market / 20 = 2002.70 oz / 20 = 100.13
        // so if it's 10k, devide that to 24k of pure = 10/24 = 0.416 = 41.6%
        // final step 100.13 * 0.416 = 41.65 is per pennyweight or dwt

        //To get the pennyweight price, divide the daily gold price per troy ounce, $400, by 20.
// (1 troy ounce equals 20 dwt). Thus, $400/20 = $20 per dwt. To get the pure gold price for 
//the item, multiply 3 dwt, the weight of the item, times $20.

// To get the grain price, divide $400 by 480. (1 troy ounce equals 480 gr).
// Thus, $400/480 = approximately $0.83 per gr (or 83¢ per gr).
// To get the pure gold price for the item, multiply 3 gr times $0.83.
// Thus, 3 x $0.83 = $2.49.
// To get the 14K gold price for the item, multiply $2.49 by 0.6.
// Thus, $2.49 x 0.6 = approximately $1.49.

        // This arrangement can be altered based on how we want the date's format to appear.
        let currentDate = new Date();

        $('.calendar').pignoseCalendar({
            format: "MM/DD/YYYY",
            init: function (context) {
                $('.selected_date').text('Book on  '+currentDate.toDateString())
                initTime()
                $('#book_date').val(currentDate.toISOString().split("T")[0])
            },
            disabledWeekdays: [0, 5, 6], // SUN (0), SAT (6)
            disabledRanges: [
                ['2000-04-12',moment(currentDate).subtract(1, 'd').toISOString().split("T")[0]]
            ],
            
            select: function(date, context) {
                $('.selected_date').text('Book on '+new Date(moment(date[0]._i)).toDateString());
                $('#book_date').val(date[0]._i);

                if (date[0]._i == currentDate.toJSON().slice(0,10))
                    initTime();
                else initTime(date[0]._i);
	        }
        });
        
        function initTime(param) {
            let i = 0, icount = 0;
            var ran = false;

            $('.time_selection').empty()

            if (param) {
                currentTime = 10;
                param = moment(param+' '+'10:00:00').toDate("dd/mm/yyyy hh:ii:ss");
                var currentDate = param;
            } else { 
                var currentDate = new Date();
            }
            
            
            let j = 0; let minutes = "00 ";
            var options = {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            };

            var ap = "am";
            do {
                
                let rnd = Math.floor(Math.random() * 1000);
                if (param) {
                    j += 30
                    currentTime = moment(currentDate).add(j, 'm').toDate();
                    
                    var timeString = currentTime.toLocaleString('en-US', options)
                    $('<a>').appendTo('.time_selection')
                            .addClass('selected_time') 
                            .attr('id','selected_time'+i)
                            .text(timeString.toLowerCase())
                    if (currentTime.getHours() == 17) {
                        j += 30
                        currentTime = moment(currentDate).add(j, 'm').toDate();
                    
                        var timeString = currentTime.toLocaleString('en-US', options)
                        $('<a>').appendTo('.time_selection')
                                .addClass('selected_time') 
                                .attr('id','selected_time'+(i+rnd))
                                .text(timeString.toLowerCase())
                    }
                    
                } else {
                    j += 30
                    currentTime = moment(currentDate).add(j, 'm').toDate();
                    //currentTime = ((currentTime.getHours()+i) + 24) % 12 || 12
                    if (currentDate.getHours()+i > 11) ap = "pm";

                    if (currentTime.getMinutes() >= 0 && currentTime.getMinutes() < 30) {
                        minutes = "00 "
                    } else if (currentTime.getMinutes() > 30) {
                        minutes = "30 "
                    }

                    $('<a>').appendTo('.time_selection')
                            .addClass('selected_time') 
                            .attr('id','selected_time'+i)
                            .text(((currentTime.getHours()+24) % 12 || 12) +':'+minutes+ap)
                    j += 30
                    
                    currentTime = moment(currentDate).add(j, 'm').toDate();
                    if (currentTime.getHours() < 17) {
                        if (currentTime.getMinutes() >= 0 && currentTime.getMinutes() < 30) {
                            minutes = "00 "
                        } else if (currentTime.getMinutes() > 30) {
                            minutes = "30 "
                        }
                        $('<a>').appendTo('.time_selection')
                                .addClass('selected_time') 
                                .attr('id','selected_time'+(i+rnd))
                                .text(((currentTime.getHours()+24) % 12 || 12) +':'+minutes+ap)
                    }
                }
                
                i += 1;
            } while (currentTime.getHours() < 17)
        }

        $('body').on('click', '.selected_time', function () {
            $('#contact_container').show();
            $('#contactname').focus();
            $('#appointment #date').text($('.selected_date').text() + ' at' + ' ' + $(this).text())
            $('#book_time').val($(this).text());
            $('#calendar_container').hide();
        }) 

        $('#contact_container a').click( function (e) {
            e.preventDefault()
            $('#contact_container').hide();
            $('#calendar_container').show();
        })

        $('.scheduling').click( function(e) {
            e.preventDefault()
            $.fancybox.open({
                src: "#div_appointment",
                type: 'inline',
                beforeShow: function() {
                    $('#contact_container').hide();
                    $('#calendar_container').show();  
                }
            });

            
        })

        $('.offer').click( function () {
            var _this = $(this);
            $('.offer-form')[0].reset();

            $.fancybox.open({
                src: "#price-offer",
                type: 'inline',
                beforeShow: function() {
                    $('.img-panel img').attr('src', $('.image img').attr('src'));
                    $('.img-panel .caption').text($('.title').text());
                    $('.img-panel .price').text('Price: '+$('.mainprice').text());
                    if ($('.p_retail').length > 0)
                        $('.img-panel .retail').text('Retail: '+$('.p_retail').text());
                    else $('.img-panel .retail').hide();
                }
            });
        })

        //$('.inquiry-form')
        Parsley.on('form:submit', function(e) {
            if (e.element.className == 'inquiry-form') {
                $.ajax ( {
                    type: 'post',
                    dataType: 'json',
                    url: $('.inquiry-form').attr('action'),
                    data: {inquiry: $('.inquiry-form').serialize(), _token: "{{csrf_token()}}"},
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
            } else if (e.element.className =='offer-form') {
                $.ajax ( {
                    type: 'post',
                    dataType: 'json',
                    url: $('.offer-form').attr('action'),
                    data: {priceoffer: $('.offer-form').serialize(),_token: "{{csrf_token()}}"},
                    success: function(response) {
                        // if ($.isEmptyObject(response.error)) {
                        if (response.error=='success') {
                            $.fancybox.close();
                            $.fancybox.open({
                                src: "<div><p style='padding: 30px 20px;width: 90%'>Your offer has been submitted successfully. Someone will get back to you as soon as possible.</p></div>",
                                type: 'html',
                            });
                        } else if (response.error=='nomatch') {
                            $.fancybox.close();
                            $.fancybox.open({
                                src: "<div><p style='padding: 30px 20px;width: 90%'>Your offer is too low. Please consider giving us a better offer.</p></div>",
                                type: 'html',
                            });
                        } else if (response.error=='nonumeric') {
                            $.fancybox.close();
                            $.fancybox.open({
                                src: "<div><p style='padding: 30px 20px;width: 90%'>Your offer amount is either empty or non-numeric. Please input a valid number.</p></div>",
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
            } else {
                $.ajax ( {
                    type: 'post',
                    url: "{{route('bookings.store')}}",
                    data: {contact: $('#bookings_form').serialize(),product_id: $('#product_id').val(), _token: "{{csrf_token()}}"},
                    success: function(response) {
                        $.fancybox.close();
                        $.alert(response)   
                    }
                })
            }
            return false;
        });

        
    })
</script>

@endsection