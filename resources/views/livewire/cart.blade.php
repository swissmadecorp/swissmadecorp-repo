<?php $totals=0; ?>
<div> 
    <div wire:ignore.self id="slideover-product-container" class="fixed inset-0 w-full h-full invisible z-60" >
        <div wire:ignore.self id="slideover-product-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div @keydown.escape.prevent="closeAndClearProductFields()" wire:ignore.self id="slideover-product" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll w-96">
            <div class="bg-gray-200 p-3 text-2xl text-gray-500">
                Cart
            </div>
            <div id="slideover-product-child" class="text-gray-900 w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div>
                @if (isset($cartproducts) && count($cartproducts))
                    <div class="total-cart animated fadeInUps">
                        <ul>
                            <li>
                                <div class="total-cart-pro">
                                    @foreach ($cartproducts as $product)
                                    <!-- single-cart -->
                                    <div class="flex gap-4 p-2 relative mb-2">
                                        <a href="/new-unworn-certified-pre-owned-watches/{{$product['slug']}}">
                                            <img src="/{{$product['image']}}" class="w-48 border">
                                        </a>
                                        <div class="bottom-0 right-0 absolute">
                                            <button wire:click.prevent="deleteFromCart({{ $product['id'] }})" data-id="{{ $product['id'] }}" type="button" class="delete inline-flex items-center mb-2 me-2 rounded-lg text-center text-gray-800 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div>
                                            <h6 class="uppercase text-gray-900">
                                                <a href="/new-unworn-certified-pre-owned-watches/{{$product['slug']}}">{{ $product['product_name'] }} </a>
                                            </h6>
                                            <p class="pt-2 text-gray-700">
                                                <span>Condition <strong>:</strong></span> {{ $product['condition'] }}
                                            </p>
                                            <p class="text-gray-700">
                                                <span>Price <strong>:</strong></span>${{ number_format($product['webprice'],2) }}
                                            </p>
                                        </div>
                                    </div>
                                    <?php $totals +=$product['webprice'] ?>
                                    <hr>
                                    @endforeach
                                </div>
                            </li>
                            <li>
                                <h4 class="text-center text-lg uppercase p-2">
                                    Total = <span class="text-cyan-500 font-bold">${{ number_format($totals,2) }}</span>
                                </h4>
                                <hr>
                            </li>
                            <li>
                                <h4 class="text-center text-lg uppercase py-2">
                                    <a href="/checkout" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">View cart</a>
                                </h4>
                            </li>
                        </ul>
                    </div>
                    @endif
            </div>
        </div>
    </div>
      
    @script
        <script> 
            $(function() {
             
                function Slider() {
                    $('body').toggleClass('overflow-hidden')
                    $('#slideover-product-container').toggleClass('invisible')
                    $('#slideover-product-bg').toggleClass('opacity-0')
                    $('#slideover-product-bg').toggleClass('opacity-20')
                    $('#slideover-product').toggleClass('translate-x-full')
                    if (!$('#slideover-product-container').hasClass('invisible')) {
                        setTimeout(() => {
                            $('#title').focus();
                        }, "400");

                    }
                }
      
                $wire.on('dispatched-message', msg => {
                    if (msg[0].msg == 'deleteproduct') {
                        
                        if ($wire.$get('countCart') == 0) {
                            Slider()
                        }
                        // $wire.$dispatch("delete-from-cart-message", {'id': msg[0].id })
                    } else if (msg[0].msg == 'createproduct') {
                        if ($wire.$get('countCart') > 0) {
                            Slider()
                        }
                    }
                })

                $(document).on('click', '#cart', function() {  
                    Slider()
                })

                $('#slideover-product-child').click(function() {
                    Slider()
                })
            })
        </script>
    @endscript
    
</div>