@if (Session::has('customer'))
    <?php 
        $customer = Session::get('customer');
        if (isset($customer['orderReferenceId']) && !isset($_GET["access_token"]))
            Session::forget('customer');
        else {
            if (isset($customer['tax']))
                $tax = $customer['tax'];
            if (isset($customer['shipping']))
                $freight = $customer['shipping'];
        }
    ?>
@endif

@if (Session::has('exchange_rate'))
<?php if (session('exchange_rate')) {
     $rate = session('exchange_rate')['rate'];
     $symbol = session('exchange_rate')['symbol'].' ';
} else {
    $rate = 1; $symbol = "$";
}
?>
@else
    <?php $rate = 1; $symbol = "$"; ?>
@endif

<form method="POST" action="https://swissmadecorp.com/cart/update" accept-charset="UTF-8" id="cartupdate" autocomplete="off">
    @csrf
<table class="table hover cart">
    <thead class="td-bk">
    <tr >
        <th>Image</th>
        <th>Name</th>
        <th>Quantity</th>
        <th>Price</th>
    </tr>
    </thead>
    <tbody>
        <?php $currentRoute = Route::current()->getName();
        $totals=0; $isUpdateCartVisibile = false;?>

        @foreach ($products as $product)
        
        <tr class="td-border">
            <td data-label="Image" class="align-middle text-center" v-if="details.images" style="width: 120px">
                <?php
                //dd($product);
                if ($product['reserve_for']) {
                    $dt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $product['reserve_time']->toDateTimeString());
                    $dt2 = \Carbon\Carbon::now();
                
                    $length = number_format($dt2->diffInMinutes($dt),"0"); ?>
                    
                <?php } else {
                    $length = 0;
                } ?>
                    
                <a href="/{{$product['slug']}}"><img src="/{{$product['image']}}" style="width: 120px"></a>
                <button class="btn-danger btn btn-sm remove" data-id="{{ $product['id'] }}" style="width: 100%"><i aria-hidden="true" class="fas shopping-cart"></i>Remove</button>
            </td>
            <td data-label="Product Name:" class="align-middle">
                @if ($product['reserve_for'])
                <span style="color: red" class="timeleft">This item is on hold for you for <span><?= $length ?></span> minutes</span><br>
                @endif
                <input name="product[]" type="hidden" value="{{ $product['id'] }}" />
                {{ $product['product_name'] }}
            </td>
            @if ($product['onhand']>1 && $currentRoute == "cart")
                <?php $isUpdateCartVisibile = true ?>
                <td class="align-middle" data-label="Quantity:" style="width: 80px"><input type="number" pattern="\d" class="form-control text-center" value="{{ $product['qty'] }}" name="qty[]" /></td>
            @else
                <td class="align-middle" data-label="Quantity:">{{ $product['qty'] }}</td>
            @endif
            <td class="align-middle text-right" data-label="Price:">{{ $symbol.number_format(($product['webprice']*$product['qty'])*$rate,2) }}</td>
        </tr>
        <?php $totals +=$product['webprice'] * $product['qty']?>
        @endforeach
    </tbody>
    <tfoot> 
        <tr class="td-border" id="discount" style="display: none">
            <th colspan="3" class="text-right">Discount:</th>
            <td class="text-right discountamount" style="color: red">-{{ number_format($discount*$rate,2) }}</td>
        </tr>
    
        <tr>
            <th colspan="3" class="text-right">Sub Total:</th>
            <td  class="text-right">{{ $symbol.number_format(($totals=$totals-$discount)*$rate,2) }}</td>
        </tr>
        <tr>
            <th colspan="3" class="text-right">Tax: </th>
            @if (empty($tax))
            <td  class="text-right taxfield">{{$symbol}}0.00</td>
            @else
            <td  class="text-right">{{$tax}}%</td>
            @endif
        </tr>
        <tr>
            <th colspan="3" class="text-right">Shipping: </th>
            @if (empty($freight))
            <td  class="text-right freightfield">{{$symbol}}0.00</td>
            @else
            <td  class="text-right">{{ $symbol.number_format($freight*$rate,2) }}</td>
            @endif
        </tr>
        <tr>
            <th colspan="3" class="text-right">Grand Total:</th>
            @if (empty($tax))
                @if (empty($freight))
                    <td  class="text-right totalfield">{{ $symbol.number_format($totals*$rate,2) }}</td>
                @else
                    <td  class="text-right">{{ $symbol.number_format(($totals+ $freight)*$rate,2) }}</td>
                    <?php $total = number_format($totals + $freight,2); ?>
                @endif
            @else
                @if (empty($freight))
                    <?php $total = number_format(($totals + ($totals * ($tax/100)))*$rate,2); ?>
                @else
                    <?php $total = number_format(($totals + ($totals * ($tax/100)+$freight))*$rate,2); ?>
                @endif
            <td  class="text-right">{{$symbol.($total)}}</td>
            @endif
        </tr>
    </tfoot>
</table>

@if ($isUpdateCartVisibile)
<button class="btn btn-success btn-sm col-sm-2 float-right update">Update Cart</button>
@endif
</form>
<script>

    $(document).ready( function() {
        $('.remove').click( function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                data: {id: $(this).attr('data-id')},
                url: "{{route('cart.product.remove')}}",
                success: function (result) {
                    if (result>0)
                        location.reload();
                    else document.location.href = '/cart';
                }
            })
        })

        @if ($discount>0)
            $('#discount').show();
        @endif

        // $('.update').click( function () {
        //     $.ajax({
        //         type: "POST",
        //         // data: {promocode: $('.promo input').val()},
        //         url: "{{route('cart.update')}}",
        //         success: function (result) {
        //             return result
        //         }
        //     })
        // })
        
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
        
        $('.promo button').click( function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                data: {promocode: $('.promo input').val()},
                url: "{{route('cart.promo')}}",
                success: function (result) {
                    
                    $.alert({
                        title: 'Promo Alert',
                        content: result.content,
                        buttons: {
                            formSubmit: {
                                text: 'Ok',
                                action: function() {
                                    $('.promo input').val('');
                                    if (result.error == 0 )
                                        document.location.href = '/cart';
                                }
                            }
                        }
                    });
                
                }
            })
        })
    })

</script>