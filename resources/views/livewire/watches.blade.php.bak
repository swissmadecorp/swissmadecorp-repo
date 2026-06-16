<div>
    {!! $categoryimageHTML !!}

    @section ("canonicallink")
        <link rel="canonical" href="{{config('app.url').'/watch-products' }}" />
    @endsection

    @if ($discount)
        <?php $productDiscount=unserialize($discount->product); ?>
        @include ('announcement',['discount'=>$discount])
    @else
        <?php $productDiscount = array() ?>
    @endif

    @section('title', 'Brand new, pre-owned, luxury, casual, and dress watches for men and women')

    @if ($isNewArrivalPage == 'watch-products')
        @push('meta-title')
            <meta name="title" content="Brand new, pre-owned, luxury, casual, and dress watches for men and women.">
        @endpush
        <!-- Breadcrumb -->
        <nav id="breadcrumb" class="flex px-5 py-3 text-gray-700 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="/watch-products" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
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
                            <svg class="rtl:rotate-180 block w-3 h-3 mx-1 text-gray-400 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                            </svg>
                            <a href="#" wire:click.prevent="clear{{$key}}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2 dark:text-gray-400 dark:hover:text-white">{{$breadcrumb}}</a>
                        </div>
                    </li>
                    @endif
                @endforeach

                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180  w-3 h-3 mx-1 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">{{$last}}</span>
                    </div>
                </li>
                <?php } ?>
            </ol>
        </nav>
    @else
        @push('meta-title')
            <meta name="description" content="New arrivals for new and pre-owned luxury watches">
        @endpush
    @endif

    @if ($products->count())
        <div id="product-items"> <!-- class="row" id="product-items"> -->
            <div class=" mx-auto p-4 grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($products as $product)
                    
                    @if (strpos(url()->current(),'chrono24')>0)
                        <?php $path = 'chrono24/watches/'.$product->slug ?>    
                    @else
                        <?php $path = 'product-details/'.$product->slug ?>
                        
                    @endif
                    <a href="#" wire:key="{{$product->id}}" wire:click.prevent="goToProductDetails('{{$product->slug}}')" class="hover:text-red-600 inline-block relative group flex flex-col justify-between p-2 rounded-lg equal-height">
                        <div>
                            @if ($product->p_status != 0)
                            <div class="absolute bg-red-200 font-bold p-0.5 right-0 rounded-lg text-red-700">{{Status()->get($product->p_status) }}</div>
                            @endif

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
                            <div class="flex font-medium items-center gap-1 justify-center mb-4 text-gray-500 md:text-lg">
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
                            <button class="bg-red-800 transition-colors duration-200 ease-in-out hover:bg-red-600 leading-5 p-2 rounded-md text-white transition group-hover:bg-red-500 w-full">
                            View Details >
                            </button>
                        </div>
                    </a>
                @endforeach 

            </div>
        </div> 
        
        {{ $products->onEachSide(1)->links('livewire.pagination') }}
        
     @else
        <div class="flex flex-col flex-grow justify-center text-center p-[150px] text-2xl">0 matches found</div>
    @endif


    <script> 
        $(function() {
            window.addEventListener("popstate", function (event) { window.location.reload(); });

        })
    </script>


</div>