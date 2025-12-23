@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
<link href="/multiselect/chosen.min.css" rel="stylesheet">
@endsection

@section ('content')

{{ Form::open(array('route'=>array('repairs.store'), 'id' => 'formRepair')) }}
    <input type="hidden" name="customer_id" id="customer_id">
    <input type="hidden" name="_blade" value="create">

    <p>Order Date:  
        <input type="text" class="form-control" name="created_at" value="<?php echo !empty(old('created_at')) ? old('created_at') : '' ?>" placeholder="Leave blank for today's date" id="datepicker">
    </p>

    <p>Assigned To:  
        <select class="form-control" name="assigned_to">
            <option value="0">Michael</option>
            <option value="1">Zalman</option>
            <option value="2">Rami</option>
            <option value="3">Chronostore</option>
            <option value="4">Gilmen</option>
            <option value="5">Simcha Barayev</option>
        </select>
    </p>

    <table class="table table-striped table-bordered hover repair-products">
    <thead>
        <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Product Name</th>
        <th>Cost</th>
        <th>Charge Amt.</th>
        <th>Serial</th>
        <th>Jobs</th>
        <th>Instructions</th>
        <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width: 100px"><input class="form-control product_id" autofocus type="text" name="id[]"></td>
            <td><input type=button value="Activate Snapshot" class="activeSnapshot btn btn-primary btn-sm">
                <input type=button value="Take Snapshot" class="takesnapshot btn btn-success btn-sm" >
                <div class="captureimage_0"></div>
                <input type="hidden" name="filename[]">
            </td>
            <td><input class="form-control" class="product_name" name="product_name[]" type="text"></td>
            <td>
                <div class="col-1 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control cost" name="cost[]" type="text" value="0"></td>
                </div>
            </td>
            <td>
                <div class="col-1 input-group">
                    <div class="input-group-addon">$</div>
                    <input style="width: 80px" class="form-control pricecalc" name="price[]" pattern="\d*" type="text" value="0">
                </div>
            </td>
            <td><input style="width: 100px" class="form-control" name="serial[]" type="text"></td>
            <td>
                <select data-placeholder="Choose a job ..." class="chosen-select" name="jobs_1[]" multiple>
                  <option>Overhaul/Clean</option>
                  <option>Staff</option>
                  <option>Stem &amp; Crown</option>
                  <option>Crystal</option>
                  <option>Polish</option>
                  <option>Hands</option>
                  <option>Rusty</option>
                  <option>Dial Refurbish</option>
                  <option>Battery</option>
                  <option>Gasket</option>
                  <option>Coil</option>
                  <option>Mainspring</option>
                  <option>Circuit</option>
                  <option>Factory</option>
                  <option>Estimate</option>
                  <option>Guarantee</option>
                </select>
            </td>
            <td><input class="form-control" name="instructions[]" type="text"></td>
            <td style="text-align: center">
                <button type="button" style="text-align:center" class="btn btn-primary btn-sm newrow" aria-label="Left Align">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
                <button type="button" style="text-align:center" class="btn btn-danger btn-sm deleteitem" aria-label="Left Align">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                </button>
            </td>
        </tr>
    </tbody>

</table>
<hr>
<div style='float: right'>    
<b>Freight: &nbsp;</b>
<input name='freight' value="0.00" id="s-freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" />
</div>

<br style="clear: both"><br>
<div class="p-1">
    <div class="form-group row firstname">
        <label for="firstname-input" class="col-3 col-form-label">First Name</label>
        <div class="col-9">
            <input class="form-control" autocomplete="off" value="<?php echo !empty(old('firstname')) ? old('firstname') : '' ?>" type="text" name="firstname" id="firstname-input">
            
        </div>
    </div>
    <div class="form-group row lastname">
        <label for="lastname-input" class="col-3 col-form-label">Last Name</label>
        <div class="col-9">
            <input class="form-control" autocomplete="off" value="<?php echo !empty(old('lastname')) ? old('lastname') : '' ?>" type="text" name="lastname" id="lastname-input">
        </div>
    </div>
    <div class="form-group row" style="position:relative">
        <label for="company-input" class="col-3 col-form-label">Company</label>
        <div class="col-9 input-group">
            <input class="form-control company" autocomplete="off" value="<?php echo !empty(old('company')) ? old('company') : '' ?>" type="text" name="company" id="company-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="address1-input" class="col-3 col-form-label">Address 1</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('address1')) ? old('address1') : '' ?>" type="text" name="address1" id="address1-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="phone-input" class="col-3 col-form-label">Phone</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('phone')) ? old('phone') : '' ?>" type="text"  name="phone" id="phone-input">
        </div>
    </div>                
    <div class="form-group row">
        <label for="email-input" class="col-3 col-form-label">Email</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('email')) ? old('email') : '' ?>" type="text"  name="email" id="email-input">
        </div>
    </div>                
</div>

<div class="clearfix"></div>

<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Internal Comments</label>
        <textarea rows="5" style="width: 100%" id="comments-input" name="comments"></textarea>
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="customer_comments-input" class="col-form-label">Customer Comments</label>
        <textarea rows="5" style="width: 100%" id="customer_comments-input" name="customer_comments"></textarea>
    </div>
</div>

<button type="submit" class="btn btn-primary create">Create Ticket</button>

    @include('admin.errors')
{{ Form::close() }}

<div id="product-container"></div>
<div id="search-customer"></div>

@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/js/webcam.min.js"></script>
<script src="/fancybox/jquery.fancybox.min.js"></script>
<script src="/multiselect/chosen.jquery.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>

