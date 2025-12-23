@extends('layouts.admin-default')

@section ('content')

<form action="margins/store" method="POST" id="marginform">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <label for="search" class="col-2 col-form-label">Search</label>
                <div class="col-9">
                    <input type="text" name="search" id="search" />
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <select name="productleft" id="productleft" multiple id="">
            @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->id.' - '.$product->title}}</option>
            @endforeach
            </select> 

            <div class="form-group row">
                <label for="search" class="col-3 col-form-label">Total Products</label>
                <div class="col-9">
                    <span id="total"></span>
                </div>
            </div>

        </div>
        <div class="col-md-2">  
            <ul class="move-button">
                <li><input type="button" id="singleright" value=">" /></li>
                <li><input type="button" id="singleleft" value="<" /></li>
            </ul>
        </div>
        <div class="col-md-5">
            <select name="productright" id="productright" multiple id="">
            @foreach ($margins as $margin)
                <option value="{{ $margin->product_id }}" data-value="{{$margin->amount}}" data-name="{{$margin->margin}}">{{ $margin->product_id.' - '.$margin->title }} <?php echo $margin->margin=='Percent' ? "($margin->amount%)" : "($$margin->amount)" ?></option>
            @endforeach
            </select> 
            <div class="form-group row">
                <label for="margin-input" class="col-3 col-form-label">Amount</label>
                <div class="col-9 input-group">
                    <input type="text" class="form-control" style="width: 100%" name="margin-amount" id="margin-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="percent-input" class="col-3 col-form-label">Percent</label>
                <div class="col-2" style="padding-top: .8rem">
                    <input type="radio" value="Percent" name="marginamount" class="form-control" id="percent-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="amount-input" class="col-3 col-form-label">Amount</label>
                <div class="col-2" style="padding-top: .8rem">
                    <input type="radio" value="Amount" name="marginamount" class="form-control" id="amount-input">
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary submit">Update</button>
    <div class='message'></div>
    @include('admin.errors')
    
</form>

@endsection

@section ('jquery')
<script>
    
    var csrf_token = "{{csrf_token()}}";
    $(document).ready( function() {
        $('#singleright').click( function () {
            var options = [];
            var amount = ''; 
            var sign='';

            if ($('#margin-input').val()=='') {
                alert ("You didn't specified the amount.")
                return
            }
                
            if ($('#amount-input').is(":checked"))
                amount = '$';
            else sign='%';

            amount = amount+$('#margin-input').val();

            $('#productleft option:selected').each ( function (i,item) {
                $('#productright').append($('<option>',{
                    value:item.value,
                    text: item.text +' ('+amount+sign+')',
                    'data-id': "new"
                }))

                options.push (item.value)
            }).remove();

            var request = $.ajax({
                type: "POST",
                url: "{{action('MarginsController@ajaxStore')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    _form: $('#marginform').serialize(),
                    _options: options
                },
                success: function (result) {
                    postsuccessmessage('Transferred successfully.');
                    var scr = $('#productright')[0].scrollHeight;
                    $('#productright').animate({scrollTop: scr},2000);
                    $('#margin-input').val('')
                    ('#amount-input').prop('checked', false);
                    ('#percent-input').prop('checked', false);
                }
            })   
        })

        $('#singleleft').click( function () {
            var options = [];
            $('#productright option:selected').each ( function (i,item) {
                $('#productleft').prepend($('<option>',{
                    value:item.value,
                    text: item.text 
                }))
                options.push (item.value)
            }).remove();  

            var request = $.ajax({
                type: "POST",
                url: "{{action('MarginsController@ajaxDelete')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    _options: options
                },
                success: function (result) {
                    postsuccessmessage('Deleted successfully.');
                }
            })
        })

        $('#productright').on('change', function () {
            $('#margin-input').val($("option:selected",this).attr('data-value'))

            if ($("option:selected",this).attr('data-name')=='Percent')
                $('#percent-input').prop('checked','checked');
            else $('#amount-input').prop('checked','checked');
        })

        $('input').on('input change', function () {
            $('#productright option:selected').each ( function (i,item) {
                $(item).attr('data-id','update');
                val = item.text.replace(/ *\([^)]*\) */g, "");
                symbol = $('input[name=marginamount]:checked').val();

                if (symbol=='Percent')
                    item.text = val+' ('+$('#margin-input').val()+'%)';
                else item.text = val+' ($'+$('#margin-input').val()+')';

                $(item).attr('data-value',$('#margin-input').val());
                $(item).attr('data-name',symbol);
            })
        }) 
        
        $('#search').on('input', function () {
            var search = $(this).val();
            var total = 0;

            if (search.length<3) return

            $('#productleft option').each( function () {
                if (this.text.toLowerCase().indexOf(search)==-1)  {
                    $(this).hide();
                } else {
                    $(this).show();
                }

            })

            $('#productright option').each( function () {
                if (this.text.toLowerCase().indexOf(search)==-1)  {
                    $(this).hide();
                } else {
                    $(this).show();
                }

            })

           // $('#total').html(total);
        })

        if (isTablet()) {
            $('#productleft,#productright').prop('multiple','')
            $('#productleft,#productright').css('height','25px')
        }

        $('#search').on('keyup', function (e) {
            var search = $(this).val();
            var total = 0;

            if (e.keyCode!=8 && e.keyCode!=46) return

            if (search.length==0) {
                $('#productleft option').show();
                $('#productright option').show();
                //total = $('#productleft option').length;
            } else {
                $('#productleft option').each( function () {
                    if (this.text.toLowerCase().indexOf(search)>-1)  {
                        $(this).show();
                        //total++;
                    }
                })

                $('#productright option').each( function () {
                    if (this.text.toLowerCase().indexOf(search)>-1)  {
                        $(this).show();
                        //total++;
                    }
                })
            }

           // $('#total').html(total);
        })

        $('.submit').click(function() {
            var options = [];
            
            $('#productright option').each ( function (i,item) {
                if ($(item).attr('data-id'))
                    options.push (item.value)
            })
            
            if (options.length == 0) return false
            var request = $.ajax({
                type: "POST",
                url: "{{action('MarginsController@ajaxUpdate')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    _form: $('#marginform').serialize(),
                    _options: options
                },
                success: function (result) {
                    if (result=='success') {
                        $('#productright option').each(function(i,item){
                            if ($(item).attr('data-id'))
                                ($(item).attr('data-id',''))
                        });  

                        $('#marginform')[0].reset();
                        postsuccessmessage('Update successfully.');
                    } else alert ('Something went wrong')
                }
            })        
        })

        var postsuccessmessage = function (message) {
            $('.message').text(message);
            $('.message').fadeIn().show();
                setTimeout( function () {
                    $('.message').fadeOut(1000);
            },5000)
        }
    })
</script>
@endsection