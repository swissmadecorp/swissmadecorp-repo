
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

                    // Ensure the countdown doesn't go below 0
                    $expiryTime = max(0, $expiryTime);
                } else {
                    $expiryTime = 0;
                } ?>

                @if ($expiryTime > 0)
                <br>
                <span style="color: red" class="timeleft">This item is being held for you for <span><?= $expiryTime ?></span> minutes</span><br>
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

        <tr class="font-semibold text-gray-900 dark:text-white" id="discount" style="display: none">
            <th colspan="3" class="text-right py-3 px-2">Discount:</th>
            <td class="text-right discountamount py-3 px-2" style="color: red">-{{ number_format($discount,2) }}</td>
        </tr>

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

@if ($isUpdateCartVisibile)
<button class="btn btn-success btn-sm col-sm-2 float-right update">Update Cart</button>
@endif

@script
<script>

    $(document).ready( function() {
        var indx = 0, cartTotalItems = "{{ \App\Models\Cart::count() }}"
        var timer = setInterval(() => {
            var product_id;
            $('.cart tr').each(function(index){
                product_id = $(this).find('td:eq(0)').find('button').attr('data-id')
                if (typeof product_id !== "undefined") {
                    _this = this
                    $.ajax({
                        type: "GET",
                        data: {id : product_id},
                        url: "{{route('product.time.left')}}",
                        async: false,
                        success: function(result) {
                            if ($(_this).find('td:eq(1)').find('span').length>0) {
                                if (result == 0) {
                                    $(_this).find('td:eq(1)').find('span.timeleft').remove();
                                    releaseHold(product_id)
                                    cartTotalItems --;
                                    if (cartTotalItems == 0)
                                        clearInterval(timer)
                                } else 
                                    $(_this).find('td:eq(1)').find('span.timeleft span').text(result)
                            }
                        }
                    })
                }
            })

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