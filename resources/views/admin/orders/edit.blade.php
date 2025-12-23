@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
<?php $customerGroup = $order->customers->first()->cgroup ?>
<p><b>Purchase Date: {{ $order->created_at->format('m/d/Y') }}</b></p>
<form method="POST" action="{{route('orders.update',[$order->id])}}" accept-charset="UTF-8" id="productform">
    @csrf
    @method('PATCH')
<input type="hidden" name="customer_id" id="customer_id" value="{{ $order->customers()->first()->id }}">
<input type="hidden" id="order_id" value="{{$order->id}}">
<p>Purchased from:  
    <select class="form-control" name="purchased_from">
        <option value="1" {{ !empty($order->purchased_from) && $order->purchased_from==1 ? 'selected' : ''  }}>Swiss Made</option>
        <option value="2" {{ !empty($order->purchased_from) && $order->purchased_from==2 ? 'selected' : ''  }}>Signature Time</option>
    </select>
</p>
<div class="form-group row">
    <div class="col-6">
        <label for="po-input" class="col-form-label">PO Number</label>
        <input name="po" id="po-input" class="form-control" type="text" value="{{$order->po}}">
    </div>    
    <div class="col-6">
        <label for="payment-input" class="col-form-label">Payment Method</label>
        <select class="form-control" name="method" id="payment-input">
            @foreach (Payments() as $value => $payment)
                <option value="{{ $payment }}" <?php echo !empty($order->method) && $order->method==$payment ? 'selected' : '' ?>>{{ $payment }}</option>
            @endforeach
        </select>

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
                    <input id="b_email-input" class="form-control" name="email" type="text" value="{{$order->email}}">
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
                {!! $countries->getAllCountries($order->s_country,'s_') !!}
                </div>
            </div>
            <div class="form-group row">
                <label for="s_state-input" class="col-3 col-form-label">State</label>
                <div class="col-9 input-group">
                    {!! $countries->getAllStates($order->s_state,'s_') !!}
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
    <?php 
        $tracking = $order->tracking;
        $chronoId = $order->chrono_order_id;
     ?>
