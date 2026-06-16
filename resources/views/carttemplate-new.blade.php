
<table class="table cart w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
    <tr >
        <th class="py-3 px-6">Image</th>
        <th class="py-3 px-6">Name</th>
        <th class="py-3 px-6">Quantity</th>
        <th class="py-3 px-6">Price</th>
    </tr>
    </thead>
    <tbody>
        <?php $currentRoute = Route::current()->getName();
        $isUpdateCartVisibile = false;?>

        @foreach ($products as $product)
        <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
            <td data-label="Image" class="py-3" style="width: 120px">
                <a href="/product-details/{{$product['slug']}}"><img src="/{{$product['image']}}" style="width: 120px"></a>
                <button wire:click.prevent="removeItemFromCart({{ $product['id'] }})" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-0.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900" data-id="{{ $product['id'] }}" style="width: 100%">
                    <i aria-hidden="true" class="fas shopping-cart"></i>
                    Remove
                </button>
            </td>
            <td data-label="Product Name:" class="py-3">
                {{ $product['product_name'] }}
                <?php
                //dd($product);
                
                if ($product['reserve_for']) {
                    $reserveDate = \Carbon\Carbon::parse($product['reserve_time']);
                    $now = \Carbon\Carbon::now();

                    // Calculate the remaining time
                    $expiryTime = number_format($now->diffInMinutes($reserveDate),"0");
                    $expirySeconds = max(0, $now->diffInSeconds($reserveDate, false));

                    // Ensure the countdown doesn't go below 0
                    $expiryTime = max(0, $expiryTime);
                } else {
                    $expiryTime = 0;
                    $expirySeconds = 0;
                } ?>

                @if ($expiryTime > 0)
                <br>
                <span style="color: red" class="timeleft" data-product-id="{{ $product['id'] }}" data-seconds-left="{{ $expirySeconds }}">This item is being held for you for <span><?= $expiryTime ?></span> minutes</span><br>
                @endif
            </td>
            @if ($product['onhand']>1 && $currentRoute == "cart")
                <?php $isUpdateCartVisibile = true ?>
                <td class="align-middle py-3 px-6" data-label="Quantity:" style="width: 80px"><input type="number" pattern="\d" class="form-control text-center" value="{{ $product['qty'] }}" name="qty[]" /></td>
            @else
                <td class="align-middle py-3 px-6" data-label="Quantity:">{{ $product['qty'] }}</td>
            @endif
            <td class="align-middle text-right py-3 px-2" data-label="Price:">${{ number_format(($product['webprice']*$product['qty']),2) }}</td>
        </tr>
        
        @endforeach
    </tbody>
    <tfoot> 

        @if ($discount > 0)
            <tr class="font-semibold text-gray-900 dark:text-white" id="discount">
                <th colspan="3" class="text-right py-3 px-2">Discount:</th>
                <td class="text-right discountamount py-3 px-2" style="color: red">-{{ number_format($discount,2) }}</td>
            </tr>
        @endif

        <tr>
            <th colspan="3" class="text-right py-2 px-6">Sub Total:</th>
            <td  class="text-right py-2 px-2">${{ number_format($subTotalPrice,2) }}</td>
        </tr>
        <tr>
            <th colspan="3" class="text-right py-2 px-6">Tax: </th>
            <td  class="text-right py-2 px-2">{{$tax}}%</td>
        </tr>
        <tr>
            <th colspan="3" class="text-right py-2 px-6">Shipping: </th>
            @if (empty($freight))
            <td  class="text-right freightfield py-2 px-2">$0.00</td>
            @else
            <td  class="text-right py-2 px-2">${{ number_format($freight,2) }}</td>
            @endif
        </tr>
        <tr>
            <th colspan="3" class="text-right py-2 px-6">Grand Total:</th>
            @if (empty($tax))
                @if (empty($freight))
                    <td  class="text-right totalfield py-2 px-2">${{ number_format($totalPrice,2) }}</td>
                @else
                    <td  class="text-right py-3 px-2">${{ $totalPrice }}</td>
                    
                @endif
            @else
                @if (empty($freight))
                    <?php $totalPrice ?>
                @else
                    <?php $totalPrice ?>
                @endif
            <td  class="text-right py-2 px-2">$<?= $totalPrice ?></td>
            @endif
        </tr>
    </tfoot>
</table>

<div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
    <label for="promo-code" class="block text-sm font-semibold text-slate-900">Promo Code</label>
    <div class="mt-2 flex flex-col gap-2 sm:flex-row">
        <input
            id="promo-code"
            type="text"
            wire:model.defer="promoCode"
            wire:keydown.enter.prevent="applyPromoCode"
            placeholder="Enter your discount code"
            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100"
        >
        <div class="flex gap-2">
            <button
                type="button"
                wire:click.prevent="applyPromoCode"
                class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
                Apply
            </button>
            @if (session()->has('discount'))
                <button
                    type="button"
                    wire:click.prevent="removePromoCode"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                >
                    Remove
                </button>
            @endif
        </div>
    </div>

    @if (!empty($promoMessage))
        <p class="mt-3 text-sm {{ $promoMessageType === 'error' ? 'text-rose-600' : 'text-emerald-600' }}">
            {{ $promoMessage }}
        </p>
    @endif

    @if (session()->has('discount'))
        <p class="mt-2 text-xs text-slate-500">
            Applied code: <span class="font-semibold text-slate-700">{{ session()->get('discount')['promocode'] ?? '' }}</span>
        </p>
    @endif
</div>

@if ($isUpdateCartVisibile)
<button class="btn btn-success btn-sm col-sm-2 float-right update">Update Cart</button>
@endif

@script
<script>

    $(document).ready( function() {
        if (window.cartHoldTimer) {
            clearInterval(window.cartHoldTimer);
        }

        window.releasedCartHoldProducts = window.releasedCartHoldProducts || {};

        window.cartHoldTimer = setInterval(() => {
            const timers = document.querySelectorAll('.cart .timeleft[data-seconds-left]');

            if (!timers.length) {
                clearInterval(window.cartHoldTimer);
                window.cartHoldTimer = null;
                return;
            }

            timers.forEach((timerElement) => {
                const productId = timerElement.dataset.productId;
                let secondsLeft = parseInt(timerElement.dataset.secondsLeft || '0', 10);

                if (Number.isNaN(secondsLeft)) {
                    secondsLeft = 0;
                }

                secondsLeft -= 1;
                timerElement.dataset.secondsLeft = secondsLeft;

                if (secondsLeft <= 0) {
                    timerElement.remove();

                    if (productId && !window.releasedCartHoldProducts[productId]) {
                        window.releasedCartHoldProducts[productId] = true;
                        releaseHold(productId);
                    }

                    return;
                }

                const valueElement = timerElement.querySelector('span');

                if (valueElement) {
                    valueElement.textContent = Math.ceil(secondsLeft / 60);
                }
            });
        }, 1000);

        function releaseHold(product_id) {
            $.ajax({
                type: "POST",
                data: {id : product_id},
                async: false,
                url: "{{route('product.release.hold')}}",
                success: function(result) {
                    return result;
                }
            })
        }

    })

</script>
@endscript
