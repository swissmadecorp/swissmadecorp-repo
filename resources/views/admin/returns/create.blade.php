@extends('layouts.admin-default')

@section ('content')

@inject('countries','App\Libs\Countries')
<div class="customer_address pb-2">
<?php 
    $address2 = '';
    
    $state_b = $countries->getStateCodeFromCountry($order->b_state);
    $country = $countries->getCountry($order->b_country);

    echo $order->b_company.'<br>';
    echo !empty($order->b_address1) ? $order->b_address1 .'<br>' : '';
    echo !empty($order->b_address2) ? $order->b_address2 .'<br>' : '';
    echo !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.'<br>': '';
    
    echo !empty($order->b_phone) ? $order->b_phone . '<br>' : '';
    echo !empty($order->po) ? 'PO #: '.$order->po . '<br>' : '';

?>
</div>

<div class="pb-2">Order Date: {{ $order->created_at->toFormattedDateString() }}</div>
<div>
<form method="POST" action="{{route('returns.store')}}" accept-charset="UTF-8" id="returnsForm">
@csrf

<input type="hidden" id="order_id" name="order_id" value="{{ $order->id }}">
<table id="returns" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Image</th>
            <th>ID</th>
            <th>Product</th>
            <th>Qty Purchased</th>
            <th>Qty Returned</th>
            <th>Qty</th>
            <th>Amount</th>
            <th>Return</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php $returns = array(); $comment = ""; ?>

        @foreach ($orderreturns as $return)
            <?php 
                $returns[] = array(
                    'ret_op_id'=>$return->ret_op_id,
                    'product_id'=>$return->product_id,
                    'qty'=>$return->qty,
                    'date'=>$return->date
                );
                
             ?>
        @endforeach

        <?php 
        
            if (!$order->returns->isEmpty())
                $comment = $order->returns->first()->comment 
        ?>

        @foreach ($order->products as $product)
        <?php 
            $qty = ''; $product_id='';$date='';
            $id = array_search($product->pivot->op_id,array_column($returns,'ret_op_id'));
            if ($id !== false) {
                $qty = e($returns[$id]['qty']);
                $product_id = $returns[$id]['product_id'];
                $date = date('m-d-Y',strtotime($returns[$id]['date']));
            }

            $orderQty = $product->pivot->qty;
            $p_image = $product->images->toArray();
            if (!empty($p_image)) {
                $image=$p_image[0]['location'];
            } else $image = '../no-image.jpg';

        ?>
        <tr>
            <td><img style="width: 70px" src="{{ '/images/thumbs/'.$image }}" /></td>
            <td><a href="/admin/products/{{$product->id}}/edit">{{$product->id}}</a></td>
            <td>{{ $product->pivot->product_name }} </td>
            <td style="text-align: center">{{ $product->pivot->qty }}<input name="qty_purchased[]" type="hidden" value="{{ $product->pivot->qty }}" /></td>
            <td style="text-align: center"><span>{{ $qty }}</span></td>
            <td>@if ($orderQty>0)<input type="text" style="width: 70px">@endif</td>
            <td style="text-align: right">{{ number_format($product->pivot->price,2) }} </td>
            <td style="text-align: center">
                @if ($orderQty>0)
                <input type="hidden" value="{{ $product->id }}" name="product_id[]" />
                <input type="hidden" value="{{ $product->pivot->op_id }}" name="op_id[]" />
                
                <button style="padding: 3px 5px" data-opid="{{$product->pivot->op_id}}" data-id="{{ $product->id }}" class="btn btn-primary returnItem" aria-label="Left Align">
                    <i class="fa fa-undo" aria-hidden="true"></i>
                </button>
                @endif
            </td>
            <td>{{ $date }}</td>
        </tr>
        @endforeach
        </tr>
    </tbody>
</table>

    <div class="clearfix"></div>

    <div class="form-group row">
        <div class="col-12">
            <label for="comments-input" class="col-form-label">Comments</label>
            <textarea rows="5" style="width: 100%" id="comments-input" name="comments">{!! e($comment) !!}</textarea>
        </div>
    </div>

    <button type="submit" <?php echo $order->total==0 ? 'disabled' : '' ?> class="btn btn-primary returnAll">Return All</button>
    <a href="/admin/returns/{{$order->id}}/print" class="btn btn-primary">Print</a>
@include('admin.errors')   
</div>

</form>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        $('.returnItem').click( function(e) {
            e.preventDefault();
            var _this = $(this);
            var order_id = $('#order_id').val();
            var p = $(this).parents('tr');
            var qty = p.find('td:nth-child(6)').children();
            var qty_purchased = p.find('td:nth-child(4)').text();
            var qty_returned = p.find('td:nth-child(5)').text();

            if (!qty_returned) qty_returned = 0;
            if ( (parseInt(qty.val()) + parseInt(qty_returned)) > parseInt(qty_purchased)) {
                alert ('You are trying to return more than was originally purchased. You can only return ' + qty_purchased + ' back.');
                return false;
            }

            if (!qty.val()) {
                alert ('Please specify the amount you would like to return.');
                return false;
            }
            
            if (confirm('Are you sure you want to return a selected product?')) {
                $.ajax({
                    type: "GET",
                    url: "{{route('return.item')}}",
                    data: { 
                        _id:    $(_this).attr('data-id'),
                        _orderid: order_id,
                        _qty:   qty.val(),
                        _opid: $(_this).attr('data-opid'),
                        _comment: $('#comments-input').val()
                    },
                    success: function (result) {
                        if ((parseInt(qty.val()) + parseInt(qty_returned)) == result.qty) {
                            $(_this).hide();
                            p.find('td:nth-child(4)').text('-'+qty.val());
                            p.find('td:nth-child(5)').text(result.qty);
                            p.find('td:nth-child(6)').find('input').hide()
                        } else {
                            p.find('td:nth-child(5)').text((parseInt(qty.val()) + parseInt(qty_returned)));
                            $(qty).val('');
                        }
                        p.find('td:nth-child(9)').text(result.date);
                        p.find('td:nth-child(8)').children().remove();
                        
                    }
                })
            }

        })

        $('.returnAll').click( function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to return an entire order?')) {
                $('#returns tr').each ( function (i) {
                    if (i>0) {
                        qty = $(this).find('td:nth-child(4)').text();
                        if (qty>0)
                            $(this).find('td:nth-child(6)').find('input').val(qty)
                    }
                })  
                
                $.ajax({
                    type: "GET",
                    url: "{{route('return.all.items')}}",
                    data: { 
                        _comment: $('#comments-input').val(),
                        _form: $('#returnsForm').serialize()
                    },
                    success: function (result) {
                        $('#returns tr').each( function () {
                            if ($(this).find('td:nth-child(4)').text()>0){
                                $(this).find('td:nth-child(5)').text($(this).find('td:nth-child(4)').text());
                                $(this).find('td:nth-child(4)').text(-$(this).find('td:nth-child(4)').text())
                                $(this).find('td:nth-child(8),td:nth-child(6)').children().hide();
                                $(this).find('td:nth-child(9)').text(result.date);
                            }
                        })
                        
                    }
                })
            }

        })
    })    
</script>
@endsection