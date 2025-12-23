@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<form method="POST" action="{{route('orders.store')}}" accept-charset="UTF-8" id="orderForm">
    @csrf
    <input type="hidden" name="customer_id" id="customer_id">
    <input type="hidden" name="_blade" value="create">
    <input type="hidden" name="printAfterSave" id="printAfterSave" value="0">
    
    <div class="row">
        <div class="col">
            <label for="comments-input" class="col-form-label">Order Date</label>
            <input type="text" class="form-control" name="created_at" value="<?php echo !empty(old('created_at')) ? old('created_at') : '' ?>" placeholder="Leave blank for today's date" id="datepicker">
        </div>

        <div class="col">
            <label for="comments-input" class="col-form-label">Purchased From</label>
            <select class="form-control" name="purchased_from">
                <option value="1">Swiss Made</option>
                <option value="2">Signature Time</option>
            </select>
        </div>
    
    
        <div class="col">
            <label for="comments-input" class="col-form-label">Customer Group</label>
            <select class="form-control" name="cgroup">
                <option value="0">Dealer</option>
                <option value="1">Customer</option>
            </select>
        </div>
    </div>
    <hr>

    <table class="table order-products table-striped table-bordered">
    <thead>
        <tr>
        @if (!isMobile())
        <th>ID</th>
        <th>Image</th>
        <th>Product Name</th>
        <th>Qty</th>
        <th>On Hand</th>
        <th>Price</th>
        <th>Org. Price</th>
        <th>Serial</th>
        <th>Action</th>
        @else
        <th>Product(s)
        <!-- <button class="addnew"><i class="fa fa-file"></i></button> -->
        </th>
        @endif
        </tr>
    </thead>
    <tbody>
    <?php $totalRows=0; ?>
    @if (isset($products))
        @foreach ($products as $product)
        <?php
            if (count($product->images)) {
                $image = $product->images->first();
                $path = '/images/thumbs/'.$image->location;
                $path = "<img style='width: 80px' title='$image->title' alt='$image->title' src='$path'>";
            } else {
                $image="/images/no-image.jpg";
                $path = "<img style='width: 80px' src='$image'>";
            }

            $totalRows++;
        ?>
        <tr>
        <td><input style="width: 65px" value="{{$product->id}}" class="form-control product_id number" autofocus autocomplete="off" type="text" pattern="\d*" name="id[]"></td>
        <td><?=$path ?></td>
        <td>
            <input class="form-control" class="product_name" value="{{$product->title}}" name="product_name[]" type="text">
            <input type="hidden" class="p_retail">
        </td>
        <td><input class="form-control qtycalc" name="qty[]" value="{{$product->p_qty}}" type="number" pattern="\d*"></td>
        <td>{{$product->p_qty}}</td>
        <td>
            <div class="col-2 input-group">
                <div class="input-group-addon">$</div>
                <input style="width: 80px" class="form-control pricecalc" pattern="^\d*(\.\d{0,2})?$" name="price[]" type="text"></td>
            </div>
        <td><span style='display:none'>{{number_format($product->p_price,0, '', '')}}</span></td>
        <td><input style="width: 100px" class="form-control" value="{{$product->p_serial}}" name="serial[]" type="text"></td>
        <td><button type="button" style="text-align:center" class="btn btn-danger deleteitem" aria-label="Left Align">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        </button></td>
        </tr>
        @endforeach
        <tr>
            <td><input style="width: 65px" class="form-control product_id number" autofocus type="text" pattern="\d*" name="id[]"></td>
            <td></td>
            <td><input class="form-control" class="product_name" name="product_name[]" type="text">
            <input type="hidden" class="p_retail">
        </td>
            <td><input class="form-control qtycalc" name="qty[]" type="number" pattern="\d*"></td>
            <td></td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control pricecalc" pattern="^\d*(\.\d{0,2})?$" name="price[]" type="text"></td>
                </div>
            <td><span style='display:none'></span></td>
            <td><input style="width: 100px" class="form-control" name="serial[]" type="text"></td>
            <td><button type="button" style="text-align:center" class="btn btn-danger deleteitem" aria-label="Left Align">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        </button></td>
        </tr>
    @else                
        <tr>
            @if (!isMobile())
            <td><input style="width: 65px" class="form-control product_id number" autofocus type="text" pattern="\d*" name="id[]"></td>
            <td></td>
            <td><input class="form-control" class="product_name" name="product_name[]" type="text">
            <input type="hidden" class="p_retail">
        </td>
            <td><input class="form-control qtycalc" name="qty[]" type="number" pattern="\d*"></td>
            <td></td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control pricecalc" pattern="^\d*(\.\d{0,2})?$" name="price[]" type="text"></td>
                </div>
            <td><span style='display:none'></span></td>
            <td><input style="width: 100px" class="form-control" name="serial[]" type="text"></td>
            <td></td>
            @else
            <td>
                <div class="mobilizer">
                    <div class="row">
                        <div class="col-3">
                            <label for="id" >Product Id</label>
                            <input style="width: 65px" class="form-control product_id number" autofocus type="text" pattern="\d*" name="id[]">
                        </div>
                        <div class="col-3 img_containers" style="border: 1px solid #999;background:#ccc"><img src="" /></div>
                        <div class="col-6">
                            <label>Cost:</label>
                            <span class="form-control cost">0</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label for="product_name">Product Name</label>
                            <input class="form-control product_name" name="product_name[]" type="text">
                        </div>    
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label for="pricecalc" >Price</label>
                            <input class="form-control pricecalc" pattern="\d*" id="pricecalc" name="price[]" type="number">
                        </div>    
                        <div class="col-3">
                            <label for="qty" >Qty</label>
                            <input class="form-control qty" pattern="\d*" readonly name="qty[]" type="number">
                        </div> 
                        <div class="col-5">
                            <label for="serial">Serial</label>
                            <input class="form-control serial" name="serial[]" type="text">
                        </div> 
                    </div>
                </div>
            </td>
            
            @endif
        </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>@if (!isMobile())

            <td><b>Qty:</b> {{$totalRows}}</td>
            <td></td>
            <td colspan="2"><b>Profit:</b></td>
            <td colspan="2"><span class="hide" id="profit"></span></td>
            <td><b>Total:</td>
            <td><span id="total"></td>
            <td><button title="Copy prices" onkeypress="return event.keyCode != 13;" id="copyprices"><i class="fas fa-copy"></i></button></td>
            @else
            <td><div style="float:left"><b>Profit ($)</b></div><div id="profit" style="float:right"></div></td>
            @endif
        </tr>
    </tfoot>
