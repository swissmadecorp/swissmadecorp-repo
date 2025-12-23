@extends('layouts.default-new')

@section('title', 'Brand new, pre-owned, luxury, casual, and dress watches for men and women')

@section ('content')

    <img src="/assets/new_arrivals.jpg" style="width: 100%" alt="New Watch Arrivals">
    
    <div class="mx-auto pt-10 flex justify-center">
        <div class="flex md:flex-wrap justify-center space-x-8 md:space-x-10 p-6">
            <a href="/watch-products" class="hover:bg-red-800 uppercase flex items-center justify-center bg-black text-white py-4 px-6 text-center rounded text-sm sm:w-40 sm:text-[13px] md:w-52 md:text-lg lg:w-56 lg:text-xl transition-colors duration-300 ease-in-out">
                Shop all brands
            </a>
            <a href="sell-your-watches" class="hover:bg-red-800 uppercase flex items-center justify-center bg-black text-white py-4 px-6 text-center rounded text-sm sm:w-40 sm:text-[13px] md:w-52 md:text-lg lg:w-56 lg:text-xl transition-colors duration-300 ease-in-out">
                Sell your Watch
            </a>
        </div>
  </div>

    <div class="flex items-center justify-center mt-10 mb-2">
        <div class="flex items-center w-full">
            <div class="bg-black grow-[2] h-10 h-[0.1rem]"></div>
            <div class="grow-[0] pl-[1rem] pr-[1rem] md:text-[2rem] font-bold uppercase ">New Arrival</div>
            <div class="bg-black grow-[2] h-10 h-[0.1rem] "></div>
        </div>
    </div>

    @if ($discount)
        <?php $productDiscount=unserialize($discount->product); ?>
        @include ('announcement',['discount'=>$discount])
    @else
        <?php $productDiscount = array() ?>
    @endif

    <div id="product-items"> <!-- class="row" id="product-items"> -->
        @if (!$products->isEmpty())
        <div class=" mx-auto p-4 grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($products as $product)
            @if (strpos(url()->current(),'chrono24')>0)
                <?php $path = 'chrono24/watches/'.$product->slug ?>    
            @else
                <?php $path = 'product-details/'.$product->slug ?>
                
            @endif
            <a href="{{$path}}" class=" hover:text-red-600 inline-block relative group flex flex-col justify-between p-2 rounded-lg equal-height">
            <div>
                @if (@count($product->images))
                    <?php $image = $product->images->first() ?>
                    @if (!file_exists(base_path(). '/public/images/thumbs/' . $image->location) || strpos($image->location,'snapshot') > 0)
                    <img src="/images/no-image.jpg" alt="">
                    @else
                    <img title="{{ $product->title }}" alt="{{ $product->title }}" src="{{ URL::to('/images/thumbs') .  '/' . $image->location }}" alt="">
                    @endif
                @else
                    <img src="/images/no-image.jpg" alt="">
                @endif

                <div class="caption text-center truncate-3-lines md:overflow-visible md:line-clamp-none h-16">
                    <?php if (isset($product->categories->category_name)) { ?>
                        {{Conditions()->get($product->p_condition). ' '. $product->title}}
                    <?php } else { ?>
                        {{Conditions()->get($product->p_condition). ' '. $product->p_model . ' ' . $product->p_reference}}
                    <?php } ?>
                </div>

            </div>
            <div class="mt-4">
                <div class="flex font-medium justify-center items-center mb-4 text-gray-500 md:text-lg relative">
                    @if ($product->p_newprice>0)
                        @if (Auth::guard('customer')->check())
                            <span class="price">${{ number_format($product->p_newprice,2) }}</span>
                        @else
                            @include ('price',['id'=>$product->id,'discount'=>$discount,'productDiscount'=>$productDiscount,'class'=>'price'])
                        @endif
                    @else
                        <span class="price" style="color:red">Call Us</span>
                    @endif
                </div>
                <span class="block text-center bg-red-800 transition-colors duration-200 ease-in-out hover:bg-red-600 leading-5 p-2 rounded-md text-white transition group-hover:bg-red-500 w-full">
                View Details >
                    </span>
            </div>
        </a>
        @endforeach 
    </div>
        @else
            <div style="text-align:center">No products found in this category</div>
        @endif
    </div> 

    <div class="flex justify-center mt-16">
        <a href="/new-arrival" class="bg-gray-900 border-b-2 dark:hover:text-gray-300 hover:border-gray-300 hover:bg-red-800 inline-block p-4 rounded-lg text-2xl text-white transition-colors duration-300 ease-in-out uppercase">Shop all new arravals</a>
    </div>

    <div class="flex items-center justify-center mt-10">
        <div class="flex items-center w-full">
            <div class="bg-black grow-[2] h-10 h-[0.1rem]"></div>
            <div class="grow-[0] pl-[1rem] pr-[1rem] md:text-[2rem] font-bold uppercase">How Swissmade is different</div>
            <div class="bg-black grow-[2] h-10 h-[0.1rem] "></div>
        </div>
    </div>

    <div class="p-4">
        <p class="mb-2">At SwissMadeCorp, we are devoted to the artistry of exquisite Swiss timepieces and the art of exceptional customer service.</p>
        <p class="mb-2">Distinguished from other online purveyors of both new and pre-owned watches, our entire collection is showcased in real-time inventory, housed within our New York City showroom, and meticulously verified for authenticity.</p>
        <p class="mb-2">Our commitment to our clientele is unwavering. Our knowledgeable sales team offers bespoke guidance and expert education, ensuring that each customer departs with a timepiece that perfectly aligns with their taste, budget, and occasion.</p>
    </div>

    <div class="dark:text-gray-400 md:gap-24 gap-4 flex font-medium md:gap-24 justify-center p-6 text-center text-gray-400 uppercase">
        <div class="flex flex-col h-full items-center justify-center">
            <svg class="w-16 md:w-24" fill="#000000" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier">
            <path d="M 0 6 L 0 8 L 19 8 L 19 23 L 12.84375 23 C 12.398438 21.28125 10.851563 20 9 20 C 7.148438 20 5.601563 21.28125 5.15625 23 L 4 23 L 4 18 L 2 18 L 2 25 L 5.15625 25 C 5.601563 26.71875 7.148438 28 9 28 C 10.851563 28 12.398438 26.71875 12.84375 25 L 21.15625 25 C 21.601563 26.71875 23.148438 28 25 28 C 26.851563 28 28.398438 26.71875 28.84375 25 L 32 25 L 32 16.84375 L 31.9375 16.6875 L 29.9375 10.6875 L 29.71875 10 L 21 10 L 21 6 Z M 1 10 L 1 12 L 10 12 L 10 10 Z M 21 12 L 28.28125 12 L 30 17.125 L 30 23 L 28.84375 23 C 28.398438 21.28125 26.851563 20 25 20 C 23.148438 20 21.601563 21.28125 21.15625 23 L 21 23 Z M 2 14 L 2 16 L 8 16 L 8 14 Z M 9 22 C 10.117188 22 11 22.882813 11 24 C 11 25.117188 10.117188 26 9 26 C 7.882813 26 7 25.117188 7 24 C 7 22.882813 7.882813 22 9 22 Z M 25 22 C 26.117188 22 27 22.882813 27 24 C 27 25.117188 26.117188 26 25 26 C 23.882813 26 23 25.117188 23 24 C 23 22.882813 23.882813 22 25 22 Z"></path></g></svg>
            Same Day Shipping
        </div>
        <div class="flex flex-col h-full items-center justify-center">
            <svg class="w-16 md:w-24" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                viewBox="0 0 25.143 25.143" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> 
                <path style="fill:#030104;" d="M18.313,21.852c0.188,0.03,0.38-0.016,0.534-0.129c0.152-0.11,0.258-0.28,0.286-0.469l0.169-1.123 c0.034-0.226,0.175-0.418,0.379-0.521l1.017-0.508c0.172-0.087,0.301-0.236,0.359-0.417c0.06-0.183,0.041-0.379-0.045-0.549 l-0.524-1.013c-0.103-0.198-0.103-0.439,0-0.639l0.524-1.012c0.086-0.17,0.104-0.366,0.045-0.549 c-0.059-0.182-0.188-0.33-0.359-0.417l-1.017-0.508c-0.204-0.103-0.345-0.295-0.379-0.521l-0.169-1.123 c-0.028-0.189-0.134-0.358-0.286-0.468c-0.154-0.114-0.346-0.16-0.534-0.129l-1.125,0.188c-0.223,0.037-0.449-0.037-0.609-0.198 l-0.799-0.812c-0.135-0.135-0.316-0.211-0.508-0.211c-0.189,0-0.374,0.077-0.508,0.211l-0.799,0.812 c-0.159,0.162-0.386,0.235-0.609,0.198l-1.123-0.185c-0.188-0.031-0.38,0.015-0.535,0.129c-0.153,0.11-0.258,0.279-0.286,0.468 l-0.168,1.123c-0.035,0.226-0.175,0.418-0.379,0.521l-1.018,0.508c-0.169,0.087-0.3,0.235-0.358,0.417 c-0.059,0.183-0.042,0.379,0.044,0.549l0.524,1.012c0.104,0.199,0.104,0.44,0,0.639l-0.524,1.013 c-0.086,0.17-0.103,0.366-0.044,0.549c0.059,0.181,0.188,0.33,0.358,0.417l1.018,0.508c0.204,0.103,0.344,0.295,0.379,0.521 l0.168,1.123c0.028,0.188,0.133,0.358,0.286,0.469c0.154,0.113,0.346,0.159,0.535,0.129l1.123-0.185 c0.223-0.039,0.45,0.036,0.609,0.197l0.799,0.81c0.135,0.138,0.318,0.214,0.508,0.214c0.191,0,0.374-0.076,0.508-0.214l0.799-0.81 c0.16-0.161,0.387-0.236,0.609-0.197L18.313,21.852z M15.271,21.032c-2.39,0-4.333-1.943-4.333-4.332s1.944-4.334,4.333-4.334 c2.39,0,4.333,1.946,4.333,4.334S17.662,21.032,15.271,21.032z"></path> <path style="fill:#030104;" d="M15.272,12.991c-2.041,0-3.703,1.66-3.703,3.702s1.663,3.702,3.703,3.702 c2.043,0,3.703-1.66,3.703-3.702S17.315,12.991,15.272,12.991z"></path> <path style="fill:#030104;" d="M21.725,22.663l-2.015-2.016l-0.102,0.68c-0.048,0.313-0.222,0.602-0.479,0.787 c-0.255,0.186-0.574,0.265-0.892,0.213l-1.126-0.184c-0.093-0.006-0.149,0.02-0.19,0.062l-0.192,0.193l2.632,2.631 c0.102,0.102,0.249,0.138,0.385,0.097c0.136-0.043,0.236-0.156,0.264-0.297l0.237-1.277l1.281-0.24 c0.14-0.026,0.253-0.127,0.294-0.264C21.864,22.911,21.826,22.762,21.725,22.663z"></path> <path style="fill:#030104;" d="M13.469,22.138l-1.16,0.189c-0.325,0.05-0.64-0.028-0.896-0.216 c-0.255-0.184-0.429-0.472-0.477-0.787l-0.102-0.677L8.82,22.663c-0.1,0.1-0.137,0.248-0.096,0.384 c0.043,0.137,0.157,0.236,0.295,0.264l1.28,0.239l0.24,1.279c0.026,0.141,0.127,0.254,0.263,0.297 c0.135,0.041,0.283,0.005,0.383-0.096l2.631-2.632l-0.192-0.194C13.582,22.161,13.526,22.138,13.469,22.138z"></path> <path style="fill:#030104;" d="M16.42,4.217H6.985c-0.34,0-0.615,0.275-0.615,0.615C6.369,5.174,6.644,5.448,6.985,5.448h9.435 c0.339,0,0.615-0.274,0.615-0.614C17.035,4.494,16.758,4.217,16.42,4.217z"></path> 
                <path style="fill:#030104;" d="M16.42,7.371H6.985c-0.34,0-0.615,0.275-0.615,0.613C6.369,8.324,6.644,8.6,6.985,8.6h9.435 c0.339,0,0.615-0.274,0.615-0.615C17.035,7.646,16.758,7.371,16.42,7.371z"></path>
                <path style="fill:#030104;" d="M9.872,20.216l-0.465-0.232c-0.119-0.06-0.227-0.137-0.327-0.223H5.108 c-0.272,0-0.493-0.222-0.493-0.492V1.804c0-0.273,0.221-0.494,0.493-0.494h13.218c0.271,0,0.493,0.221,0.493,0.494v9.007 c0.22,0.052,0.43,0.148,0.614,0.285c0.36,0.257,0.609,0.668,0.676,1.116l0.02-10.407C20.131,0.81,19.321,0,18.326,0H5.108 C4.113,0,3.303,0.811,3.303,1.805v17.466c0,0.996,0.811,1.806,1.805,1.806h3.905L9.872,20.216z"></path> </g> </g> </g>
            </svg>
            Certified 100% Authentic
        </div>
        <div class="flex flex-col h-full items-center justify-center">
            <svg class="w-16 md:w-24" fill="#000000" viewBox="0 0 52 52" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M26,2c3,0,5.43,3.29,8.09,4.42s6.82.51,8.84,2.65,1.51,6.07,2.65,8.84S50,23,50,26s-3.29,5.43-4.42,8.09-.51,6.82-2.65,8.84-6.07,1.53-8.84,2.65S29,50,26,50s-5.43-3.29-8.09-4.42-6.82-.51-8.84-2.65-1.53-6.07-2.65-8.84S2,29,2,26s3.29-5.43,4.42-8.09.51-6.82,2.65-8.84,6.07-1.53,8.84-2.65S23,2,26,2Zm0,7.58A16.42,16.42,0,1,0,42.42,26h0A16.47,16.47,0,0,0,26,9.58Zm7.62,9.15,1.61,1.52a1.25,1.25,0,0,1,0,1.51L25.08,33.07a2.07,2.07,0,0,1-1.61.7,2.23,2.23,0,0,1-1.61-.7L16.37,27.6a1,1,0,0,1-.1-1.42l.1-.11L18,24.56a1.1,1.1,0,0,1,1.54-.07l.07.07,3.89,4,8.59-9.8A1.1,1.1,0,0,1,33.62,18.73Z"></path></g></svg>
            12 Month Warrany
        </div>
        <div class="flex flex-col h-full items-center justify-center">
            <svg class="w-16 md:w-24" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M12.9998 8L6 14L12.9998 21" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M6 14H28.9938C35.8768 14 41.7221 19.6204 41.9904 26.5C42.2739 33.7696 36.2671 40 28.9938 40H11.9984" stroke="#000000" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
            15 Day Returns
        </div>
    </div>
@endsection

