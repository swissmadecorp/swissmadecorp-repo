@extends('layouts.admin-default')

@section ('content')

<div class="alert-info clearfix" style="padding: 3px">
        <div class="float-right">
            <button type="submit" id="print" class="btn btn-primary">Print</button>
            
        </div>
        </div>
    <hr>

<div class="row">
    <div class="col-md-6">
        <div class="form-group row">
            <label for="search" class="col-2 col-form-label">Add</label>
            <div class="col-9 input-group">
                <input class="form-control" autocomplete="search" type="text" autofocus name="search" id="search" type="number" />
                <button type="button" style="text-align:center" class="btn btn-primary btn-sm addrow" aria-label="Left Align">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group row">
            <label for="remove_search" class="col-2 col-form-label">Remove</label>
            <div class="col-9 input-group">
                <input class="form-control" type="text" autocomplete="remove_search" autofocus name="search" id="remove_search" type="number" />
                <button type="button" style="text-align:center" class="btn btn-primary btn-sm removerow" aria-label="Left Align">
                    <i class="fa fa-minus" aria-hidden="true"></i>
                </button>
            </div>    
        </div>
    </div>    
</div>

<table id="products" class="table table-striped table-bordered" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th><a href='#' style="color: #dedede;cursor:default">Image</a></th>
            <th>Id</th>
            <th>Name</th>
            <th>Serial#</th>
            <th>Cost</th>
        </tr>
    </thead>

    <?php $grandTotal = 0; $qty=0?>
    
    <tbody>
        @foreach ($theshow as $atshow)
        
        <?php 
            $product = $atshow->product;
            $grandTotal += ($product->p_price);
            if ($product->images->first()) {
                $img = "/images/thumbs/".$product->images->first()->location;
            } else $img = "/images/no-image.jpg";
            
            $qty++;
         ?>
        
            <tr>
                <td class="text-center" style="width: 80px">
                    <img style="width: 70px" title="{{ $product->title }}" alt="{{ $product->title }}" src="{{$img}}">
                </td>
                <td>{{ $product->id }}</td>
                <td>{{ $product->title}}</td>
                <td>{{ $product->p_serial }}</td>
                <td class="text-right">${{ number_format($product->p_price,0) }}</td>
            </tr>
        @endforeach
    </tbody>
    
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td class="text-right" style="font-weight:bold">Total</td>
            <td class="text-center">{{$qty}}</td>
            <td class="text-right">${{ number_format($grandTotal,2) }}</td>
        </tr>
    </tfoot>
</table>

<div id="container_confirmation" style="display: none;height:100px;">
    <div class="form-group">
        <div class="form-group row confirmation" style="width: 570px">
            <div class="image ml-4 col-4"></div>
            <div class="col-7 description">
                <div id="product_id"><b>ID:</b>&nbsp;<span></span></div>
                <div id="product_name"><b>Name:</b>&nbsp;<span></span></div>
                <div id="serial"><b>Serial:</b>&nbsp;<span></span></div>
                <div id="cost"><b>Cost:</b>&nbsp;$<span></span></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section ('jquery')