<table class="table order-products table-striped table-bordered">
    <thead>
        <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Product Name</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Cost</th>
        <th>Serial#</th>
        <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->products as $order   )
        <?php 
            $p_image = $order  ->images->toArray();
            if (!empty($p_image)) {
                $image=$p_image[0]['location'];
            } else $image = '../no-image.jpg';
        ?>
        <tr>
            <td>{{$order   ->id}}</td>
            <td>
                <img style="width: 70px" src="{{ '/images/thumbs/'.$image }}" />
                <input type="hidden" value="{{$order   ->pivot->op_id}}" name="op_id[]" />
                <input type="hidden" value="{{$order   ->id}}" name="product_id[]" />
            </td>
            <td  style="width:28%"><input type="text" class="form-control" name="product_name[]" value="{{ !$order ->pivot->product_name ? $order   ->title : $order   ->pivot->product_name }}" /> </td>
            <td><input type="text" class="form-control" style="width: 50px" value="{{ $order   ->pivot->qty }}" name="qty[]" /></td>
            <td style="text-align: right">
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="width:70px" type="text" value="{{ $order->pivot->price }}" name="price[]"></input>
                </div>
            </td>
            <td style="text-align: right; width: 92px"><span class="hide">{{ $order->pivot->cost>0 ? number_format($order  ->pivot->cost,2) : number_format($order   ->p_price,2) }}</span></td>
            <td style="text-align: right"><input type="hidden" name="serial[]" value="{{ $order->pivot->serial }}" />{{ $order ->pivot->serial }}</td>
            <td style="width: 30px;text-align: center">
                <a class="btn btn-danger delete nonsubmit" data-id="{{$order   ->pivot->op_id}}" data-pid="{{$order   ->pivot->product_id}}"><i class="fa fa-trash" aria-hidden="true"></i></a>
            </td>
        </tr>
        @endforeach
        <tr>
            <td><input style="width: 65px" class="form-control product_id number" autofocus type="text" pattern="\d*" name="id[]"></td>
            <td></td>
            <td><input class="form-control" class="product_name" name="product_name[]" type="text"></td>
            <td>
                <input type="hidden" value="0" name="op_id[]" />
                <input type="hidden" name="product_id[]" />
                <input class="form-control qtycalc" style="width: 50px" name="qty[]" type="number" pattern="\d*">
            </td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 70px" class="form-control pricecalc" pattern="^\d*(\.\d{0,2})?$" name="price[]" type="text"></td>
                </div>
            <td><span style='display:none'></span></td>
            <td><input style="width: 100px" class="form-control" name="serial[]" type="text"></td>
            <td style="text-align: center"><button type="button" style="text-align:center" class="btn btn-danger deleteitem" aria-label="Left Align">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                </button></td>
        </tr>
    </tbody>
    <tfoot>
        
        @if ($customerGroup==1)
        <tr>
            <td style="text-align: left;" colspan="5">
                <b>Tax Exemption</b>
                <input type="checkbox" name="taxexempt" <?= $order->taxexempt ? 'checked' : '' ?> class="checkbox" style="width: 30px">
            </td>
            <td style="text-align: right" colspan="2"><b>Tax</b></td>
            <td style="text-align: right"><input type="text" value="{{ $order->taxable }}" id="taxable" style="width: 100px; text-align: right;display:inline" class="form-control" name="taxable" /></td>
        </tr>
        @endif
        <tr>
            <td style="text-align: right" colspan="7"><b>Sub Total</b></td>
            <td style="text-align: right">{{ number_format($order->subtotal,2) }}</td>
        </tr>
        <tr>
            <td style="text-align: right" colspan="7"><b>Discount</b></td>
            <td style="text-align: right"><input name='discount' id="discount-input" style="width: 100px; text-align: right;display:inline" class="form-control" value="{{ $order->discount }}" /></td>
        </tr>        
        <tr>
            <td style="text-align: right" colspan="7"><b>Freight</b></td>
            <td style="text-align: right"><input name='freight' id="s_freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" value="{{ number_format($order->freight,2) }}" /></td>
        </tr>        
        <tr>
            <td style="text-align: right" colspan="7"><b>Grand Total</b></td>
            <td style="text-align: right"><input type="text" value="{{number_format($order->total,0,'','')}}" id="grand_total"></td>
        </tr>
                  
    </tfoot>
</table>

<div class="form-group row">
    <div class="col-12">
        <label for="tracking-input" class="col-form-label">Tracking No.</label>
        <input name="tracking" id="tracking-input" class="form-control" type="text" value="{{ $tracking }}">
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="chrono-order-id-input" class="col-form-label">Chono24 Order Id.</label>
            <input name="chrono_order_id" id="chrono-order-id-input" class="form-control" type="text" value="{{$chronoId}}">
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Comments</label>
        <textarea type="text" name="comments" rows="5" id="comments-input" class="form-control">{{ $order->comments }}</textarea>
    </div>    
</div>

@include('admin.errors')

<div class="alert-info clearfix">
    <button type="submit" class="float-right btn btn-danger update">Update Order</button>
