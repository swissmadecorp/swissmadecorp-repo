@extends('layouts.admin-default')

@section ('header')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
<form method="POST" action="{{route('memotransfer.update',[$order->id])}}" accept-charset="UTF-8" id="orderForm">
@csrf
@method('PATCH')
@include('admin.errors')
<input type="hidden" name="customer_id" id="customer_id" value="{{ $order->customers()->first()->id }}">
<input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}">

<p>Order Date:  <input type="text" name="created_at" value="<?php echo !empty(old('created_at')) ? old('created_at') : '' ?>" style="width: 40%" placeholder="Leave blank for today's date" id="datepicker"></p>

<table class="table order-products">
<thead>
        <tr>
        <th>Image</th>
        <th>ID</th>
        <th>Product Name</th>
        <th>Qty</th>
        <th>On Hand</th>
        <th>Price</th>
        <th>Org. Price</th>
        <th>Serial</th>
        <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->products as $product)
        @if ($product->pivot->qty>0)
        <?php 
            if (count($product->images)) {
                $image = $product->images->first();
                $path = '/images/thumbs/'.$image->location;
                $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' title='$image->title' alt='$image->title' src='$path'></a>";
            } else {
                $image="/images/no-image.jpg";
                $path = "<a href='/$product->slug' target='_blank'><img style='width: 80px' src='$image'></a>";
            }
        ?>
        <tr>
            <td>{{$product->id}}</td>
            <td><?= $path ?></td>
            <td>
                <input style="width: 40px" type="hidden" name="id[]" value="{{ $product->id }}">
                <input type="text" class="form-control" name="product_name[]" value="{{ !$product->pivot->product_name ? $product->title : $product->pivot->product_name }}" />
            </td>
            <td><input style="width: 50px" type="text" class="form-control" name="qty[]" value="{{ $product->pivot->qty}}"></td>
            <td>{{$product->p_qty}}</td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control" type="text" name="price[]" value="<?= $product->pivot->price ?>">
                </div>
            </td>
            <td><span style='display:none'>{{number_format($product->p_price,0, '', '')}}</span></td>
            <td><input type="hidden" name="serial[]" value="{{ $product->pivot->serial }}" />{{ $product->pivot->serial }}</td>
            <!-- oninput="setCustomValidity('')" oninvalid="this.setCustomValidity('Please Enter valid a serial number')" -->
            <td>
                <button type="button" class="btn btn-danger deleteitem" aria-label="Left Align">
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                </button>
            </td>
        </tr>
        @endif
        @endforeach
        <tr>
            <td><input style="width: 40px" class="form-control product_id" type="text" name="id[]"></td>
            <td></td>
            <td><input type="text" class="form-control" name="product_name[]" /></td>
            <td><input style="width: 50px" type="text" class="form-control" name="qty[]"></td>
            <td></td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control" type="text" name="price[]">
                </div>
            </td>
            <td></td>
            <td><input class="form-control" type="text" name="serial[]" /></td>
            <!-- oninput="setCustomValidity('')" oninvalid="this.setCustomValidity('Please Enter valid a serial number')" -->
            <td>
                <button type="button" class="btn btn-danger deleteitem" aria-label="Left Align">
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                </button>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td style="text-align: right" colspan="8"><b>Discount</b></td>
            <td style="text-align: right"><input name='discount' value="0.00" id="discount-input" style="width: 100px; text-align: right;display:inline" class="form-control" /></td>
        </tr>        
        <tr>
            <td style="text-align: right" colspan="8"><b>Freight</b></td>
            <td style="text-align: right"><input name='freight' value="0.00" id="s_freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" /></td>
        </tr>
    </tfoot>    
</table>

<p>Purchased from:  
    <select class="form-control" name="purchased_from">
        <option value="1" {{ !empty($order->purchased_from) && $order->purchased_from==1 ? 'selected' : ''  }}>Swiss Made</option>
        <option value="2" {{ !empty($order->purchased_from) && $order->purchased_from==2 ? 'selected' : ''  }}>Signature Time</option>
    </select>
</p>
<div class="form-group row">
    <div class="col-6">
        <label for="po-input" class="col-form-label">PO Number</label>
            <input name="po" id="po-input" class="form-control" autocomplete="off" type="text" value="{{$order->po}}">
    </div>    
    <div class="col-6">
        <label for="payment-input" class="col-form-label">Payment Method</label>
        <input type="text" autocomplete='off' name='payment' id="payment-input" readonly class="form-control" value="Invoice">
        
        <label for="payment-options-name-input" class="col-form-label">Payment Options</label>
        <select class="form-control" id="payment-options-name-input" name="payment_options">
            @foreach (PaymentsOptions() as $value => $payment_option)
                <option value="{{ $value }}" <?php echo !empty($order->payment_options) && $order->payment_options==$value ? 'selected' : '' ?>>{{ $payment_option }}</option>
            @endforeach
        </select>
    </div>    