</table>

<div style='float: right'>    
<b>Freight: &nbsp;</b>
<input name='freight' value="0.00" id="s_freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" />
</div>

    <div class="form-group row" style="clear: both" >
        <div class="col-6">
            <label for="po-input" class="col-form-label">PO Number</label>
            <input class="form-control" autocomplete="off" value="<?php echo !empty(old('po')) ? old('po') : '' ?>" type="text" name="po" id="po-input">
        </div>    
        <div class="col-6">
            <label for="payment-input" class="col-form-label">Payment Method</label>
            <select class="form-control" name="payment" id="payment-input">
                @foreach (Payments() as $value => $payment)
                    <option value="{{ $payment }}" <?php echo !empty(old('payment')) && old('payment')==$value ? 'selected' : '' ?>>{{ $payment }}</option>
                @endforeach
            </select>

            <label for="payment-options-name-input" class="col-form-label">Payment Options</label>
            <select class="form-control" id="payment-options-name-input" name="payment_options">
                @foreach (PaymentsOptions() as $value => $payment_option)
                    <option value="{{ $value }}" <?php echo !empty(old('payment_options')) && old('payment_options')==$value ? 'selected' : '' ?>>{{ $payment_option }}</option>
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
                    <div class="col-9">
                        <input class="typeahead form-control" autocomplete="off" value="<?php echo !empty(old('b_firstname')) ? old('b_firstname') : '' ?>" type="text" name="b_firstname" id="b_firstname-input">
                        
                    </div>
                </div>
                <div class="form-group row lastname">
                    <label for="b_lastname-input" class="col-3 col-form-label">Last Name</label>
                    <div class="col-9">
                        <input class="form-control" autocomplete="lastname" value="<?php echo !empty(old('b_lastname')) ? old('b_lastname') : '' ?>" type="text" name="b_lastname" id="b_lastname-input">
                    </div>
                </div>
                <div class="form-group row" style="position:relative">
                    <label for="b_company-input" class="col-3 col-form-label">Company</label>
                    <div class="col-9 input-group">
                        <input class="form-control company" autocomplete="company" value="<?php echo !empty(old('b_company')) ? old('b_company') : '' ?>" type="text" name="b_company" id="b_company-input" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_address1-input" class="col-3 col-form-label">Address 1</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('b_address1')) ? old('b_address1') : '' ?>" type="text" name="b_address1" id="b_address1-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_address2-input" class="col-3 col-form-label">Address 2</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('b_address2')) ? old('b_address2') : '' ?>" type="text"  name="b_address2" id="b_address2-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_phone-input" class="col-3 col-form-label">Phone</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('b_phone')) ? old('b_phone') : '' ?>" type="tel"  name="b_phone" id="b_phone-input">
                    </div>
                </div>                
                <div class="form-group row">
                    <label for="b_country-input" class="col-3 col-form-label">Country</label>
                    <div class="col-9">
                        @inject('countries','App\Libs\Countries')
                        <?php echo $countries->getAllCountries() ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_state-input" class="col-3 col-form-label">State</label>
                    <div class="col-9">
                        <?php echo $countries->getAllStates() ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_city-input" class="col-3 col-form-label">City</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('b_city')) ? old('b_city') : '' ?>" type="text" name="b_city" id="b_city-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_zip-input" class="col-3 col-form-label">Zip Code</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('b_zip')) ? old('b_zip') : '' ?>" type="text"  name="b_zip" id="b_zip-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b_email-input" class="col-3 col-form-label">Email</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('email')) ? old('email') : '' ?>" type="email"  name="email" id="b_email-input">
                    </div>
                </div>                
            </div>
        </div>

        <div class="order-group col-md-6 shipping">
            <div class="group-title">Shipping Address</div>
            <div class="p-1">
                <div class="form-group row">
                    <label for="s_firstname-input" class="col-3 col-form-label">First Name</label>
                    <div class="col-9">
                        <input class="typeahead1 form-control" value="<?php echo !empty(old('s_firstname')) ? old('s_firstname') : '' ?>" type="text" name="s_firstname" id="s_firstname-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_lastname-input" class="col-3 col-form-label">Last Name</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s_lastname')) ? old('s_lastname') : '' ?>" type="text" name="s_lastname" id="s_lastname-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_company-input" class="col-3 col-form-label">Company</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s_company')) ? old('s_company') : '' ?>" type="text" name="s_company" id="s_company-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_address-input" class="col-3 col-form-label">Address 1</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s_address')) ? old('s_address1') : '' ?>" type="text" name="s_address1" id="s_address1-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_address2-input" class="col-3 col-form-label">Address 2</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s_address2')) ? old('s_address2') : '' ?>" type="text"  name="s_address2" id="s_address2-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_phone-input" class="col-3 col-form-label">Phone</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s_phone')) ? old('s_phone') : '' ?>" type="text"  name="s_phone" id="s_phone-input">
                    </div>
                </div>                
                <div class="form-group row">
                    <label for="s_country-input" class="col-3 col-form-label">Country</label>
                    <div class="col-9">
                        <?php echo $countries->getAllCountries(0,'s_') ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_state-input" class="col-3 col-form-label">State</label>
                    <div class="col-9">
                        <?php echo $countries->getAllStates(0,'s_') ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_city-input" class="col-3 col-form-label">City</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s_city')) ? old('s_city') : '' ?>" type="text" name="s_city" id="s_city-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s_zip-input" class="col-3 col-form-label">Zip Code</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s_zip')) ? old('s_zip') : '' ?>" type="text"  name="s_zip" id="s_zip-input">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="form-group row">
        <div class="col-12">
            <label for="comments-input" class="col-form-label">Comments</label>
            <textarea rows="5" style="width: 100%" id="comments-input" name="comments"></textarea>
        </div>
    </div>

    <div class="alert-info clearfix">
        <button type="submit" class="btn btn-primary create" id="createOrder">Create Order</button>
    </div>

    @include('admin.errors')
    </form>

