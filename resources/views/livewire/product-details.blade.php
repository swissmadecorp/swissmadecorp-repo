<div>

@section ('header')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="/js/pignose-calendar/pignose.calendar.full.min.js"></script>
<link href="/js/pignose-calendar/pignose.calendar.min.css" rel="stylesheet">
@endsection

@if (isset($product))
    <?php $newprice = 0; ?>
    @if (isset($product->p_newprice))
        <?php $newprice = $product->p_newprice; ?>
    @endif

    <?php

        $webprice = ceil($newprice+($newprice*CCMargin()));$new_webprice=0;
        $productDiscount = array();
        if ($discount) {
            $webprice = ceil($webprice - ($webprice * ($discount->amount/100)));
            $productDiscount=unserialize($discount->product);
        }
    ?>

    @if (!isset( $product->slug))
        <?php \Log::debug($product) ?>
    @endif


    @section ("canonicallink")
        <link rel="canonical" href="{{config('app.url').'/product-details/'. $product->slug }}" />
    @endsection

    @if ($product->p_metatitle)
        @push('meta-title')
            <meta name="title" content="{{$product->p_metatitle}}">
        @endpush
    @endif

    @section('title', $product->title)

    @if ($product->p_metadescription)
        @push('meta-description')
            <meta name="description" content="{{$product->p_metadescription}}">
        @endpush
    @else
        @push('meta-description')
            <meta name="description" content="Detailed information of {{$product->title . ' for only $' . number_format($webprice,2) }}">
        @endpush
    @endif

    @if ($product->p_keywords)
        @push('meta-keywords')
            <meta name="keywords" content="{{$product->p_keywords}}">
        @endpush
    @else
        @push('meta-keywords')
            <meta name="keywords" content="{{ Conditions()->get($product->p_condition).','.str_replace(' ',',',$product->title) }}">
        @endpush
    @endif


    <div class="bg-gray-50">
        <?php $imageMain=$product->images()->first();$isPreviousNoImage=false; ?>
        <!-- Breadcrumb -->
        <nav id="breadcrumb" class="flex px-5 py-3 text-gray-700 border rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700" aria-label="Breadcrumb">
            <ol class="md:inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="/watch-products" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/6000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                        </svg>
                        Watches
                    </a>
                </li>
                <?php
                    $last = end($breadcrumbs);
                    if ($last) {
                ?>
                @foreach ($breadcrumbs as $key => $breadcrumb )
                @if ($breadcrumb != $last)
                    <li>
                        <div class="flex items-center">
                            <svg class="rtl:rotate-180 block w-3 h-3 mx-1 text-gray-400 " aria-hidden="true" xmlns="http://www.w3.org/6000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <a href="#" wire:click="setBread({{$key}})" class="breadcrumb ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2 dark:text-gray-400 dark:hover:text-white">{{$breadcrumb}}</a>
                        </div>
                    </li>
                    @endif
                @endforeach

                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180  w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/6000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">{{$last}}</span>
                    </div>
                </li>
                <?php } ?>
            </ol>
        </nav>

        <?php
            $condition = $product->p_condition== 1 || $product->p_condition == 2 ? 'New / Unworn' : Conditions()->get($product->p_condition);
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
        <div class="max-w-7xl mx-auto bg-white pl-6 pr-6 pb-4">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Section: Images -->
                <div class="relative flex flex-col w-full lg:w-1/2 gap-4">

                    <style>
                        .no-tap-highlight { -webkit-tap-highlight-color: transparent; }
                        /* Hide scrollbar for Chrome, Safari and Opera */
                        .no-scrollbar::-webkit-scrollbar { display: none; }
                        /* Hide scrollbar for IE, Edge and Firefox */
                        .no-scrollbar { -ms-overflow-style: none;  scrollbar-width: none; }
                    </style>

                    <div class="relative w-full h-[340px] group overflow-hidden border border-gray-100">

                        <div id="mainCarouselTrack" class="flex h-full w-full transition-transform duration-500 ease-in-out cursor-zoom-in js-open-modal js-swipeable">
                            @if($product->images->count() > 0)
                                @foreach ($product->images as $index => $image)
                                    <div class="w-full flex-shrink-0 h-full flex items-center justify-center p-2">
                                        <img src="/images/{{ $image->location }}"
                                            class="h-full w-full object-contain pointer-events-none select-none"
                                            alt="Product Image {{ $index + 1 }}"
                                            {{ $index > 0 ? 'loading=lazy' : '' }}>
                                    </div>
                                @endforeach
                            @else
                                <div class="w-full flex-shrink-0 h-full flex items-center justify-center p-2">
                                    <img src="/images/no-image.jpg" class="h-full w-full object-contain pointer-events-none select-none">
                                </div>
                            @endif
                        </div>

                        @if ($product->images->count() > 1)
                            <button class="js-change-image absolute top-1/2 left-4 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-md transition-all duration-300 z-10 opacity-100 md:opacity-0 md:group-hover:opacity-100 md:hover:scale-110 no-tap-highlight" data-direction="-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <button class="js-change-image absolute top-1/2 right-4 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-md transition-all duration-300 z-10 opacity-100 md:opacity-0 md:group-hover:opacity-100 md:hover:scale-110 no-tap-highlight" data-direction="1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        @endif

                        @if($product->images->count() > 0)
                        <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur px-3 py-1.5 rounded-full shadow-sm text-xs font-medium text-gray-600 pointer-events-none flex items-center gap-2 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                            </svg>
                            Click to Expand
                        </div>
                        @endif
                    </div>

                    @if ($product->images->count() > 1)
                    <div class="relative px-8 lg:px-10 h-[75px]">
                        <button class="js-scroll-thumbs absolute left-0 top-0 bottom-0 w-8 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-l-lg flex items-center justify-center transition-colors z-10 no-tap-highlight h-full" data-direction="-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        <div id="thumbnailContainer" class="flex gap-2 h-full overflow-x-auto scroll-smooth no-scrollbar">
                            @foreach ($product->images as $index => $image)
                                <div class="js-thumb-item relative flex-shrink-0 w-[75px] h-full cursor-pointer rounded-lg overflow-hidden border-2 transition-all duration-300 hover:opacity-100 {{ $index === 0 ? 'border-gray-600 opacity-100 ring-2 ring-blue-100' : 'border-transparent opacity-60' }}"
                                    data-index="{{ $index }}">
                                    <img src="/images/thumbs/{{ $image->location }}" class="w-full h-full object-cover pointer-events-none">
                                </div>
                            @endforeach
                        </div>

                        <button class="js-scroll-thumbs absolute right-0 top-0 bottom-0 w-8 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-r-lg flex items-center justify-center transition-colors z-10 no-tap-highlight h-full" data-direction="1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                    @endif

                    @if ($product->images->count() > 0)
                    <div id="modal" class="fixed inset-0 z-50 invisible opacity-0 transition-all duration-300 ease-out">
                        <div class="absolute inset-0 bg-black/80 backdrop-blur-md js-close-modal"></div>

                        <button class="js-close-modal absolute top-6 right-6 z-[70] text-white/70 hover:text-white transition-colors p-2 group no-tap-highlight">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 group-hover:scale-110 transition-transform">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-xs font-light mt-1">CLOSE</span>
                            </div>
                        </button>

                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">

                            <div id="modalWrapper" class="pointer-events-auto relative w-full md:w-[80vw] h-[60vh] md:h-[80vh] overflow-hidden transform scale-95 transition-all duration-300 ease-out">
                                <div id="modalCarouselTrack" class="flex h-full w-full transition-transform duration-500 ease-in-out items-center js-swipeable">
                                    @foreach ($product->images as $image)
                                        <div class="w-full flex-shrink-0 h-full flex items-center justify-center p-2 md:p-4">
                                            <img src="/images/{{ $image->location }}" class="max-w-full max-h-full object-contain drop-shadow-2xl select-none rounded-2xl" loading="lazy">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if ($product->images->count() > 1)
                            <button class="js-change-image pointer-events-auto absolute left-2 md:left-8 text-white/60 hover:text-white transition-transform hover:scale-110 p-4 z-[60] no-tap-highlight" data-direction="-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 drop-shadow-md">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <button class="js-change-image pointer-events-auto absolute right-2 md:right-8 text-white/60 hover:text-white transition-transform hover:scale-110 p-4 z-[60] no-tap-highlight" data-direction="1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 drop-shadow-md">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Right Section: Product warranty -->
                <div class="w-full lg:w-2/3 flex flex-col gap-4">
                    <!-- Product Title and Rating -->
                    <div>
                        <h1 class="text-lg font-bold">{{$product->title}}</h1>
                        <!-- <div class="flex items-center mt-2">
                            <span class="text-yellow-500">★★★★☆</span>
                        </div> -->
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400" cellpadding="3">
                            <tr>
                                <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Stock No:</th>
                                <td class="px-3 py-2"><span class="font-bold">{{ $product->id  }}</span></td>
                            </tr>
                            <tr>
                                <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Availability:</th>
                                <td class="px-3 py-2"><span style="color: {{ $color  }}">{{ $status  }}</span></td>
                            </tr>
                            <tr>
                                <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Condition:</th>
                                <td class="px-3 py-2"><div class="condition">{{ $condition }}</div></td>
                            </tr>

                            @if (isset($lpath) && $lpath=="withmarkups")

                                <tr>
                                <?php $webprice = $product->p_price3P ?>
                                <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Web Price:</th>
                                @if ($product->p_price3P>0)
                                <td class="px-3 py-2"><span class="p_price">${{ number_format($webprice,2) }}</span></td>
                                @else
                                <td class="px-3 py-2"><span class="p_price">Call For Price</span></td>
                                @endif
                                </tr>
                            @else
                                <tr>
                                <?php $loggedIn = false ?>
                                @if (Auth::guard('customer')->check())
                                    <?php $loggedIn = true ?>
                                    @if ($newprice>0)
                                    <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Dealer Price:</th>
                                    <td class="px-3 py-2 flex gap-2">
                                        <span class="p_price">${{ number_format($newprice,2) }}</span>
                                        <span style="font-weight: 600">
                                            @if ($product->percent>0 && $product->percent-(CCMargin()*100) > 0)
                                                ({{ number_format($product->percent-(CCMargin()*100),0) }}% Off)
                                            @endif
                                        </span>
                                    </td>
                                    @else
                                    <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Dealer Price:</th>
                                    <td class="px-3 py-2"><span class="p_price">Call For Price</span></td>
                                    @endif
                                @else
                                    @if ($discount && $discount->action == 4)
                                        <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Sale Price</th>
                                    @elseif ($discount && $discount->action == 5 && !empty($productDiscount) && in_array($product->id, $productDiscount))
                                        <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800"> class="product_sale">Sale Price</th>
                                    @else
                                        <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Price</th>
                                    @endif
                                    <td class="px-3 py-2 flex gap-2">
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
                                <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Retail Price:</th>
                                <td class="px-3 py-2">
                                    @if ($product->p_retail>0)
                                    <span class="p_retail p_price">${{ number_format($product->p_retail,2) }}</span>
                                    @else
                                    <span class="p_retail">Not Available</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($product->p_qty > 1)
                            <tr>
                                <th scope="col" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap bg-gray-100 dark:text-white dark:bg-gray-800">Qty:</th>
                                <td class="px-3 py-2">
                                    <input type="text" name="order_qty" class="form-control" id="order_qty" value="1" />

                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-900 bg-gray-100 dark:text-white dark:bg-gray-800 w-1/2">
                                    <span>Want to see this watch in person? </span>
                                </td>
                                <td class="py-2 pl-2"><livewire:calendar :productId="$product->id" /></td>
                            </tr>

                            <?php $wire_price = $newprice; ?>
                            <?php  if ($wire_price > 1 && $status == 'In Stock' && $product->wire_discount) { ?>
                            <tr>
                                <td class="py-2" colspan="2" >Save an additional <b style="color:red">$<?= $product->web_price-$wire_price ?></b> when you pay with <a style="color: blue" href="\wire-transfer-guide">Bank Wire</a> during checkout. You pay <b style="color:red">$<?= number_format($wire_price,2) ?></b>.</td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>

                    <div class="sm:flex sm:gap-2 gap-6 grid justify-between">
                        <?php $location = "https://web.whatsapp.com/send?phone=17186147678&text=Hello, I am on your website and I am interested in " . str_replace("'",'',$product->title) . " (".$product->id.")" ?>

                        <?php
                            // $p_status = 0;
                            // if (isset($productStatus))
                            //     if (array_key_exists($product->id,$productStatus))
                            //         $p_status = $productStatus[$product->id];
                        ?>

                        @if ($status=="In Stock" && $product->p_price3P>0 && $p_status == 0)
                        <div class="flex gap-2">
                            <button  id="addtocart" wire:click.prevent="AddToCart({{$product->id}})" class="bg-black text-white px-3 py-3 rounded-lg ">Add to Cart</button>
                            <button wire:click.prevent="BuyNow({{$product->id}})" class="bg-red-700 text-white px-3 py-3 rounded-lg ">Buy now</button>
                        </div>
                        @endif
                        <div class="flex gap-2">
                            <button class="whatsapp bg-green-500 rounded-lg text-white text-sm md:text-lg px-2" aria-label="Contact us via whatsapp" onclick='window.open("<?=$location ?>")' autocomplete="off"><i class="fab fa-whatsapp"></i></button>

                            <button data-modal-target="inquiry" data-modal-toggle="inquiry" class="inquire bg-gray-300 rounded-lg text-gray-800 px-2 ">Inquire</button>
                            <button data-modal-target="offer" data-modal-toggle="offer"
                                    class="offer bg-gray-300 rounded-lg text-gray-800 px-2
                                    <?= ($status == "In Stock" && $product->p_price3P > 0 && $p_status == 0) ? '' : ' hidden'?>
                                    ">Make Offer</button>

                            <livewire:offer :product="$product" />
                            <livewire:inquire :product="$product" />

                        </div>


                    </div>
                    <!-- Features and Add to Cart -->


                </div>
            </div>

            <!-- Description, Return Policy, and warranty -->
            <div x-data="{ activeTab: 'description' }" class="mt-8">
                <div class="border-b">
                    <nav class="-mb-px flex space-x-1" aria-label="Tabs">
                        <a href="#"
                        :class="{ 'bg-black text-white': activeTab === 'description', 'text-gray-700 hover:bg-gray-300': activeTab !== 'description' }"
                        class="transition-colors duration-300 whitespace-nowrap py-2 px-1 border-b-2 border-transparent font-medium text-sm rounded-t-md text-center w-32"
                        @click.prevent="activeTab = 'description'">
                            Description
                        </a>

                        <a href="#"
                        :class="{ 'bg-black text-white': activeTab === 'return_policy', 'text-gray-700 hover:bg-gray-300': activeTab !== 'return_policy' }"
                        class="transition-colors duration-300 whitespace-nowrap py-2 px-1 border-b-2 border-transparent font-medium text-sm rounded-t-md text-center w-32"
                        @click.prevent="activeTab = 'return_policy'">
                        Return Policy
                        </a>

                        <a href="#"
                        :class="{ 'bg-black text-white': activeTab === 'warranty', 'text-gray-700 hover:bg-gray-300': activeTab !== 'warranty' }"
                        class="transition-colors duration-300 whitespace-nowrap py-2 px-1 border-b-2 border-transparent font-medium text-sm rounded-t-md text-center w-32"
                        @click.prevent="activeTab = 'warranty'">
                            Warranty
                        </a>
                    </nav>
                </div>

                <!-- Content for Tabs -->
                <div class="mt-4">
                    <div x-show="activeTab === 'description'" class="text-gray-600">
                        <div class="attributes">
                            <ul>
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
                        @if ($product->p_longdescription)
                            <p class="pt-4">{!! $product->p_longdescription !!}</p>
                        @endif
                        @if ($product->p_smalldescription)
                            <p class="pt-4"><em>{!! $product->p_smalldescription !!}</em></p>
                        @endif
                    </div>

                    <div x-show="activeTab === 'return_policy'" class="text-gray-600">
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

                    <div x-show="activeTab === 'warranty'" class="text-gray-600">
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
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const totalImages = {{ $product->images->count() }};
            let currentIndex = 0;

            // Variables for Swipe Logic
            let touchStartX = 0;
            let touchEndX = 0;
            let isSwiping = false; // Flag to prevent click event when swiping

            // Cache jQuery Objects
            const $mainTrack = $('#mainCarouselTrack');
            const $modalTrack = $('#modalCarouselTrack');
            const $modal = $('#modal');
            const $modalWrapper = $('#modalWrapper');
            const $thumbsContainer = $('#thumbnailContainer');

            // --- Core Display Logic ---
            function updateDisplay() {
                if (totalImages === 0) return;

                // 1. Slide Tracks
                const translateVal = `translateX(-${currentIndex * 100}%)`;
                $mainTrack.css('transform', translateVal);
                $modalTrack.css('transform', translateVal);

                // 2. Update Thumbnails
                $('.js-thumb-item').each(function(index) {
                    const $el = $(this);
                    if (index === currentIndex) {
                        $el.removeClass('border-transparent opacity-60')
                        .addClass('border-gray-600 opacity-100 ring-2 ring-gray-100');
                        $el[0].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    } else {
                        $el.addClass('border-transparent opacity-60')
                        .removeClass('border-gray-600 opacity-100 ring-2 ring-gray-100');
                    }
                });
            }

            function changeImage(dir) {
                currentIndex += dir;
                if (currentIndex < 0) currentIndex = totalImages - 1;
                if (currentIndex >= totalImages) currentIndex = 0;
                updateDisplay();
            }

            // --- Event Handlers ---

            // 1. Click Arrows
            $(document).on('click', '.js-change-image', function(e) {
                e.stopPropagation();
                changeImage(parseInt($(this).data('direction')));
            });

            // 2. Click Thumbnails
            $(document).on('click', '.js-thumb-item', function() {
                const index = $(this).data('index');
                if (index !== currentIndex) {
                    currentIndex = index;
                    updateDisplay();
                }
            });

            // 3. Open Modal (Click on Main Image)
            $(document).on('click', '.js-open-modal', function(e) {
                // If the user was actually swiping, do NOT open the modal
                if (isSwiping) {
                    isSwiping = false; // Reset flag
                    return;
                }

                if (totalImages === 0) return;
                $modal.removeClass('invisible opacity-0').addClass('visible opacity-100');
                setTimeout(function() {
                    $modalWrapper.removeClass('scale-95').addClass('scale-100');
                }, 10);
            });

            // 4. Close Modal
            $(document).on('click', '.js-close-modal', function() {
                $modalWrapper.removeClass('scale-100').addClass('scale-95');
                $modal.removeClass('visible opacity-100').addClass('invisible opacity-0');
            });

            // 5. Scroll Thumbnail Bar
            $(document).on('click', '.js-scroll-thumbs', function() {
                const dir = parseInt($(this).data('direction'));
                const currentScroll = $thumbsContainer.scrollLeft();
                $thumbsContainer.animate({ scrollLeft: currentScroll + (dir * 150) }, 300);
            });

            // --- SWIPE LOGIC (Touch Events) ---

            // Touch Start
            $('.js-swipeable').on('touchstart', function(e) {
                // Get the original touch event to access coordinates
                touchStartX = e.originalEvent.changedTouches[0].screenX;
                isSwiping = false; // Reset swiping status
            });

            // Touch Move (Detect if user is actually moving their finger)
            $('.js-swipeable').on('touchmove', function(e) {
                // If movement is detected, mark as swiping so we don't trigger "Open Modal" click
                isSwiping = true;
            });

            // Touch End
            $('.js-swipeable').on('touchend', function(e) {
                touchEndX = e.originalEvent.changedTouches[0].screenX;
                handleSwipeGesture();
            });

            function handleSwipeGesture() {
                // Calculate distance moved
                const swipeDistance = touchEndX - touchStartX;
                const threshold = 50; // Minimum distance (px) to count as a swipe

                if (Math.abs(swipeDistance) > threshold) {
                    // If swiped left (negative distance), go next
                    if (swipeDistance < 0) {
                        changeImage(1);
                    }
                    // If swiped right (positive distance), go prev
                    else {
                        changeImage(-1);
                    }
                    isSwiping = true; // Confirm it was a swipe
                } else {
                    // If distance was too small, it was just a tap/click
                    isSwiping = false;
                }
            }

            // --- Keyboard Nav ---
            $(document).on('keydown', function(e) {
                if ($modal.hasClass('visible')) {
                    if (e.key === 'Escape') $('.js-close-modal').first().trigger('click');
                    if (e.key === 'ArrowLeft') changeImage(-1);
                    if (e.key === 'ArrowRight') changeImage(1);
                }
            });
        });
    </script>
@else
<div class="flex items-center justify-center h-screen">
    <div class="text-center">
        <h1 class="text-2xl font-bold mb-4">Product Not Found</h1>
        <p class="text-gray-600">The product you are looking for does not exist or has been removed.</p>
        <a href="/" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Go to Home</a>
    </div>
@endif
</div>