</div>
</form>
<div id="product-container"></div>
<div id="search-customer"></div>
@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/fancybox/jquery.fancybox.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = 'edit';
    
    $(document).ready( function() {
        @include ('admin.countrystate')

        var getPath = "{{route('get.customer.info')}}";
        var mainPath = "{{route('get.customer.byID')}}";

        function fillInData(data,exclude) {
            if (exclude == 'b_firstname-input')
                $('#customer_id').val(data.id);
            
            for (name in data) {
                if (name != 'id') {
                    if (data[name]) {
                        if (exclude == 'b_firstname-input')
                            $('#b_'+ name +'-input').val(data[name]);
                        $('#s_'+ name +'-input').val(data[name]);
                    } else {
                        if (exclude == 'b_firstname-input')
                            $('#b_'+ name +'-input').val('');
                        $('#s_'+ name +'-input').val('');
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
                var el = this.id;
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
                            fillInData(result,el);
                        }
                    }
                })
            }
        });

        $('#grand_total').keyup( function(even) {
            var kc, num, rt = false;
            kc = event.keyCode;
            if(kc == 8 || ((kc > 47 && kc < 58) || (kc > 95 && kc < 106))) {
                $('.order-products tr').each( function(index, value) {
                    if (index != 0 && $('td:eq(4)',this).find('input').val()) {
                        amount = parseFloat($('td:eq(4)',this).find('input').val());
                        total = parseFloat($('#grand_total').val());

                        price = total / 1.08875;
                        $('td:eq(4)',this).find('input').val(price.formatMoney(2, '.', ''));
                        $('#taxable').val((total-price).formatMoney(2, '.', ''));
                    }
                })
            }
        })

        $('input.typeahead1').devbridgeAutocomplete({
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
                            fillInData(result,1);
                        }
                    }
                })
            }
        });

        stateFromCountry('#b_country-input',"{{$order->b_state}}");
        stateFromCountry('#s_country-input',"{{$order->s_state}}");

        function stateFromCountry(_this,stateId) {
            $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
            .done (function (data) {
                if ($(_this).attr('id') == 'b_country-input') {
                    $('#b_state-input').html(data);
                    $('#b_state-input').val(stateId)
                    $('#s_state-input').html(data);
                    $('#s_state-input').val(stateId)
                } else {
                    $('#s_state-input').html(data);
                    $('#s_state-input').val(stateId)
                }   

                
            })
        }

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
        
        $('#b_country-input').change( function() {
            stateFromCountry($(this),$('#s_country-input').val());
        })

        $('.billing input').on('input propertychange', function() {
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
        })

        $(document).on('keypress', '.product_id', function(e) {
            if (e.which == 32){
                return false;
            }
        });
        
        $(document).on('blur', '.product_id', function(e) {
            $('.additem').tooltip('dispose');
            e.preventDefault();
            
            if ($(this).val() != '') {
                _this = $(this);
                var is_onmemo = '';

                $.ajax({
                    type: "GET",
                    async: false,
                    url: "{{route('find.product')}}",
                    data: { _token: csrf_token,id: $(this).val() },
                    success: function (result) {
                        if (result.error==1){
                            $(_this).parents('tr').find('td:eq(2)').find('input').focus()
                            $.alert ('Product not found');
                        } else {
                            var pr = $(_this).parents('tr');
                            td = $('td',pr);
                            loadNew = true;
                            if (result.onhand==0) {
                                $.confirm({
                                    content: "Item is <b>Out of Stock</b>. Do you still want to add it?",
                                    buttons: {
                                        yes:  function() {},
                                        no: function() {
                                            loadNew = false;
                                            pr.remove();
                                        }
                                    }
                                })
                            } else if (result.status == 1) {
                                $.alert ('Product is currently On Memo');
                                loadNew = false;
                                is_onmemo = 1;
                            } else if (result.status==2) {
                                $.confirm({
                                    content: "This item is On Hold for <b style='color:red'>" + result.reservedFor + "</b>. Do you still want to add it?",
                                    buttons: {
                                        yes:  function() {
                                            
                                        },
                                        no: function() {
                                            loadNew = false;
                                            pr.remove();
                                        }
                                    }
                                })
                            }


                            if (getDeviceType()=='desktop') {
                                $(td).eq(1).children().remove();
                                $(td).eq(1).append(result.image)
                                
                                $(td).eq(2).find('input').val(result.product_name)
                                $(td).eq(3).find('input:nth-child(2)').val($(_this).val())
                                $(td).eq(3).find('input:nth-child(3)').val(1) 
                                if (result.price==0)
                                    $(td).eq(5).html('<input type="text" value="'+result.price+'" class="form-control" style="width: 60px" name="newcost[]">')
                                else {
                                    $(td).eq(5).html('<span style="display: none;"></span>');
                                    $(td).eq(5).find('span').text(result.price)
                                }
                                $(td).eq(6).find('input').val(result.serial)
                                $(td).eq(4).find('input').attr({
                                    'oninput': "setCustomValidity('')", 
                                    'oninvalid': "this.setCustomValidity('Please enter a price amount')",
                                    'required': 'required'
                                })
                            } else {
                                mobilized = _this.parents('.mobilizer');
                                if ($('.img_containers',mobilized).children().length==1)
                                    $('.img_containers',mobilized).children().remove();
                                
                                $('.img_containers',mobilized).append(result.image);
                                $('.img_containers img',mobilized).css('width','100%')
                                $('.product_name',mobilized).val(result.product_name)
                                $('.qty',mobilized).val(result.onhand)
                                $('.cost',mobilized).text(result.price)
                                $('.serial',mobilized).val(result.serial)
                            }

                            //calculateProfits();
                            //$('.order-products tfoot').find('td:eq(0)').html('<b>Qty:</b> '+($('.order-products tr').length-2))
                        }
                    }
                })

                if (loadNew==true) {
                    var mobile = getDeviceType();
                    if ($(this).parents('tr').index() == $('.order-products tr').length-6 && $(this).val() != '') {
                        $.ajax({
                            type: "GET",
                            url: "{{route('new.invoice.row')}}",
                            data: { _token: csrf_token, _blade: 'invoice_edit', isMobile:mobile },
                            success: function (result) {
                                $('.order-products tr').eq($('#table tr').length - 5).after(result);
                                $('.order-products tr').eq($('#table tr').length - 5).find('td:eq(0)').find('input').focus()
                            }
                        })
                    }
                } else {
                    if (is_onmemo) {
                        var pr = $(_this).parents('tr');
                        td = $('td',pr);
                        $(td[0]).find('input').val('');
                        $(td[1]).find('img').remove();
                        $(td[2]).find('input').val('');
                        $(td[3]).find('input').val('');
                        $(td[4]).text('');
                        $(td[5]).find('input').val('');
                        $(td[7]).find('input').val('');
                    }
                }
            }
        })
        
        $(".addnew").on('click', function(e) {
            e.preventDefault();
            var mobile = getDeviceType();
            $.ajax({
                type: "GET",
                url: "{{route('new.invoice.row')}}",
                data: { 
                    _token: csrf_token,
                    _blade: 'invoice_edit', 
                    isMobile:mobile 
                },
                success: function (result) {
                    $('.order-products tr').eq($('.order-products tr').length-6).after(result);
                    $('.order-products tr').eq($('.order-products tr').length-6).find('td:eq(0)').find('input').focus()

                    // $.fancybox.open({
                    //     src: "#product-container",
                    //     type: 'inline',
                    //     width: 980,
                    // });
                }
            })
        });

        $('.fancybox-close-small').click( function () {
            $.fn.fancybox.close()
        })

        $('.billing input, .billing select').on('input propertychange', function(e) { 
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
            //$('#s_country-input').change()
            //if (!$('#s'+id.substr(1)).val() || e.currentTarget.tagName=="SELECT")
                
        })
        
        paymentOptions('{{$order->method}}');
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

        <?php if (!isMobile()) {?>
            $('.order-products tbody').on('mouseenter', 'td', function () {
                if ($(this).index()==5)
                    $(this).find('span').show()
            }).on('mouseleave', 'td', function () {
                if ($(this).index()==5)
                    $(this).find('span').hide()
            })
        <?php } ?>

        $(".delete").on('click', function(e) {
            _this = $(this);
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this item from the order?')) return

            $.ajax({
                type: "GET",
                url: "{{route('destroy.product')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    orderid: $("#order_id").val(),
                    productid: _this.attr('data-pid'),
                    opid: _this.attr('data-id')
                },
                success: function (result) {
                    if (result=='success') {
                        //location.reload(true);
                        $(_this).parents('tr').remove();
                    } else 
                        alert('There were some errors while deleting this product.')
                }
            })
        });
        
        $(document).on('click','.deleteitem', function(e) {
            e.preventDefault();
            $(this).parents('tr').remove();
        })

    })

</script>
@endsection