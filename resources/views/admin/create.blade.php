@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
<link href="{{ asset(production().'fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
@endsection

@section ('content')

{{ Form::open(array('action'=>'OrdersController@store', 'id' => 'orderForm')) }}
    <input type="hidden" name="customer_id" id="customer_id">
    <input type="hidden" name="_blade" value="create">

    <p>Order Date:  
        <input type="text" class="form-control" name="created_at" value="<?php echo !empty(old('created_at')) ? old('created_at') : '' ?>" placeholder="Leave blank for today's date" id="datepicker">
    </p>
    <p>Purchased from:  
        <select class="form-control" name="purchased_from">
            <option value="1">Swiss Made</option>
            <option value="2">Signature Time</option>
        </select>
    </p>
    
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
                $path = production().'images/thumbs/'.$image->location;
                $path = "<img style='width: 80px' title='$image->title' alt='$image->title' src='$path'>";
            } else {
                $image=production()."images/no-image.jpg";
                $path = "<img style='width: 80px' src='$image'>";
            }

            $totalRows++;
        ?>
        <tr>
        <td><input style="width: 65px" value="{{$product->id}}" class="form-control product_id number" autofocus type="text" pattern="\d*" name="id[]"></td>
        <td><?=$path ?></td>
        <td><input class="form-control" class="product_name" value="{{$product->title}}" name="product_name[]" type="text"></td>
        <td><input class="form-control qtycalc" name="qty[]" value="{{$product->p_qty}}" type="number" pattern="\d*"></td>
        <td>{{$product->p_qty}}</td>
        <td>
            <div class="col-2 input-group">
                <div class="input-group-addon">$</div>
                <input style="width: 80px" class="form-control pricecalc" pattern="\d*" name="price[]" type="text"></td>
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
            <td><input class="form-control" class="product_name" name="product_name[]" type="text"></td>
            <td><input class="form-control qtycalc" name="qty[]" type="number" pattern="\d*"></td>
            <td></td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control pricecalc" pattern="\d*" name="price[]" type="text"></td>
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
            <td><input class="form-control" class="product_name" name="product_name[]" type="text"></td>
            <td><input class="form-control qtycalc" name="qty[]" type="number" pattern="\d*"></td>
            <td></td>
            <td>
                <div class="col-2 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control pricecalc" pattern="\d*" name="price[]" type="text"></td>
                </div>
            <td><span style='display:none'></span></td>
            <td><input style="width: 100px" class="form-control" name="serial[]" type="text"></td>
            <td></td>
            @else
            <td>
                <div class="mobilizer">
                    <div class="row">
                        <div class="col-4">
                            <label for="id" >Product Id</label>
                            <input style="width: 65px" class="form-control product_id number" autofocus type="text" pattern="\d*" name="id[]">
                        </div>
                        <div class="col-4 img_containers" style="border: 1px solid #999;background:#ccc"><img src="" /></div>
                        <div class="col-4">
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
            <td colspan="2"><span id="total"></td>
            @else
            <td><div style="float:left"><b>Profit ($)</b></div><div id="profit" style="float:right"></div></td>
            @endif
        </tr>
    </tfoot>
</table>

