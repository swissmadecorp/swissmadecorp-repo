@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
<?php $payments_options = ['None' =>'None','Net-30'=>'Net 30','Net-60'=>'Net 60','Net-120'=>'Net 120']; ?>
<p><b>Purchase Date: {{ $estimate->created_at->format('m/d/Y') }}</b></p>
<form method="POST" action="{{route('estimates.update',[$estimate->id])}}" accept-charset="UTF-8" id="estimateform">
    @csrf
    @method('PATCH')
    <input type="hidden" value="edit-estimator" name="_blade">
    <div class="form-group row">
        <div class="col-6">
            <label for="po-input" class="col-form-label">PO Number</label>
            <input name="po" id="po-input" class="form-control" type="text" value="po">
        </div>    
        <div class="col-6">
            <label for="payment-input" class="col-form-label">Payment Method</label>
            <select class="form-control" name="payment">
                @foreach (Payments() as $value => $payment)
                    <option value="{{ $payment }}" <?php echo !empty($estimate->method) && $estimate->method==$payment ? 'selected' : '' ?>>{{ $payment }}</option>
                @endforeach
            </select>

            <label for="payment-options-name-input" class="col-form-label">Payment Options</label>
            <select class="form-control" id="payment-options-name-input" name="payment_options">
                @foreach (PaymentsOptions() as $value => $payment_option)
                <option value="{{ $value }}" <?php echo !empty($estimate->payment_options) && $estimate->payment_options==$value ? 'selected' : '' ?>>{{ $payment_option }}</option>
            @endforeach
        </select>
        </div>    
    </div>