</div>

<div class="row">
    <div class="col-md-6 order-group billing" >
        <div class="group-title">Billing Address</div>
        <div class="p-1">
            <div class="form-group row firstname">
                <label for="b_firstname-input" class="col-3 col-form-label">First Name</label>
                <div class="col-9 input-group">
                <input autocomplete="off" id="b_firstname-input" class="typeahead form-control" name="b_firstname" type="text" value="{{$order->b_firstname}}">
                </div>
            </div>
            <div class="form-group row lastname">
                <label for="b_lastname-input" class="col-3 col-form-label">Last Name</label>
                <div class="col-9 input-group">
                <input autocomplete="off" id="b_lastname-input" class="form-control" name="b_lastname" type="text" value="{{$order->b_lastname}}">
                </div>
            </div>
            <div class="form-group row company">
                <label for="b_company-input" class="col-3 col-form-label">Company</label>
                <div class="col-9 input-group">
                <input autocomplete="off" id="b_company-input" class="form-control" name="b_company" type="text" value="{{$order->b_company}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="b_address1-input" class="col-3 col-form-label">Address 1</label>
                <div class="col-9 input-group">
                <input id="b_address1-input" class="form-control" name="b_address1" type="text" value="{{$order->b_address1}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="b_address2-input" class="col-3 col-form-label">Address 2</label>
                <div class="col-9 input-group">
                <input id="b_address2-input" class="form-control" name="b_address2" type="text" value="{{$order->b_address2}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="b_phone-input" class="col-3 col-form-label">Phone</label>
                <div class="col-9 input-group">
                <input id="b_phone-input" class="form-control" name="b_phone" type="text" value="{{$order->b_phone}}">
                </div>
            </div>        
            <div class="form-group row">
                <label for="b_country-input" class="col-3 col-form-label">Country</label>
                <div class="col-9 input-group">
                    @inject('countries','App\Libs\Countries')
                    <?php echo $countries->getAllCountries($order->b_country) ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="b_state-input" class="col-3 col-form-label">State</label>
                <div class="col-9 input-group">
                    <?php echo $countries->getAllStates($order->b_state) ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="b_city-input" class="col-3 col-form-label">City</label>
                <div class="col-9 input-group">
                    <input id="b_city-input" class="form-control" name="b_city" type="text" value="{{$order->b_city}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="b_zip-input" class="col-3 col-form-label">Zip Code</label>
                <div class="col-9 input-group">
                    <input id="b_zip-input" class="form-control" name="b_zip" type="text" value="{{$order->b_zip}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="b_email-input" class="col-3 col-form-label">Email</label>
                <div class="col-9 input-group">
                    <input id="b_email-input" class="form-control" name="email" type="text" value="email">
                </div>
            </div>
        </div>
    </div>

    <div class="order-group col-md-6 shipping">
        <div class="group-title">Shipping Address</div>
        <div class="p-1">
        <div class="form-group row">
                <label for="s_firstname-input" class="col-3 col-form-label">First Name</label>
                <div class="col-9 input-group">
                    <input id="s_firstname-input" class="typeahead1 form-control" name="s_firstname" type="text" value="{{$order->s_firstname}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_lastname-input" class="col-3 col-form-label">Last Name</label>
                <div class="col-9 input-group">
                    <input id="s_lastname-input" class="form-control" name="s_lastname" type="text" value="{{$order->s_lastname}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_company-input" class="col-3 col-form-label">Company</label>
                <div class="col-9 input-group">
                    <input id="s_company-input" class="form-control" name="s_company" type="text" value="{{$order->s_company}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_address1-input" class="col-3 col-form-label">Address 1</label>
                <div class="col-9 input-group">
                    <input id="s_address1-input" class="form-control" name="s_address1" type="text" value="{{$order->s_address1}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_address2-input" class="col-3 col-form-label">Address 2</label>
                <div class="col-9 input-group">
                    <input id="s_address2-input" class="form-control" name="s_address2" type="text" value="{{$order->s_address2}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_phone-input" class="col-3 col-form-label">Phone</label>
                <div class="col-9 input-group">
                    <input id="s_phone-input" class="form-control" name="s_phone" type="text" value="{{$order->s_phone}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_country-input" class="col-3 col-form-label">Country</label>
                <div class="col-9 input-group">
                <?php echo $countries->getAllCountries($order->s_country,'s_') ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="s_state-input" class="col-3 col-form-label">State</label>
                <div class="col-9 input-group">
                    <?php echo $countries->getAllStates($order->s_state,'s_') ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="s_city-input" class="col-3 col-form-label">City</label>
                <div class="col-9 input-group">
                    <input id="s_city-input" class="form-control" name="s_city" type="text" value="{{$order->s_city}}">
                </div>
            </div>
            <div class="form-group row">
                <label for="s_zip-input" class="col-3 col-form-label">Zip Code</label>
                <div class="col-9 input-group">
                    <input id="s_zip-input" class="form-control" name="s_zip" type="text" value="{{$order->s_zip}}">
                </div>
            </div>
        </div>
    </div>
