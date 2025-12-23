@extends('layouts.admin-default')

@section ('content')

@inject('countries','App\Libs\Countries')

@section ('content')
<div class="customer_address pb-2" style="clear: both">
<?php 
    $address2 = '';
    
    $state_b = $countries->getStateCodeFromCountry($customer->state);
    $country = $countries->getCountry($customer->country);

    echo $customer->company.'<br>';
    echo !empty($customer->address1) ? $customer->address1 .'<br>' : '';
    echo !empty($customer->address2) ? $customer->address2 .'<br>' : '';
    echo !empty($customer->city) ? $customer->city .', '. $state_b . ' ' . $customer->zip.'<br>': '';
    
    echo !empty($customer->phone) ? $customer->phone . '<br>' : '';
    echo !empty($customer->po) ? 'PO #: '.$customer->po . '<br>' : '';
        //die($customer->company);
?>
</div>


<form method="POST" action="{{route('payments.store')}}" accept-charset="UTF-8" id="paymentForm">
@csrf
<table id="payments" class="table" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Id</th>
            <th>Amount</th>
            <th>Reference</th>
            <th>Payment</th>
            <th>Date</th>

        </tr>
    </thead>
    <tbody>
        <?php $order_id = 0; $orders = $customer->orders()->sortit()->get(); ?>
        @foreach ($orders as $order)
            <?php $totalLeft = $order->total ?>
            <?php $calc = $order->total ?>

            @foreach ($order->payments as $payment)
            <?php $totalLeft = $totalLeft - $payment->amount;?>
            
            @if ($order_id != $order->id && $order_id != 0)
            <tr><td colspan="5" style="background: #c0c0c0;text-align:right;font-weight:bold">${{ number_format($previousTotal,2) }}</td></tr>
            @endif
            <tr>
                <td><a href="/admin/orders/{{ $order->id }}">{{ $order->id }}</a></td>
                <td>${{ number_format($calc,2) }}</td>
                <td>{{ $payment->ref }}</td>
                <td>${{ number_format($payment->amount,2) }}</td>
                <td style="width: 270px; text-align:right;">{{ $payment->created_at->format('m/d/Y') }}</td>
            </tr>

            <?php $calc = $calc - $payment->amount;$order_id = $order->id; ?>
            <?php $previousTotal = $order->total ?>
            @endforeach
        @endforeach
        <tr><td colspan="5" style="background: #c0c0c0;text-align:right;font-weight:bold">${{ number_format($previousTotal,2) }}</td></tr>
    </tbody>
</table>
@include('admin.errors') 

</form>

@endsection