<div class="order-group billing" style="margin-right: 8px;margin-bottom: 8px;">
    <div class="group-title">Billing Address</div>
    <div class="p-1">
        <div class="form-group row">
            <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <input name="b_firstname" id="b_firstname-input" class="form-control" type="text" value="{{$estimate->b_firstname}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <input name="b_lastname" id="b_lastname-input" class="form-control" type="text" value="{{$estimate->b_lastname}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="b-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <input name="b_company" id="b_company-input" class="form-control" type="text" value="{{$estimate->b_company}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <input name="b_address1" id="b_address1-input" class="form-control" type="text" value="{{$estimate->b_address1}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
            <input name="b_address2" id="b_address2-input" class="form-control" type="text" value="{{$estimate->b_address2}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="b-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <input name="b_phone" id="b_phone-input" class="form-control" type="text" value="{{$estimate->b_phone}}">
            </div>
        </div>        
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                @inject('countries','App\Libs\Countries')
                <?php echo $countries->getAllCountries($estimate->b_country) ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                <?php echo $countries->getAllStates($estimate->b_state) ?>
            </div>
        </div>
        <div class="form-group row">
            <label for="b-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <input name="b_city" id="b_city-input" class="form-control" type="text" value="{{$estimate->b_city}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <input name="b_zip" id="b_zip-input" class="form-control" type="text" value="{{$estimate->b_zip}}">
            </div>
        </div>
    </div>
</div>

<div class="order-group shipping">
    <div class="group-title">Shipping Address</div>
    <div class="p-1">
    <div class="form-group row">
            <label for="s-firstname-input" class="col-3 col-form-label">First Name</label>
            <div class="col-9 input-group">
                <input name="s_firstname" id="s_firstname-input" class="form-control" type="text" value="{{$estimate->s_firstname}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="s-lastname-input" class="col-3 col-form-label">Last Name</label>
            <div class="col-9 input-group">
                <input name="s_lastname" id="s_lastname-input" class="form-control" type="text" value="{{$estimate->s_lastname}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="s-company-input" class="col-3 col-form-label">Company</label>
            <div class="col-9 input-group">
                <input name="s_company" id="s_company-input" class="form-control" type="text" value="{{$estimate->s_company}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="s-address1-input" class="col-3 col-form-label">Address 1</label>
            <div class="col-9 input-group">
                <input name="s_address1" id="s_address1-input" class="form-control" type="text" value="{{$estimate->s_address1}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="s-address2-input" class="col-3 col-form-label">Address 2</label>
            <div class="col-9 input-group">
                <input name="s_address2" id="s_address2-input" class="form-control" type="text" value="{{$estimate->s_address2}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="s-phone-input" class="col-3 col-form-label">Phone</label>
            <div class="col-9 input-group">
                <input name="s_phone" id="s_phone-input" class="form-control" type="text" value="{{$estimate->s_phone}}">
            </div>
        </div>            
        <div class="form-group row">
            <label for="b-country-input" class="col-3 col-form-label">Country</label>
            <div class="col-9 input-group">
                {!! $countries->getAllCountries($estimate->s_country,'s_') !!}
            </div>
        </div>
        <div class="form-group row">
            <label for="s-state-input" class="col-3 col-form-label">State</label>
            <div class="col-9 input-group">
                {!! $countries->getAllStates($estimate->s_state,'s_') !!}
            </div>
        </div>
        <div class="form-group row">
            <label for="s-city-input" class="col-3 col-form-label">City</label>
            <div class="col-9 input-group">
                <input name="s_city" id="s_city-input" class="form-control" type="text" value="{{$estimate->s_city}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="s-zip-input" class="col-3 col-form-label">Zip Code</label>
            <div class="col-9 input-group">
                <input name="s_zip" id="s_zip-input" class="form-control" type="text" value="{{$estimate->s_zip}}">
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
        <th>Delete</th>
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
            <td>
                <img style="width: 80px" src="{{ 'images/thumbs/' . $image }}" />
            </td>
            <td  style="width:28%"><input type="text" class="form-control" name="product_name[{{ $product->id }}]" value="{{ !$product->pivot->product_name ? $product->title : $product->pivot->product_name }}" /> </td>
            <td style="text-align: center"><input style="width: 40px" type="text" name="qty[{{ $product->id }}]" value="{{ $product->pivot->qty }}" /> </td>
            <td style="text-align: right">{{ number_format($product->p_retail,2) }} </td>
            <td style="text-align: right">$<input style="width: 100px" type="text" name="price[{{ $product->id }}]" value="{{ $product->pivot->price }}" /> </td>
            <td style="text-align: right">
                <button class="btn btn-danger delete" data-id="{{$product->pivot->product_id}}"><i class="fa fa-trash" aria-hidden="true"></i></button>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="text-align: right" colspan="5"></td>
            <td style="text-align: right"><button class="btn btn-success addnew"><i class="fa fa-plus" aria-hidden="true"></i></button></td>
        </tr>
        <tr>
            <td style="text-align: right" colspan="5"><b>Freight</b></td>
            <td style="text-align: right; width: 100px"><input type="text" class="input-group" value="{{ $estimate->freight }}" name="freight" /></td>
        </tr>
        <tr>
            <td style="text-align: right" colspan="5"><b>Sub Total</b></td>
            <td style="text-align: right">{{ number_format($estimate->subtotal,2) }}</td>
        </tr>
        @if ($estimate->customers->first()->cgroup==0)
        <tr>
            <td style="text-align: right" colspan="5"><b>Tax</b></td>
            <td style="text-align: right">{{ number_format($estimate->taxable,3) }}</td>
        </tr>
        @endif
        <!-- <tr>
            <td style="text-align: right" colspan="5"><b>Freight</b></td>
            <td style="text-align: right"><input name='freight' id="s-freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" value="{{ number_format($estimate->freight,2) }}" /></td>
        </tr>         -->
        <tr>
            <td style="text-align: right" colspan="5"><b>Grand Total</b></td>
            <td style="text-align: right">${{number_format($estimate->total,2)}}</td>
        </tr>
                  
    </tfoot>
</table>

<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Comments</label>
        <input type="text" name="comments" id="comments-input" style="height: 150px; overflow-y:auto;overflow-x:hidden" class="form-control">{{ $estimate->comments }}
    </div>    
</div>

<button type="submit" class="btn btn-danger update">Update Order</button>

@include('admin.errors')
<div class="float-right">
    <a href="{{ URL::to('admin/estimates/'.$estimate->id.'/print') }}" class="btn btn-primary pull-left" style="margin-right: 3px;">Print</a>
</div>
<div class="float-right mr-3">
    <a href="{{ route('invoice.create', array('id' => $estimate->id)) }}" class="btn btn-primary">Make Invoice</a>
</div>
</form>

<div id="product-container"></div>
@endsection

@section ('footer')
<script src="/fancybox/jquery.fancybox.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = $('input[name=_blade]').val();

    $(document).ready( function() {
        @include ('admin.countrystate')

        $('.billing input').on('input propertychange', function() {
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
        })

        stateFromCountry('#b_country-input');
        stateFromCountry('#s_country-input');

        function stateFromCountry(_this,stateId) {
            $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
            .done (function (data) {
                if ($(_this).attr('id') == 'b_country-input') {
                    $('#b_state-input').html(data);
                    $('#b_state-input').val("{{$estimate->b_state}}")
                } else {
                    $('#s_state-input').html(data);
                    $('#s_state-input').val("{{$estimate->s_state}}")
                }   

                
            })
        }

        $('#b_country-input,#s_country-input').change( function() {
            stateFromCountry($(this));
        })

        $('.billing input, .billing select').on('input propertychange', function(e) { 
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
            $('#s_country-input').change()
            //if (!$('#s'+id.substr(1)).val() || e.currentTarget.tagName=="SELECT")
                
        })

        $(".addnew").on('click', function(e) {
            e.preventDefault();
            $.ajax({
                type: "GET",
                url: "{{route('estimated.products')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    _blade: blade
                },
                success: function (result) {
                    $('#product-container').html(result.content.content+result.content.jquery);

                    $.fancybox.open({
                        src: "#product-container",
                        type: 'inline',
                        width: 980,
                    });
                }
            })
        });

        $('#b_zip-input').blur(function() {
            getAddressFromZip(this);
        })

        $('#s_zip-input').blur(function() {
            getAddressFromZip(this,'s');
        })

        function getAddressFromZip(zip,location) {
            $.get("{{route('address.from.zip')}}",{zip: $(zip).val()},function(data) {
                if (data.city) {
                    if (location == 's') {
                        $('#s_city-input').val(data.city)
                        $('#s_state-input').val(data.state);    
                    } else {
                        $('#b_city-input').val(data.city)
                        $('#s_city-input').val(data.city)
                        $('#b_state-input').val(data.state);
                        $('#s_state-input').val(data.state);
                        if ($('#b_zip-input').val()=='') {
                            $('#b_zip-input').val($(zip).val())
                        };
                    }
                }
            })
        }
        
        $('.fancybox-close-small').click( function () {
            $.fn.fancybox.close()
        })

        $(".delete").on('click', function(e) {
            _this = $(this);
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this item from the order?')) return

            $.ajax({
                type: "GET",
                url: "{{route('delete.estimate.product')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    estimateid: "{{$estimate->id}}",
                    productid: _this.attr('data-id')
                },
                success: function (result) {
                    if (result=='success') {
                        location.reload(true);
                    } else 
                        alert('There were some errors while deleting this product.')
                }
            })
        });
        
        $(document).on('click','.deletenew', function(e) {
            e.preventDefault();
            $(this).parents('tr').remove();
        })
    })

</script>
@endsection