@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = $('input[name=_blade]').val();
    var loadnext = false; 

    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

    $(document).ready( function() {
        $( "#datepicker" ).datepicker();

    $(document).on('click','.activeSnapshot', function () {
        var row=$(this).parents('tr');
        div = row.find('td:eq(1)').find('div');
        Webcam.attach( '.'+div.attr('class') );

        $(this).hide();
        row.find('td:eq(1)').find('.takesnapshot').show();
    })

    $(document).on('click','.takesnapshot', function() {
        // take snapshot and get image data

        var row=$(this).parents('tr');
        Webcam.snap( function(data_uri) {
            // display results in page
            
            var product_name=row.find('td:eq(2)').find('input').val();
            if (product_name=='') {
                alert ('Please enter Product Name first.')
                return;
            }

            var request = $.ajax({
                type: "POST",
                url: "{{route('capture.image')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    captured_image: data_uri,
                    title: product_name,
                    blade: 'repair'
                },
                success: function (result) {
                    if (result.error == false) {
                        row.find('td:eq(1)').find('div').hide();
                        row.find('td:eq(1)').find('.takesnapshot').hide();
                        
                        row.find('td:eq(1)').append('<img />');
                        newimg = row.find('td:eq(1)').find('img')
                        newimg.attr('src','/public/images/'+result.filename)
                        newimg.css('width','80px');
                        row.find('td:eq(1)').find('input').val(result.filename);
                    } else
                        alert (result.message);
                }
            })

        } );
    })

        var getPath = "{{route('get.customer.info')}}";
        var mainPath = "{{route('get.customer.byID')}}";

        function fillInData(data) {
            $('#customer_id').val(data.id);
            for (name in data) {
                if (name != 'id') {
                    if (data[name]) {
                        $('#'+ name +'-input').val(data[name]);
                    } else {
                        $('#'+ name +'-input').val('');
                    }   
                }
            }
        }
        
        $('#firstname-input').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            onSelect: function (suggestion) {
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

        $(document).on('click', '.newrow', function () {
            $(this).remove();
            createEmptyRow()
        })

        // $(document).on('keyup','.chosen-search-input', function(evt) {
        //     if (evt.keyCode==13)
        //     {
        //         evt.preventDefault();
        //         $(this).parents('td').find('select').append('<option>' + $(evt.target).val() + '</option>');
        //         $(this).trigger('chosen:updated');
        //         //this.result_highlight = this.search_results.find('li.active-result').lastrn 
        //         //this.result_select(evt);
        //     }
        // })

        $(document).on('blur', '.product_id', function(e) {
            $('.additem').tooltip('dispose');
            e.preventDefault();
            
            if ($(this).val() != '') {
                _this = $(this);
                $.ajax({
                    type: "GET",
                    async: false,
                    url: "{{route('find.product')}}",
                    data: { _token: csrf_token,id: $(this).val() },
                    success: function (result) {
                        if (result.error==1){
                            ran=true;
                            loadnext=false;
                            $('.product_name').focus();
                            alert ('Product not found');
                        } else {
                            loadnext=true;
                            var pr = $(_this).parents('tr');
                            td = $('td',pr)

                            $(td).eq(1).children().remove();
                            $(td).eq(1).append(result.image)
                            hidden=$(td).eq(1).append('<input type="hidden" name="filename[]">')
                            $(td).eq(1).find(':hidden').val(result.filename);

                            $(td).eq(2).find('input').val(result.product_name)
                            $(td).eq(5).find('input').val(result.serial)
                            
                            $(td).eq(4).find('input').attr({
                                'oninput': "setCustomValidity('')", 
                                'oninvalid': "this.setCustomValidity('Please enter charge amount')",
                                'required': 'required'
                            })

                            $(td).eq(6).find('select').attr('name','jobs_'+pr.length+'[]')
                            initChosen()
                        }
                    }
                })

                if (loadnext == true) {
                    if ($(this).parents('tr').index() == $('.repair-products tr').length-2 && $(this).val() != '') {
                        createEmptyRow()
                    }
                }
            }
        })

        function createEmptyRow() {

            $.ajax({
                type: "GET",
                url: "{{route('new.invoice.row')}}",
                data: { _token: csrf_token, _blade:'repair', num: $('.repair-products tr').length},
                success: function (result) {
                    $('.repair-products tr').eq($('#table tr').length - 1).after(result);
                    initChosen()
                    var pr = $('.repair-products tr').eq($('#table tr').length-1);
                    td = $('td',pr)
                    $(td).eq(6).find('select').attr('name','jobs_'+($('.repair-products tr').length-1)+'[]')
                    $(td).eq(0).find('text').focus();

                    pr = $('.repair-products tr').eq($('#table tr').length-2)
                    td = $('td',pr)
                    $(td).eq(8).find('.newrow').remove();
                }
            })
        }

        $('#country-input').change( function() {
            _this = $(this);
            $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
            .done (function (data) {
                $('#state-input').html(data);
            })
        })

        @include ('admin.countrystate')

        $(document).on('click','.deleteitem',function() {
            $(this).parents('tr').remove();
        })

        $('.billing input,.billing select').on('input propertychange', function(e) {
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
            //if (!$('#s'+id.substr(1)).val() || e.currentTarget.tagName=="SELECT")
                
        })

        var config = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : { allow_single_deselect: true },
            '.chosen-select-no-single' : { disable_search_threshold: 10 },
            '.chosen-select-no-results': { no_results_text: 'Oops, nothing found!' },
            '.chosen-select-rtl'       : { rtl: true },
            '.chosen-select-width'     : { width: '95%' },
            'no_results_text'          : "No result found. Press enter to add "
        }

        initChosen()
        function initChosen() {
            for (var selector in config) {
                $(selector).chosen(config[selector]);
            }
        }

        $(".chosen-container .chosen-drop").css({
            "width" : "200px",
            "right" : 0
        })
    }) 

</script>
@endsection