<div id="product-container"></div>
<div id="search-customer"></div>

@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = $('input[name=_blade]').val();
    var loadnext = false;
    var invoicedata;
    var loadNew;
    
    $(document).ready( function() {
        $( "#datepicker" ).datepicker();
        $(':input').on('focus',function(){
            $(this).attr('autocomplete', 'off');
        });
      
        function fillInData(data,exclude) {
            if (exclude == 'b_firstname-input')
                $('#customer_id').val(data.id);
                
            //localStorage.setItem("customer_id", data.id);
            var items = [];
            $('.order-products tr').each( function(index) {
                if (index > 0) {
                    if ($('td:eq(0)',this).find('input').val() != '') {
                        items.push($('td:eq(0)',this).find('input').val())
                    }
                }
            })
            invoicedata = {
                customerId: data.id,
                rows: $('.order-products tr').length,
                products: items
            }

            localStorage.setItem("invoicedata", JSON.stringify(invoicedata));
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
        
        var getPath = "{{route('get.customer.info')}}";
        var mainPath = "{{route('get.customer.byID')}}";

        $('input.typeahead,input.typeahead1').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            orientation: 'auto',

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

        $.extend($.ui.autocomplete.prototype.options, {
            open: function(event, ui) {
                $(this).autocomplete("widget").css({
                    "width": ($(this).width() + "px")
                });
            }
        });

        Number.prototype.formatMoney = function(c, d, t){
            var n = this, 
                c = isNaN(c = Math.abs(c)) ? 2 : c, 
                d = d == undefined ? "." : d, 
                t = t == undefined ? "," : t, 
                s = n < 0 ? "-" : "", 
                i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
                j = (j = i.length) > 3 ? j % 3 : 0;
        
            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
        };

        var calculateProfits = function () {
            var total=0,val = 0,val2=0;total2=0;
            $('.order-products tr').each( function (indx) {
                if (indx > 0 && $('.order-products tr').length-1 > indx) {
                    if (getDeviceType()=='tablet' || getDeviceType()=='mobile') {
                        val = $('td',this).find('.pricecalc').val()-$('td',this).find('.cost').text();
                    
                    } else {
                        val = $('td',this).eq(5).find('input').val()-$('td',this).eq(6).text();
                        val2 = $('td',this).eq(5).find('input').val()*$('td',this).eq(3).find('input').val();
                    }
                    total = total+val;
                    if (val2)
                        total2 = (total2+parseFloat(val2));
                }
            })

            $('#profit').text('$'+total.formatMoney(2, '.', ','));
            $('#total').text('$'+total2.formatMoney(2, '.', ','));
        }

        $(window).keydown(function(e) {
            if (e.keyCode === 13) {
                e.preventDefault()
            }   
        })

        $('#copyprices').click(function(e) {
            e.stopPropagation();
            e.preventDefault();

            $.confirm({
                title: 'Price copy',
                content: '' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>Enter the price amount you would like to duplicate on each product?' +
                '<input type="text" autofocus class="price_amount form-control" required />' +
                '</div>' +
                '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function () {
                            var price_amount = this.$content.find('.price_amount').val();
                            if(!price_amount){
                                $.alert('Provide a valid number.');
                                return false;
                            } else if (price_amount.charAt(0) == '0' || price_amount.charAt(0) == '.') {
                                $.alert('Enter number without zeros or decimal in front of digits.');
                                return false;
                            }
                         
                            $('.order-products tr').each(function(i,g) {
                                if (i>0) {
                                    td = $('td',$(this));
                                    if ($(td).eq(0).find('input').val()) {
                                        var retail = $(td).eq(2).find('input').eq(1).val();
                                        var new_price = price_amount

                                        if (new_price.indexOf('%')>-1) {
                                            if (retail) {
                                                percent = new_price.replace("%",'');
                                                price = retail - (retail * (percent/100))
                                                $(td).eq(5).find('input').val(Math.ceil(price));
                                            }
                                        } else {
                                            $(td).eq(5).find('input').val(new_price);
                                        }
                                    }
                                }
                            })
                            calculateProfits();
                        }
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });

        })

        $(document).on('blur','.pricecalc,.qtycalc', function() {
            var pr = $(this).parents('tr');
            td = $('td',pr);
            var retail = $(td).eq(2).find('input').eq(1).val();
            var percent = $(td).eq(5).find('input').val();
            var price = null;

            if (percent.indexOf('%')>-1) {
                percent = percent.replace("%",'');
                if (retail) {
                    price = retail - (retail * (percent/100))
                    $(td).eq(5).find('input').val(Math.ceil(price));
                }
            }
            calculateProfits();
        })
        
        @include ('admin.products.productimport',['rows' => 3])

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

        $('#b_country-input').change( function() {
            stateFromCountry($(this),$('#s_country-input').val());
        })

        @include ('admin.countrystate')

        if (custId=localStorage.getItem("customer_id")) {
            $('#customer_id').val(custId);
        }

        $(document).on('keypress', '.product_id', function(e) {
            if (e.which == 32){
                return false;
            }
        });
        
        $(document).on('click','.deleteitem',function() {
            $(this).parents('tr').remove();
            setTimeout( function () {
                $('.order-products tfoot').find('td:eq(0)').html('<b>Qty:</b> '+($('.order-products tr').length-2))
            },100)
            
        })

        $('.billing input,.billing select').on('input propertychange', function(e) {
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
            //$('#s_country-input').change()
            //if (!$('#s'+id.substr(1)).val() || e.currentTarget.tagName=="SELECT")
                
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
                // } else if (method=='Repair') {
                //     document.location.href = '/admin/repairs/create'
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
        
        orderCreate=false;

        // window.onbeforeunload = function(e) {
        //     if (orderCreate==false)
        //         return 'Dialog text here.';
        // };
        
        if (invoiceData=JSON.parse(localStorage.getItem("invoicedata"))) {
            if (!$('#b_company-input').val()) {
                localStorage.clear();
                return
            }

            $('#customer_id').val(invoiceData.customerId);

            for (var i=0;i < invoiceData.rows-4;i++) {
                tr_clone = $('.order-products tr:eq(1)').clone()
                $('.order-products tbody').after(tr_clone);
                tr = $('td:eq(0)',tr_clone).find('input');
                tr.val(invoiceData.products[i+1])
                tr.focus(); tr.blur();
            }

            firstRow = $('.order-products tr:eq(1)');
            firstRow = $('td:eq(0)',firstRow).find('input'); //.val(invoiceData.products[0]);
            firstRow.focus(); firstRow.blur(); 
        }

        $('#createOrder').click (function (e) {
            orderCreate=true;
            $(this).prop("disabled",true)
            $('.order-products tr').each( function(index) {
                if (index > 0) {
                    if (!$('td:eq(5)',this).find('input').val() || !$('td:eq(2)',this).find('input').val()) {
                        if (!$('td:eq(0)',this).find('input').val() && index>1) {
                            e.preventDefault();
                            orderCreate = true;
                            return false;
                        }
                        $('#createOrder').prop("disabled",false)
                        $.alert ('Product name or Price field cannot be left blank.')
                        $('html, body').animate({scrollTop : 0},600);
                        $('td:eq(5)',this).find('input').focus();
                        e.preventDefault();
                        orderCreate = false;
                        return false;
                    }
                }
            })

            if (orderCreate) {
                if ($('.order-products tr').length == 2) {
                    $('html, body').animate({scrollTop : 0},600);
                    $('.additem').tooltip('show');
                    $(this).prop("disabled",false)
                    e.preventDefault();
                    //orderCreate=false;
                    //return false;
                }
            }
            
            if (orderCreate) { 
                if (!$('#b_company-input').val()) {
                    $.alert ('Company field cannot be left blank.')
                    $('html, body').animate({scrollTop : 200},600);
                    $(this).prop("disabled",false)
                    return false;
                    orderCreate = false;
                }
            }


            if (orderCreate) {
                $.confirm({
                    content: "Would you like to print this invoice?",
                    buttons: {
                        print:  function() {
                            $('#printAfterSave').val('1');
                            $('form').submit();
                        },
                        no: function() {
                            $('form').submit();
                        },
                        cancel: function() {
                            
                        }
                    }
                })
            }

        })
    }) 

</script>
@endsection