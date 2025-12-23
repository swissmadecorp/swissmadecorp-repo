@extends('layouts.admin-default')

@section ('content')

@inject('countries','App\Libs\Countries')

<a href="/admin/orders/{{$order->id}}/payments/print" class="btn btn-primary float-right">Print Statement</a>
<div class="customer_address pb-2" style="clear: both">
<?php 
    $address2 = '';
    
    if (in_array($order->b_company,['Website','eBay','Chrono24'])) {
        $state_s = $countries->getStateCodeFromCountry($order->s_state);
        $country = $countries->getCountry($order->s_country);

        echo $order->s_company.'<br>';
        echo !empty($order->s_address1) ? $order->s_address1 .'<br>' : '';
        echo !empty($order->s_address2) ? $order->s_address2 .'<br>' : '';
        echo !empty($order->s_city) ? $order->s_city .', '. $state_s . ' ' . $order->s_zip.'<br>': '';
        
        echo !empty($order->b_phone) ? $order->b_phone . '<br>' : '';
        echo !empty($order->po) ? 'PO #: '.$order->po . '<br>' : '';
    } else {
    $state_b = $countries->getStateCodeFromCountry($order->b_state);
    $country = $countries->getCountry($order->b_country);

    echo $order->b_company.'<br>';
    echo !empty($order->b_address1) ? $order->b_address1 .'<br>' : '';
    echo !empty($order->b_address2) ? $order->b_address2 .'<br>' : '';
    echo !empty($order->b_city) ? $order->b_city .', '. $state_b . ' ' . $order->b_zip.'<br>': '';
    
    echo !empty($order->b_phone) ? $order->b_phone . '<br>' : '';
    echo !empty($order->po) ? 'PO #: '.$order->po . '<br>' : '';
    }
        //die($order->b_company);
?>
</div>

<div>
<form method="POST" action="{{route('payments.store')}}" accept-charset="UTF-8" id="paymentForm">
@csrf
<input type="hidden" name="order_id" value="{{ $order->id }}">
<table id="payments" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Id</th>
            <th>Amount</th>
            <th></th>
            <th>Payment</th>
            <th>Date</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php $totalLeft = $order->total ?>
        <?php $calc = $order->total ?>

        @if (count($order->payments))
            @foreach ($order->payments as $payment)
            <?php $totalLeft = $totalLeft - $payment->amount ?>
            
            <tr>
                <td>{{ $payment->id }}</td>
                <td>${{ number_format($calc,2) }}</td>
                <td>{{ $payment->ref }}</td>
                <td>${{ number_format($payment->amount,2) }}</td>
                <td style="width: 270px; text-align:right">{{ $payment->created_at->format('m/d/Y') }}</td>
                <td style="text-align: center">
                    <button type="button" style="padding: 3px 5px" data-id="{{ $payment->id }}" class="btn btn-danger deleteitem" aria-label="Left Align">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </td>

            </tr>
            <?php $calc = $calc - $payment->amount ?>
            @endforeach
            @if ($totalLeft > 0)
            <tr>
                <td></td>
                <td></td>
                <td>
                    <input type="hidden" name="totalLeft" value="{{$totalLeft}}">
                    </td>
                <td>
                    ${{ number_format($totalLeft,2) }}
                    <input type="hidden" value="{{$totalLeft}}" name="fullamount" class="fullamount">
                </td>
                <td colspan='2'><input type="text" style="width: 140px" name="payment" class="payment" placeholder="$ Amount" required>
                    <input type="text" style="width: 120px" name="payment_option" class="payment_option" placeholder="Reference" required>
                    <button tooltip="Full amount" style="padding: 3px 5px" class="btn btn-primary copyamount" aria-label="Left Align">
                        <i class="far fa-copy" tooltip="Copy full amount"  aria-hidden="true"></i>
                    </button>
                    <button style="padding: 3px 5px" class="btn btn-primary additem" aria-label="Left Align">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </button>
                </td>
                
            </tr>
            @else
            <td colspan="6" style="text-align: center; color: green">Order has been paid fully</td>
            @endif
        @else 
        @if ($order->status == 0)
        <tr>
            <td>
                {{ $order->id }}
                <input type="hidden" name="totalLeft" value="{{$totalLeft}}">
                <input type="hidden" name="order_id" value="{{ $order->id }}">
            </td>
            <td>
                ${{ number_format($order->total,2) }}
                <input type="hidden" value="{{$order->total}}" name="fullamount" class="fullamount">
            </td>
            <td>
                <input type="text" style="width: 140px" name="payment" class="payment" placeholder="$ Amount" required>
                <input type="text" style="width: 120px" name="payment_option" placeholder="Reference" required>
                <button tooltip="Full amount" style="padding: 3px 5px" class="btn btn-primary copyamount" aria-label="Left Align">
                      <i class="far fa-copy" tooltip="Copy full amount"  aria-hidden="true"></i>
                </button>
                <button style="padding: 3px 5px" class="btn btn-primary additem" aria-label="Left Align">
                      <i class="fas fa-plus" aria-hidden="true"></i>
                </button>
            </td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @else
            <td colspan="5" style="text-align: center; color: green">Order has been paid fully</td>
        @endif
        @endif
    </tbody>
</table>
@include('admin.errors')   
</div>

</form>
@if ($order->status == 1)
<div class="paid-in-full">&nbsp;</div>
@endif

@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        $('.deleteitem').click( function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete selected payment?')) {
                window.location.href=$(this).attr('data-id')+'/destroy';
            }
        })

        $('.copyamount').click ( function (e) {
            e.preventDefault();
            amount = $('#total_credit').attr('data-amount');

            // if (amount>0)
            //     $('.payment').val(amount)
            // else
            $('.payment').val($('.fullamount').val())

        })

        $('.additem').click( function(e) {
            if ($('.payment_option').val()!='' && $('.payment').val()!='') {
                //$(this).prop('disabled','disabled');
            }
        })
    })    
</script>
@endsection