</div>



<div class="form-group row">
<div class="col-12">
    <label for="comments-input" class="col-form-label">Comments</label>
    <textarea rows="5" style="width: 100%" id="comments-input" name="comments">{{ $order->comments }}</textarea>
</div>
</div>

<div class="clearfix"></div>
<button type="submit" class="btn btn-primary create">Transfer Now</button>

@include('admin.errors')
</form>

<div id="product-container"></div>
<div id="search-customer"></div>
@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/fancybox/jquery.fancybox.min.js')"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = 'memotransfer';
    var fromShipping=false;
    
    $(document).ready( function() {
        $( "#datepicker" ).datepicker();

        var getPath = "{{route('get.customer.info')}}";
        var mainPath = "{{route('get.customer.byID')}}";

        function fillInData(data) {
            $('#customer_id').val(data.id);
            for (name in data) {
                if (name != 'id') {
                    if (data[name]) {
                        $('#b_'+ name +'-input').val(data[name]);
                        $('#s-'+ name +'-input').val(data[name]);
                    } else {
                        $('#b_'+ name +'-input').val('');
                        $('#s-'+ name +'-input').val('');
                    }
                }
            }
        }
        
        $('input.typeahead').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            onSelect: function (suggestion) {
                //alert('You selected: ' + suggestion.value + ', ' + suggestion.data);

                $.ajax({
                    type: "GET",
                    url: getPath,
                    data: { 
                        _token: csrf_token,
                        _id: suggestion.data,
                        _searchBy: $(this).attr('id')
                    },
                    success: function (result) {
                        if (result) {
                            fillInData(result);
                        }
                    }
                })
            }
        });

        $(".additem").on('click', function(e) {
            e.preventDefault();
            $.ajax({
                type: "GET",
                url: "{{route('ajax.products')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                },
                success: function (result) {
                    $('#product-container').html(result.content+result.jquery);

                    $.fancybox.open({
                        src: "#product-container",
                        type: 'inline',
                        width: 980,
                    });
                }
            })
        });

        $('.fancybox-close-small').click( function () {
            $.fn.fancybox.close()
        })

        $('#b_country-input').change( function() {
            _this = $(this);
            $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
            .done (function (data) {
                $('#b_state-input').html(data);
            })
        })

        @include ('admin.countrystate')

        // Delete an unwanted item
        $(document).on('click','.deleteitem',function() {
            $(this).parents('tr').find('td').eq(2).find('input').val('-1')
           // $(this).parents('tr').hide();
            $(this).parents('tr').remove();
        })

        paymentOptions('Invoice');
        $('#payment-input').change( function () {
            paymentOptions(this.value);
        })

        function paymentOptions(method) {
            $('#payment-options-name-input option').each (function () {
                if (method=='Invoice') {
                    if (this.value=='None')
                        $(this).hide()
                    else $(this).show()
                } else {
                    if (this.value!='None') {
                        $(this).hide()
                        $('#payment-options-name-input option').eq(7).prop('selected','')
                    } else { 
                        $(this).show()
                        $('#payment-options-name-input option').eq(7).prop('selected','selected')
                    }
                }
            })
            if (method=='Invoice') 
                 $('#payment-options-name-input option').eq(0).prop('selected','selected')

        }

        $('.billing input').on('input propertychange', function() {
            id=$(this).attr('id');
            if (!$('#s'+id.substr(1)).val())
                $('#s'+id.substr(1)).val($(this).val());
        })

        $('.create').click (function (e) {
            if (!$('#customer_id').val() && $('#b_company-input').val().length > 0) {
                e.preventDefault();
                if (confirm("Customer doesn't exit. Would you like to create it?")) {
                    $.ajax({
                        type: "GET",
                        url: "{{route('save.customer')}}",
                        data: { 
                            _token: csrf_token,
                            _form: $('#orderForm').serialize()
                        },
                        success: function (result) {
                            $('#customer_id').val(result);
                            $('#orderForm').submit();
                        }
                    })
                }
            } 

            if ($('.order-products tr').length == 2) {
                $('html, body').animate({scrollTop : 0},600);
                $('.additem').tooltip('show');
                e.preventDefault();
            }

        })

        @include ('admin.products.productimport',['rows' => 4])

    })

</script>
@endsection