<div style='float: right'>    
<b>Freight: &nbsp;</b>
<input name='freight' value="0.00" id="s-freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" />
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
                    <label for="b-firstname-input" class="col-3 col-form-label">First Name</label>
                    <div class="col-9">
                        <input class="form-control" autocomplete="firstname" value="<?php echo !empty(old('b-firstname')) ? old('b-firstname') : '' ?>" type="text" name="b-firstname" id="b-firstname-input">
                        
                    </div>
                </div>
                <div class="form-group row lastname">
                    <label for="b-lastname-input" class="col-3 col-form-label">Last Name</label>
                    <div class="col-9">
                        <input class="form-control" autocomplete="lastname" value="<?php echo !empty(old('b-lastname')) ? old('b-lastname') : '' ?>" type="text" name="b-lastname" id="b-lastname-input">
                    </div>
                </div>
                <div class="form-group row" style="position:relative">
                    <label for="b-company-input" class="col-3 col-form-label">Company</label>
                    <div class="col-9 input-group">
                        <input class="form-control company" autocomplete="company" value="<?php echo !empty(old('b-company')) ? old('b-company') : '' ?>" type="text" name="b-company" id="b-company-input" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-address1-input" class="col-3 col-form-label">Address 1</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('b-address1')) ? old('b-address1') : '' ?>" type="text" name="b-address1" id="b-address1-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-address2-input" class="col-3 col-form-label">Address 2</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('b-address2')) ? old('b-address2') : '' ?>" type="text"  name="b-address2" id="b-address2-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-phone-input" class="col-3 col-form-label">Phone</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('b-phone')) ? old('b-phone') : '' ?>" type="tel"  name="b-phone" id="b-phone-input">
                    </div>
                </div>                
                <div class="form-group row">
                    <label for="b-country-input" class="col-3 col-form-label">Country</label>
                    <div class="col-9">
                        @inject('countries','App\Libs\Countries')
                        <?php echo $countries->getAllCountries() ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-state-input" class="col-3 col-form-label">State</label>
                    <div class="col-9">
                        <?php echo $countries->getAllStates() ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-city-input" class="col-3 col-form-label">City</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('b-city')) ? old('b-city') : '' ?>" type="text" name="b-city" id="b-city-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-zip-input" class="col-3 col-form-label">Zip Code</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('b-zip')) ? old('b-zip') : '' ?>" type="text"  name="b-zip" id="b-zip-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="b-email-input" class="col-3 col-form-label">Email</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('email')) ? old('email') : '' ?>" type="email"  name="email" id="b-email-input">
                    </div>
                </div>                
            </div>
        </div>

        <div class="order-group col-md-6 shipping">
            <div class="group-title">Shipping Address</div>
            <div class="p-1">
                <div class="form-group row">
                    <label for="s-firstname-input" class="col-3 col-form-label">First Name</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s-firstname')) ? old('s-firstname') : '' ?>" type="text" name="s-firstname" id="s-firstname-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-lastname-input" class="col-3 col-form-label">Last Name</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s-lastname')) ? old('s-lastname') : '' ?>" type="text" name="s-lastname" id="s-lastname-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-company-input" class="col-3 col-form-label">Company</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s-company')) ? old('s-company') : '' ?>" type="text" name="s-company" id="s-company-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-address-input" class="col-3 col-form-label">Address 1</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s-address')) ? old('s-address1') : '' ?>" type="text" name="s-address1" id="s-address1-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-address2-input" class="col-3 col-form-label">Address 2</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s-address2')) ? old('s-address2') : '' ?>" type="text"  name="s-address2" id="s-address2-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-phone-input" class="col-3 col-form-label">Phone</label>
                    <div class="col-9 input-group">
                        <input class="form-control" value="<?php echo !empty(old('s-phone')) ? old('s-phone') : '' ?>" type="text"  name="s-phone" id="s-phone-input">
                    </div>
                </div>                
                <div class="form-group row">
                    <label for="s-country-input" class="col-3 col-form-label">Country</label>
                    <div class="col-9">
                        <?php echo $countries->getAllCountries(0,'s-') ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-state-input" class="col-3 col-form-label">State</label>
                    <div class="col-9">
                        <?php echo $countries->getAllStates(0,'s-') ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-city-input" class="col-3 col-form-label">City</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s-city')) ? old('s-city') : '' ?>" type="text" name="s-city" id="s-city-input">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="s-zip-input" class="col-3 col-form-label">Zip Code</label>
                    <div class="col-9">
                        <input class="form-control" value="<?php echo !empty(old('s-zip')) ? old('s-zip') : '' ?>" type="text"  name="s-zip" id="s-zip-input">
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

    <button type="submit" class="btn btn-primary create">Create Order</button>

    @include('admin.errors')
{{ Form::close() }}

<div id="product-container"></div>
<div id="search-customer"></div>

@endsection

