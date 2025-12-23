function populateProductFields(el) {
if ($(el).val() != '') {
_this = $(el);
var is_onmemo = '';

$.ajax({
    type: "GET",
    async: false,
    url: "{{route('find.product')}}",
    data: { _token: csrf_token,id: $(el).val() },
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
                //var $th = $(_this).closest('table').find('th')
                $(td).eq(1).children().remove();
                $(td).eq(1).append(result.image)
                
                $(td).eq(2).find('input').eq(0).val(result.product_name)
                $(td).eq(2).find('input').eq(1).val(result.retail)
                $(td).eq(3).find('input').val(1)
                $(td).eq(4).text(result.onhand)
                //if (result.price==0)
                //    $(td).eq(6).html('<input type="text" value="'+result.price+'" class="form-control" style="width: 60px" name="newcost[]">')
                //else {
                    $(td).eq(6).html('<span style="display: none;"></span>');
                    $(td).eq(6).find('span').text(result.price)
                //}
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

            m = "{{Route::current()->getName()}}"
            if (m != 'memo.transfer') {
                calculateProfits();
                $('.order-products tfoot').find('td:eq(0)').html('<b>Qty:</b> '+($('.order-products tr').length-2))
            }
        }
    }
})

        if (loadNew==true) {
            var mobile = getDeviceType();
            var rows = {{$rows}}
            if ($(el).parents('tr').index() == $('.order-products tr').length-rows && $(el).val() != '') {
                $.ajax({
                    type: "GET",
                    url: "{{route('new.invoice.row')}}",
                    data: { _token: csrf_token, _blade: 'invoice', isMobile:mobile },
                    success: function (result) {
                        $('.order-products tr').eq($('#table tr').length - rows+1).after(result);
                        $('.order-products tr').eq($('#table tr').length - rows+1).find('td:eq(0)').find('input').focus()
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
}

$(document).on('blur', '.product_id', function(e) {
    $('.additem').tooltip('dispose');
    e.preventDefault();
    
    populateProductFields(this)
})

$('.order-products tfoot,.order-products tbody').on('mouseenter', 'td', function () {
    if ($(this).index()==3 || $(this).index()==6)
        $(this).find('span').show()
}).on('mouseleave', 'td', function () {
    if ($(this).index()==3 || $(this).index()==6)
        $(this).find('span').hide()
})