<script>
    
    var csrf_token = "{{csrf_token()}}";
    $(document).ready( function() {
        
        // $body = $("body");

        // $(document).on({
        //     ajaxStart: function() { $body.addClass("loading"); },
        //     ajaxStop: function() { $body.removeClass("loading"); }
        // });
        
        $('.addrow').click( function() {
            var _this=$('#search');
            showConfirmation(_this,true)
        })
        
        $('.removerow').click( function() {
            var _this=$('#remove_search');
            showConfirmation(_this,false)
        })

        $('#search').keypress(function (e) {
            var _this=$('#search');
            if (_this.val()!='' && e.keyCode == 13 ) {
                showConfirmation(_this,true)
            }
        })
        
        var addProductToTable = function (criteria) {
            $.ajax({
                type: 'get',
                async: false,
                url: "{{route('show.add')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    id: criteria,
                },
                success: function (result) {
                    if (result.error=='') { 
                        $('#products > tbody').prepend(result.content)
                        var amount=0,qty=0,footer=$('#products').find('tfoot');ifoot=$('#products tr').length-2;
                        $('#products tr').each( function(i){
                            if (i>0 && i < ifoot) 
                                amount+=parseInt($('td:eq(4)',this).text().replace(/\s|\$|\,/g,''));
                            
                        })
                        amount+=parseFloat(result.cost);
                        footer.find('td:eq(4)').text('$'+amount.formatMoney(2, '.', ','));
                        qtyfooter = footer.find('td:eq(3)');
                        qty = qtyfooter.text();
                        qtyfooter.text(parseInt(qty)+parseInt(result.qty));
                    } else {
                        alert (result.error)
                    }
                }
            })
        }

        function showConfirmation(el,addEl) {
            var criteria = $(el).val();
            var previousColor,tr

            $.confirm({
                title: 'Product Confirmation',
                content: function () {
                    var self = this;
                    return $.ajax({
                        url: "{{ route('find.product') }}",
                        data: {id: criteria}
                    }).done(function (response) {
                        if (response.onhand == 0 || response.error == 1) {
                            self.setTitle('Error');
                            criteria = 0;
                            if (response.error == 1)
                                self.setContent('Please enter the product id.');
                            else self.setContent('Item is out of stock.');
                            return false;
                        }

                        var foundin = $('*:contains("'+criteria+'")').filter(function(){
                            return $(this).text() === criteria ? true : false;
                        });

                        if (addEl == true) {
                            if (foundin.length) {
                                self.setTitle('Error');
                                criteria = 0;
                                self.buttons.formSubmit.hide();
                                self.setContent('Item has already been added to the list');
                                return false;
                            }
                        } else {
                            if (!foundin.length) {
                                self.setTitle('Error');
                                criteria = 0;
                                self.buttons.formSubmit.hide();
                                self.setContent('Item is not in the current list');
                                return false;
                            }
                        }
                        
                        $('.confirmation .image').html(response.image.replace('/thumbs',''));
                        $('.description #product_id span').html(response.product_id)
                        $('.description #product_name span').html(response.product_name)
                        $('.description #serial span').html(response.serial)
                        $('.description #cost span').html(parseInt(response.price).formatMoney(2, '.', ','))
                        $('.confirmation img').css('width','auto')
                        $('.description span').css('display', 'inline-block')
                        self.setContent($('#container_confirmation').html())
                        
                        // self.setTitle(response.name);
                    }).fail(function(){
                        $(el).val('');
                        $(el).focus();
                        self.setTitle('Error');
                        self.buttons.formSubmit.hide();
                        self.setContent("Item doesn't exist in the database");
                    });
                },
                theme: 'white',
                type: 'blue',
                useBootstrap: false,
                escapeKey: 'cancel',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        keys: ['enter'],
                        action: function () {
                            if (criteria != 0) {
                                if (addEl) 
                                    addProductToTable(criteria);
                                else removeProductFromTable(criteria)

                                $('.description #product_id span').html('')
                                $(el).val('');
                                setTimeout( function () {
                                    $(el).focus();
                                },1300);
                            }

                            $(el).focus();
                        }
                    },
                    cancel: function () {
                        $(el).val('');
                        $(el).focus();
                        $('.description #product_id span').html('')
                        if (criteria != 0) {
                            $(tr).css('backgroundColor',previousColor);
                        }
                    },
                },
            })
        }

        $('#print').click( function () {
            window.location.href='theshow/print';
        })

        var removeProductFromTable = function(criteria) {
            setTimeout( function () {
                $.ajax({
                    type: 'post',
                    async: false,
                    url: "{{route('show.delete')}}",
                    data: { id: criteria },
                    success: function (result) {
                        if (result.error=='') { 
                            var amount=0,qty=0,footer=$('#products').find('tfoot');ifoot=$('#products tr').length-2;
                            
                            $('#products tr').each( function(i){
                                if ($(this).find('td:eq(1)').text()==criteria)
                                    $(this).remove();
                            })
                            
                            $('#products tr').each( function(i){
                                if (i>0 && i < ifoot) {
                                    amount+=parseInt($('td:eq(4)',this).text().replace(/\s|\$|\,/g,''));
                                }
                            })
                            
                            footer.find('td:eq(4)').text('$'+amount.formatMoney(2, '.', ','));
                            qtyfooter = footer.find('td:eq(3)');
                            qty = qtyfooter.text();
                            qtyfooter.text(parseInt(qty)-parseInt(result.qty));
                        } else 
                            alert (result.error)
                    }
                })
            },300)  
        }

        $('#remove_search').keypress(function (e) {
            var _this=$('#remove_search');
            if (_this.val()!='' && e.keyCode == 13 ) {
                showConfirmation(_this,false) 
            }
        })
        
    })
</script>

@endsection