@section ('footer')
<script src="{{ asset(production().'fancybox/jquery.fancybox.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = $('input[name=_blade]').val();
    var loadnext = false;
    var invoicedata;

    $(document).ready( function() {
        $( "#datepicker" ).datepicker();
        function fillInData(data) {
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
                    $('#orderForm input').each ( function () {
                        if (data[name]) {
                            $('#b-'+ name +'-input').val(data[name]);
                            $('#s-'+ name +'-input').val(data[name]);
                        }
                        return false;
                    })
                }
            }
        }

        var getPath = "{{action('CustomersController@ajaxgetCustomer')}}";
        var mainPath = "{{action('CustomersController@ajaxCustomer')}}";
        
        $('#b-firstname-input').dropdown({
            // default is fullname so no need to specify
            getPath: getPath,
            mainPath: mainPath,
            success: function(data) {
                fillInData(data)
            }
        });
        
        $('#b-company-input').dropdown({
            getPath: getPath,
            mainPath: mainPath,
            searchBy: 'company',
            success: function(data) {
                fillInData(data)
            }
        });

        $('#b-lastname-input').dropdown({
            getPath: getPath,
            mainPath: mainPath,
            searchBy: 'lastname',
            success: function(data) {
                fillInData(data)
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
                    if (isMobile()) {
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

        $(document).on('blur','.pricecalc,.qtycalc', function() {
            calculateProfits();
        })
        
        $(document).on('blur', '.product_id', function(e) {
            $('.additem').tooltip('dispose');
            e.preventDefault();
            
            if ($(this).val() != '') {
                _this = $(this);
                $.ajax({
                    type: "GET",
                    async: false,
                    url: "{{action('ProductsController@ajaxFindProduct')}}",
                    data: { _token: csrf_token,id: $(this).val() },
                    success: function (result) {
                        if (result.error==1){
                            $(_this).parents('tr').find('td:eq(2)').find('input').focus()
                            alert ('Product not found');
                        } else {
                            var pr = $(_this).parents('tr');
                            td = $('td',pr);
                            loadNew = true;
                            if (result.onhand==0) {
                                if ( !confirm('Item is out of stock. Do you still want to add it?') ) {
                                    loadNew = false;
                                    return false;
                                }
                            } else if (result.status!=0) {
                                if ( !confirm('This item is Reserved or On Hold. Do you still want to add it?') ) {
                                    loadNew = false;
                                    return false;
                                }
                            }


                            if (!isMobile()) {
                                $(td).eq(1).children().remove();
                                $(td).eq(1).append(result.image)
                                
                                $(td).eq(2).find('input').val(result.product_name)
                                $(td).eq(3).find('input').val(1)
                                $(td).eq(4).text(result.onhand)
                                if (result.price==0)
                                    $(td).eq(6).html('<input type="text" value="'+result.price+'" class="form-control" style="width: 60px" name="newcost[]">')
                                else    
                                    $(td).eq(6).find('span').text(result.price)

                                $(td).eq(7).find('input').val(result.serial)
                                $(td).eq(5).find('input').attr({
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

                            calculateProfits();
                            $('.order-products tfoot').find('td:eq(0)').html('<b>Qty:</b> '+($('.order-products tr').length-2))
                        }
                    }
                })

                if (loadNew==true) {
                    var mobile = isMobile();
                    if ($(this).parents('tr').index() == $('.order-products tr').length-3 && $(this).val() != '') {
                        $.ajax({
                            type: "GET",
                            url: "{{action('ProductsController@ajaxCreateEmptyRowForInvoice')}}",
                            data: { _token: csrf_token, _blade: 'invoice', isMobile:mobile },
                            success: function (result) {
                                $('.order-products tr').eq($('#table tr').length - 2).after(result);
                                $('.order-products tr').eq($('#table tr').length - 2).find('td:eq(0)').find('input').focus()
                            }
                        })
                    }
                }
            }
        })

        $('#b-country-input').change( function() {
            _this = $(this);
            $.get("{{ action('CountriesController@getStateFromCountry')}}",{id: $(_this).val()})
            .done (function (data) {
                $('#b-state-input').html(data);
            })
        })

        @include ('admin.countrystate')

        if (custId=localStorage.getItem("customer_id")) {
            $('#customer_id').val(custId);
        }

        $(document).on('click','.deleteitem',function() {
            $(this).parents('tr').remove();
            setTimeout( function () {
                $('.order-products tfoot').find('td:eq(0)').html('<b>Qty:</b> '+($('.order-products tr').length-2))
            },100)
            
        })

        $('.billing input,.billing select').on('input propertychange', function(e) {
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
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
                } else if (method=='Repair') {
                    document.location.href = '/admin/repairs/create'
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

        $('.order-products tfoot,.order-products tbody').on('mouseenter', 'td', function () {
            if ($(this).index()==3 || $(this).index()==6)
                $(this).find('span').show()
        }).on('mouseleave', 'td', function () {
            if ($(this).index()==3 || $(this).index()==6)
                $(this).find('span').hide()
        })

        
        orderCreate=false;

        window.onbeforeunload = function(e) {
            if (orderCreate==false)
                return 'Dialog text here.';
        };
        
        if (invoiceData=JSON.parse(localStorage.getItem("invoicedata"))) {
            if (!$('#b-company-input').val()) {
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

        $('.create').click (function (e) {
            // if (!$('#customer_id').val() && $('#b-company-input').val().length > 0) {
            //     e.preventDefault();
            //     if (confirm("Customer doesn't exit. Would you like to create it?")) {
            //         $.ajax({
            //             type: "GET",
            //             url: "{{action('OrdersController@ajaxSaveCustomer')}}",
            //             data: { 
            //                 _token: csrf_token,
            //                 _form: $('#orderForm').serialize()
            //             },
            //             success: function (result) {
            //                 $('#customer_id').val(result);
            //                 $('#orderForm').submit();
            //             }
            //         })
            //     }
            // } 

            $('.order-products tr').each( function(index) {
                if (index > 0) {
                    if ($('td:eq(0)',this).find('input').val() != '' && $('td:eq(5)',this).find('input').val()=='') {
                        alert ('Price field cannot be empty.')
                        $('html, body').animate({scrollTop : 0},600);
                        $('td:eq(5)',this).find('input').focus();
                        e.preventDefault();    
                    }
                }
            })

            orderCreate=true;
            if ($('.order-products tr').length == 2) {
                $('html, body').animate({scrollTop : 0},600);
                $('.additem').tooltip('show');
                e.preventDefault();
            }

        })
    }) 

</script>
@endsection