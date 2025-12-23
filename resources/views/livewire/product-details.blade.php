<div>

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
                    <!-- Main Image Container -->
                    <div class="flex justify-center items-center relative h-[340px] overflow-hidden">
                        @if ($product->images->count() > 1)
                        <!-- Left Arrow -->
                        <button id="prevArrow" class="absolute bg-black left-0 opacity-50 p-2 rounded-full text-white">
                            <!-- SVG icon for left arrow -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        @endif

                        <!-- Main Image -->
                        @if ($imageMain == null)
                            <img id="mainImage" src="/images/no-image.jpg" alt="Main Product Image"
                            class="rounded-lg w-auto cursor-pointer transition-opacity duration-500 ease-in-out">
                        @else

                        <img id="mainImage" src="/images/{{$imageMain->location}}" alt="Main Product Image"
                            class="rounded-lg w-auto cursor-pointer transition-opacity duration-500 ease-in-out">
                        @endif

                        @if ($product->images->count() > 1)
                        <!-- Right Arrow -->
                        <button id="nextArrow" class="absolute bg-black opacity-50 p-2 right-0 rounded-full text-white">
                            <!-- SVG icon for right arrow -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        @endif
                        <!-- Magnifying Glass Icon -->
                        <div class="absolute bottom-0 right-0 mb-2 mr-2 bg-white rounded-full p-2">
                            <!-- SVG icon for the magnifying glass -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                            </svg>
                        </div>

                    </div>

                    <!-- Thumbnail Slider -->
                    <div class="relative h-[75px]">
                        <!-- Left Button -->
                        @if ($product->images->count() > 4)
                        <button id="leftSlider"
                            class="hidden lg:block sm:hidden -translate-y-1/2 absolute bg-gray-600 focus:outline-none hover:bg-gray-700 hover:text-white left-0 opacity-50 p-0.5 h-full shadow-md text-gray-900 text-lg text-white top-1/2 transform z-10">
                            ←
                        </button>
                        @endif
                        <div id="lightslider" class="flex gap-1 overflow-x-hidden">
                            @if ($product->images->count() > 1)
                            @foreach ($product->images as $image)
                                <img data-src="/images/{{$image->location}}" src="/images/thumbs/{{$image->location}}" alt="Product Thumbnail" class="cursor-pointer p-1 border rounded-lg h-[75px] hover:shadow thumbnail">
                            @endforeach
                            @endif
                        </div>

                        @if ($product->images->count() > 4)
                        <button id="rightSlider"
                            class="hidden lg:block sm:hidden -translate-y-1/2 absolute bg-gray-600 focus:outline-none hover:bg-gray-700 hover:text-white opacity-67 right-0 p-0.5 h-full shadow-md text-lg text-white top-1/2 transform z-10">
                            →
                        </button>
                        @endif
                    </div>
                    <!-- Modal Structure -->
                    <div id="imageModal" class="fixed inset-0 bg-gray-900/75 flex justify-center items-center hidden z-50">
                        <div class="flex items-center justify-center w-screen h-screen overflow-hidden">
                            <!-- Close Button -->
                            <button id="closeModal" class="bg-gray-100 fixed hover:bg-gray-300 mr-2 mt-2 p-2 right-0 rounded-full shadow-2xl text-gray-600 top-0">
                                <!-- SVG icon for close -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <!-- Modal Image -->
                            <img id="modalImage" src="" alt="Large Product Image" class="max-w-full max-h-full object-contain">
                        </div>
                    </div>
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
                        <?php $location = "https://web.whatsapp.com/send?phone=19176990831&text=Hello, I am on your website and I am interested in " . str_replace("'",'',$product->title) . " (".$product->id.")" ?>

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

        $(document).ready( function() {
            let images = @json($product->images->pluck('location')->map(fn($location) => "/images/$location"));
            let currentIndex = 0;
            const $lightslider = $('#lightslider');
            let isDragging = false;  // To track whether dragging is active
            let startX;              // Starting X position of the mouse
            let scrollLeftStart;     // Initial scrollLeft value when dragging starts

            function updateMainImage() {
                $('#mainImage').removeClass('opacity-100').addClass('opacity-0'); // Fade out

                setTimeout(function() {
                    $('#mainImage').attr('src', images[currentIndex]); // Update the image src
                }, 300); // Wait for the fade-out to finish

                $('#mainImage').on('load', function() {
                    $(this).removeClass('opacity-0').addClass('opacity-100'); // Fade in
                });
            }

            $lightslider.on('mousedown', function (event) {
                isDragging = true;
                startX = event.pageX;                    // Store the mouse's X position
                scrollLeftStart = $lightslider.scrollLeft(); // Store the initial scroll position
                $lightslider.addClass('dragging');       // Optional: Add class for visual feedback
                event.preventDefault();                 // Prevent text selection during drag
            });

            // Mouse move: perform dragging
            $(window).on('mousemove', function (event) {
                if (!isDragging) return; // Only proceed if dragging
                const xDiff = event.pageX - startX; // Calculate the distance moved
                $lightslider.scrollLeft(scrollLeftStart - xDiff); // Adjust scrollLeft value
            });

            // Mouse up: stop dragging
            $(window).on('mouseup', function () {
                if (!isDragging) return;
                isDragging = false;
                $lightslider.removeClass('dragging'); // Optional: Remove class after drag
            });

            // Prevent dragging from affecting click events
            $lightslider.on('click', function (event) {
                if (isDragging) {
                    event.preventDefault(); // Cancel the click if dragging was active
                    isDragging = false; // Reset the dragging state
                }
            });

            // ScrollLeft and ScrollRight for buttons
            function scrollLeft() {
                $lightslider.animate(
                    { scrollLeft: '-=200' },
                    400 // Duration in milliseconds
                );
            }

            function scrollRight() {
                $lightslider.animate(
                    { scrollLeft: '+=200' },
                    400 // Duration in milliseconds
                );
            }

            // Attach to buttons
            $('#leftSlider').on('click', scrollLeft);
            $('#rightSlider').on('click', scrollRight);

            function toggleButtons() {
                const leftButton = document.getElementById('leftButton');
                const rightButton = document.getElementById('rightButton');

                const screenWidth = window.innerWidth;

                if (screenWidth >= 1024 || screenWidth < 640) {
                    // Show buttons for screens >= 1024px or < 640px
                    $('#leftSlider').removeClass('hidden');
                    $('#rightSlider').removeClass('hidden');
                } else {
                    // Hide buttons for screens between 640px and 1024px
                    $('#leftSlider').addClass('hidden');
                    $('#rightSlider').addClass('hidden');
                }
            }

            // Attach the resize event listener
            window.addEventListener('resize', toggleButtons);

            // Initial call to handle page load
            toggleButtons();

            // Initial load with fade-in
            $('#mainImage').on('load', function() {
                $(this).removeClass('opacity-0').addClass('opacity-100');
            }).attr('src', images[0]);

            // Next arrow click
            $('#nextArrow').click(function() {
                currentIndex = (currentIndex + 1) % images.length;
                updateMainImage();
            });

            // Previous arrow click
            $('#prevArrow').click(function() {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateMainImage();
            });

            // Thumbnail click
            $('.thumbnail').click(function() {
                currentIndex = $('.thumbnail').index(this);
                updateMainImage();
            });

            // Main image click to open modal
            $('#mainImage').click(function() {
                let imageUrl = $(this).attr('src');
                $('#modalImage').attr('src', imageUrl);
                $('#imageModal').removeClass('hidden');
            });

            // Close modal
            $('#closeModal, #imageModal').click(function() {
                $('#imageModal').addClass('hidden');
            });

            // Prevent closing modal when clicking on the image itself
            $('#modalImage').click(function(event) {
                event.stopPropagation();
            });
        })
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