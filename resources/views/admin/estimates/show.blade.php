@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
<link href="{{ asset('/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
@endsection

@section ('content')

<p><b>Purchase Date: {{ $estimate->created_at->format('m/d/Y') }}</b></p>
<form method="POST" action="{{ route('memotransfer.update', array('id' => $estimate->id)) }}">
<div class="form-group row">
    <div class="col-6">
        <label for="po-input" class="col-form-label">PO Number</label>
        <span class="form-control" name="po-input">{{ $estimate->po }}</span>
    </div>    
    <div class="col-6">
        <label for="payment-input" class="col-form-label">Payment Method</label>
        @if ($estimate->method == 'On Memo' || $estimate->method == 'On Hold')
        <select class="form-control" name="payment">
            @foreach (Payments() as $value => $payment)
                <option value="{{ $payment }}" <?php echo !empty($estimate->method) && $estimate->method==$payment ? 'selected' : '' ?>>{{ $payment }}</option>
            @endforeach
        </select>
        @else
        <span class="form-control">{{ $estimate->method }}</span>
        @endif
    
        <label for="payment-input" class="col-form-label">Payment Option</label>
        @if ($estimate->method == 'On Memo' || $estimate->method == 'On Hold')
        <select class="form-control" id="payment-options-name-input" name="payment_options" required>
            @foreach (PaymentsOptions() as $value => $payment_option)
                <option value="{{ $value }}" <?php echo !empty($estimate->payment_options) && $estimate->payment_options==$value ? 'selected' : '' ?>>{{ $payment_option }}</option>
            @endforeach
        </select>
        @else
        <span class="form-control">{{ PaymentsOptions()->get($estimate->payment_options) }}</span>
        @endif 
    </div>    
</div>

<div class="order-group billing" style="margin-right: 8px;margin-bottom: 8px;">
    <div class="group-title">Billing Address</div>
    <div class="p-1">
        <div class="form-group row">
            <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_firstname }}</span>
                
            </div>
        </div>
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_lastname }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_company }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_address1 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_address2 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_phone }}</span>
            </div>
        </div>        
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                @inject('countries','App\Libs\Countries')
                <span class="form-control">{{ $countries->getCountry($estimate->b_country) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_city }}</span>
            </div>
        </div>        
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getStateCodeFromCountry($estimate->b_state) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->b_zip }}</span>
            </div>
        </div>
    </div>
</div>

<div class="order-group shipping">
    <div class="group-title">Shipping Address</div>
    <div class="p-1">
        <div class="form-group row">
            <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_firstname }}</span>
                
            </div>
        </div>
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_lastname }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_company }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_address1 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_address2 }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="s-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_phone }}</span>
            </div>
        </div>            
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getCountry($estimate->s_country) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_city }}</span>
            </div>
        </div>        
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $countries->getStateCodeFromCountry($estimate->s_state) }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <span class="form-control">{{ $estimate->s_zip }}</span>
            </div>
        </div>
    </div>
</div>


<table class="table estimate-products">
    <thead>
        <tr>
        <th>Image</th>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Retail</th>
        <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($estimate->products as $product)
        <?php 
        $noImage = '../no-image.jpg';
        $p_image = $product->images->toArray();
        if (!empty($p_image)) {
            $image=$p_image[0]['location'];
        } else $image = $noImage;
        ?>

        <tr>
            <td><img style="width: 80px" src="/images/thumbs/{{$image }}" />
                <input type="hidden" name="product_id" value="{{$product->id}}">
            </td>
            <td><a href="/admin/products/{{ $product->id }}/edit">{{ !$product->pivot->product_name ? $product->title : $product->pivot->product_name}}</a></td>
            <td style="text-align: center">{{ $product->pivot->qty }} </td>
            <td style="text-align: right">{{ number_format($product->p_retail,2) }} </td>
            <td style="text-align: right">{{ number_format($product->pivot->price,2) }} </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="text-align: right;border-top: 1px solid #b3afaf" colspan="4"><b>Sub Total</b></td>
            <td style="text-align: right;border-top: 1px solid #b3afaf">{{ number_format($estimate->subtotal,2) }}</td>
        </tr>
        @if ($estimate->customers->first()->cgroup==0)
        <tr>
            <td style="text-align: right" colspan="4"><b>Tax</b></td>
            <td style="text-align: right">{{ number_format($estimate->taxable,3) }}</td>
        </tr>
        @endif
        <tr>
            <td style="text-align: right" colspan="4"><b>Freight</b></td>
            <td style="text-align: right">{{ number_format($estimate->freight,2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right" colspan="4"><b>Grand Total</b></td>
            <td style="text-align: right">${{number_format($estimate->total,2)}}</td>
        </tr>
                  
    </tfoot>
</table>

<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Comments</label>
        <span id="comments-input" style="height: 150px; overflow-y:auto;overflow-x:hidden" class="form-control">{{ $estimate->comments }}</span>
    </div>    
</div>

<div class="float-right mr-3">
    <a href="{{ route('invoice.create', array('id' => $estimate->id)) }}" class="btn btn-primary">Make Invoice</a>
</div>
<div class="float-right">
    <a href="{{ URL::to('admin/estimates/'.$estimate->id.'/print') }}" class="btn btn-primary pull-left" style="margin-right: 3px;">Print</a>
</div>
<div class="float-right mr-3">
    <a href="{{ URL::to('admin/estimates/'.$estimate->id.'/edit') }}" class="btn btn-danger pull-left" style="margin-right: 3px;">Edit</a>
</div>


</form>
@endsection

@section ('footer')
<script src="{{ asset('/fancybox/jquery.fancybox.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      