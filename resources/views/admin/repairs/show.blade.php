@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
<link href="{{ asset('/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
@endsection

@section ('content')
<?php $payments_options = ['None' =>'None','Net-30'=>'Net 30','Net-60'=>'Net 60','Net-120'=>'Net 120']; ?>
<p>Purchase Date: <b>{{ $order->created_at->format('m/d/Y') }}</b></p>
<p>Purchased from:  
    <?php if (!empty($order->purchased_from)) { ?>
        <?php if ($order->purchased_from==1) { ?>
            <span style="font-weight: bold">Swiss Made</span>
        <?php } else { ?>
            <span style="font-weight: bold">Signature Time</span>
        <?php } ?>
    <?php } ?>
</p>
<div class="form-group row">
<div class="col-6">
    <label for="po-input" class="col-form-label">PO Number</label>
    <span class="form-control" name="po-input">{{ $order->po ? $order->po : '&nbsp;' }}</span>
</div>    
<div class="col-6">
    <label for="payment-input" class="col-form-label">Payment Method</label>
    <span class="form-control">{{ $order->method }}</span>
    
    <label for="payment-input" class="col-form-label">Payment Option</label>
    <span class="form-control">{{ PaymentsOptions()->get($order->payment_options) }}</span>
    
</div>    
</div>

<div class="row">
    <div class="col-md-6 order-group billing" >
        <div class="group-title">Billing Address</div>
        <div class="p-1">
        <div class="form-group row">
            <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_firstname }}</span>
                
            </div>
        </div>
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_lastname }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_company }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_address1 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_address2 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_phone }}</span>
            </div>
        </div>        
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                @inject('countries','App\Libs\Countries')
                <span class="form-control">{{ $countries->getCountry($order->b_country) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getStateFromCountry($order->b_state) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_city }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->b_zip }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-email-input" class="col-3 col-form-label">Email</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->email }}</span>
            </div>
        </div>        
        </div>
    </div>

    <div class="order-group col-md-6 shipping">
        <div class="group-title">Shipping Address</div>
        <div class="p-1">
            <div class="form-group row">
            <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_firstname }}</span>
                
            </div>
        </div>
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_lastname }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_company }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_address1 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_address2 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="s-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_phone }}</span>
            </div>
        </div>            
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getCountry($order->s_country) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getStateFromCountry($order->s_state) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_city }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $order->s_zip }}</span>
            </div>
        </div>
        </div>
    </div>
</div>

<table class="table order-products">
    <thead>
        <tr>
        <th>Image</th>
        <th>Product Name</th>
        <th>Serial#</th>
        <th>Qty</th>
        <th>Cost</th>
        <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->products as $product)
        <?php 
            $p_image = $product->images->toArray();
            if (!empty($p_image)) {
                $image=$p_image[0]['location'];
            } else $image = '../no-image.jpg';
        ?>
        <tr>
            <td><img style="width: 70px" src="{{ URL::to('/public/images/thumbs').'/'.$image }}" />
                <input type="hidden" name="product_id" value="{{$product->id}}">
            </td>
            <td><a href="/admin/products/{{$product->id}}/edit">{{ !$product->pivot->product_name ? $product->title : $product->pivot->product_name}}</a></td>
            <td>{{ $product->pivot->serial }} </td>
            <td>{{ $product->pivot->qty }} </td>
            <td style="text-align: right">{{ number_format($product->p_price,2) }} </td>
            <td style="text-align: right">{{ number_format($product->pivot->price,2) }} </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="text-align: right" colspan="5"><b>Sub Total</b></td>
            <td style="text-align: right">{{ number_format($order->subtotal,2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right" colspan="5"><b>Freight</b></td>
            <td style="text-align: right">{{ number_format($order->freight,2) }}</td>
        </tr>
        @if ($order->customers()->first()->cgroup==1)
        <tr>
            <td style="text-align: right" colspan="5"><b>Tax</b></td>
            <td style="text-align: right">{{ number_format($order->taxable,3) }}</td>
        </tr>
        @endif
        <tr>
            <td style="text-align: right" colspan="5"><b>Grand Total</b></td>
            <td style="text-align: right">${{number_format($order->total,2)}}</td>
        </tr>           
    </tfoot>
</table>

<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Comments</label>
        <span id="comments-input" style="height: 150px; overflow-y:auto;overflow-x:hidden" class="form-control">{{ $order->comments }}</span>
    </div>    
</div>

<div class="float-right">
<a href="{{ URL::to('admin/orders/'.$order->id.'/print') }}" class="btn btn-primary pull-left" style="margin-right: 3px;">Print</a>
</div>
@if ($order->status==0)
<div class="float-right mr-3">
<a href="{{ route('payments.create', array('id' => $order->id)) }}" class="btn btn-primary">Go to Payments</a>
</div>
@endif
<div class="float-right mr-3">
<a href="{{ route('create.returns', array('id' => $order->id)) }}" class="btn btn-primary">Go to Returns</a>
</div>
<div class="float-right mr-3">
<a href="{{ URL::to('admin/orders/'.$order->id.'/edit') }}" class="btn btn-danger pull-left" style="margin-right: 3px;">Edit</a>
</div>



@if ($order->method == 'On Memo' || $order->method == 'On Hold')
<div class="float-right mr-3">
    <a href="{{$order->id}}/memotransfer" class="btn btn-primary">Transfer to Order</a>
</div>
@endif

@endsection

@section ('footer')
<script src="{{ asset('/fancybox/jquery.fancybox.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      