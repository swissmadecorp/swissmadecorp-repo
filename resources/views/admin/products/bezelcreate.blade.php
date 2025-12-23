@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')


{{  Form::open(array('action'=>array('ProductsController@storeBezel'),'id' => 'productform')) }}  
    
    <input type="hidden" value="" name="title">
    <input type="hidden" value="2" name="group_id">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Category Name *</label>
                <div class="col-9">
                    <select class="form-control" id="category" name="p_category"  required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" <?php echo !empty(old('p_category')) && old('p_category')==$category->id ? 'selected' : !empty($product) && $product->category_id==$category->id ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="category_selected" id="category_selected" />
                </div>
            </div>
            
            <div class="form-group row">
                <label for="condition-name-input" class="col-3 col-form-label">Condition</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_condition">
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty(old('p_condition')) && old('p_condition')==$key ? 'selected' : !empty($product->category_id) && $product->category_id==$category->id ? 'selected' : '' ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_reference')) ? old('p_reference') : '' ?>" type="text"  name="p_reference" id="reference-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="color-input" class="col-3 col-form-label">Bezel Color *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_color')) ? old('p_color') : '' ?>" type="text" name="p_color" id="color-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="gender-input" class="col-3 col-form-label">Gender</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_gender">
                        @foreach (Gender() as $key => $gender)
                        <option <?php echo !empty(old('p_gender')) && old('p_gender')==$key ? 'selected' : !empty($product->p_gender) && $product->category_id==$category->id ? 'selected' : '' ?> value="{{ $key }}">{{ $gender }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="form-group row">
                <label for="material-name-input" class="col-3 col-form-label">Material *</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_material" required>
                        @foreach (Materials() as $key => $material)
                        <option <?php echo !empty(old('p_material')) && old('p_material')==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="price-input" class="col-3 col-form-label">Cost *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty(old('p_price')) ? old('p_price') : '' ?>" type="text" name="p_price" id="price-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="retail-input" class="col-3 col-form-label">Retail *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty(old('p_retail')) ? old('p_retail') :  '0' ?>" type="text" name="p_retail" id="retail-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">Quantity *</label>
                <div class="col-9">
                    <input class="form-control" value="1" autocomplete="off" value="<?php echo !empty(old('p_qty')) ? old('p_qty') : '' ?>" type="text" name="p_qty" id="quantity-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="status-input" class="col-3 col-form-label">Status</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_status">
                        @foreach (Status() as $key => $status)
                        <option <?php echo !empty(old('p_status')) && old('p_status')==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>            
        </div> 

        <div class="col-md-6">    
            <div class="form-group row" style="border:1px solid #ccc;padding: 10px 0;background:#f5f5f5">
                <label for="p_diamond_cost-input" class="col-3 col-form-label">Diamond Cost</label>
                <div class="col-9" style="margin-bottom: 10px">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty(old('p_diamond_cost')) ? old('p_diamond_cost') : '' ?>" type="text" name="p_diamond_cost" id="p_diamond_cost-input">
                </div>
                <label for="ctw-input" class="col-3 col-form-label">TCW</label>
                <div class="col-3">
                    <input class="form-control" autocomplete="off" type="text" name="p_diamond_weight" id="tcw-input">
                </div>
                <label for="cost_carat-input" class="col-2 col-form-label">Cost</label>
                <div class="col-4">
                    <input class="form-control" autocomplete="off" type="text" id="cost_carat-input">
                </div>
            </div>
            <div class="form-group row" style="border:1px solid #ccc;padding: 10px 0;background:#f5f5f5">
                <label for="metal_cost-input" class="col-3 col-form-label">Metal Cost</label>
                <div class="col-9" style="margin-bottom: 10px">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty(old('p_metal_cost')) ? old('p_metal_cost') : '' ?>" type="text" name="p_metal_cost" id="metal_cost-input">
                </div>
                <label for="weight-input" class="col-3 col-form-label">Metal Weight</label>
                <div class="col-3">
                    <input class="form-control" autocomplete="off" name="p_metal_weight" value="<?php echo !empty(old('p_metal_weight')) ? old('p_metal_weight') : '' ?>" type="text" id="weight-input">
                </div>
                <label for="metal_karat_cost-input" class="col-2 col-form-label">Cost</label>
                <div class="col-4">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty(old('p_diamond_market_cost')) ? old('p_diamond_market_cost') : '' ?>" name="p_diamond_market_cost" type="text" id="metal_karat_cost-input">
                </div>
            </div> 
            <div class="form-group row">
                <label for="p_labor_cost-input" class="col-3 col-form-label">Labor Cost</label>
                <div class="col-9">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty(old('p_labor_cost')) ? old('p_labor_cost') : '' ?>" type="text" name="p_labor_cost" id="p_labor_cost-input">
                </div>
            </div>             
            <div class="form-group row supplier">
                <label for="supplier-input" class="col-3 col-form-label">Supplier</label>
                <div class="col-4">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty(old('supplier')) ? old('supplier') : '' ?>" type="text" name="supplier" id="supplier-input" required>
                </div>
                <label for="invoice-input" class="col-2 col-form-label">Invoice#</label>
                <div class="col-3">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty(old('supplier_invoice')) ? old('supplier_invoice') : '' ?>" type="text" name="supplier_invoice" id="invoice-input">
                </div>
            </div>    
            
            <div class="form-group row comments">
                <label for="comments-input" class="col-3 col-form-label">Comments</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" value="<?php echo !empty(old('p_comments')) ? old('p_comments') : '' ?>" type="text" name="p_comments" id="comments-input"></textarea>
                </div>
            </div>

            <div class="form-group row smalldescription">
                <label for="smalldescription-input" class="col-3 col-form-label">Small Description</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" value="<?php echo !empty(old('p_smalldescription')) ? old('p_smalldescription') : '' ?>" type="text" name="p_smalldescription" id="smalldescription-input"></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row multi-image-container">
    
        <div class="col-md-12 image-holder">
            <div class="form-group row">
                <label for="images-input" class="col-3 col-form-label">Images *</label>
                <div class="col-12">
                    <div id="dropzoneFileUpload" style="padding:62px 20px" class="dropzone" required></div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row snapshot">
        <label for="captureimage-input" class="col-3 col-form-label">Capture Image</label>
        <div class="col-12">
            <div class="row">
                <div class="col-6">
                    <div style="width: 278px" id="captureimage"></div>
                </div>
                <div class="col-6">
                    <div id="results"></div>
                </div>
            </div>
            <input type=button value="Activate Snapshot" onClick="activate_snapshot()" class="activeSnapshot">
            <input type=button value="Take Snapshot" id="takesnapshot" onClick="take_snapshot()">
        </div>
    </div>

    <button type="submit" class="btn btn-primary uploadPhoto">Save</button>

    @include('admin.errors')
{{  Form::close() }}  
    <div id="search-customer"></div>

@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/js/dropzone.js"></script>
<script src="/editable-select/jquery-editable-select.js"></script>

<script src="/js/webcam.min.js"></script>
<!-- Configure a few settings and attach camera -->
<script language="JavaScript">

    Webcam.set({
        width: 460,
        height: 345,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

</script>

<!-- Code to handle taking the snapshot and displaying it locally -->
<script language="JavaScript">
function activate_snapshot() {
    Webcam.attach( '#captureimage' );
    $('#takesnapshot').show();
    $('html, body').animate({scrollTop: $(document).height()}, 400);
}

function take_snapshot() {
    // take snapshot and get image data
    Webcam.snap( function(data_uri) {
        // display results in page
        var request = $.ajax({
            type: "POST",
            url: "{{action('DropzoneController@capturedImage')}}",
            data: { 
                _token: "{{csrf_token()}}",
                captured_image: data_uri,
                _form: $('#productform').serialize(),
            },
            success: function (result) {
              if (result.error == false) {
                //$('.snapshot').hide();
                if ($('.multi-image-container .col-md-6').length==0) {
                    $('.multi-image-container').prepend(result.content);
                    $('.image-holder').removeClass('col-md-12').addClass('col-md-6');
                } else {
                    $('.image-container').append(result.content2);
                }
                
                $('input[name=title]').val(result.title);
                
              } else
                alert (result.message);
            }
        })

    } );
}
</script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    $(document).ready( function() {
        Dropzone.autoDiscover = false;
        
        var myDropzone = new Dropzone("div#dropzoneFileUpload", {
            url: "{{action('DropzoneController@uploadFiles')}}",
            maxFilesize: 10, // MB
            maxFiles: 6,
            parallelUploads: 6,
            dictDefaultMessage:'Drop files here or click to upload manually',
            addRemoveLinks: true,
            autoProcessQueue:false,
            uploadMultiple: true,
            sending: function(file, xhr, formData) {
                $("form").find("input").each(function(){
                    if ($(this).attr("name") !==undefined && $(this).attr("name")!='_method')
                        formData.append($(this).attr("name"), $(this).val());
                });

                formData.append('title',$('input[name=title]').val());

                $("form").find("select").each(function(){
                    formData.append($(this).attr("name"), $(this).val());
                });

            }
        });

        $('#tcw-input,#cost_carat-input,#p_labor_cost-input,#p_diamond_cost-input').change( function () {
            calculateDiamondCost()
        })

        function calculateDiamondCost() {
            var twc=$('#tcw-input').val();
            var diamondCost = $('#cost_carat-input').val();

            if (twc && diamondCost) {
                totalDiamondCost=parseFloat(twc)*parseFloat(diamondCost);
                if (totalDiamondCost!=0) 
                    $('#p_diamond_cost-input').val(totalDiamondCost.toFixed(2));
            }

            if ($('#metal_cost-input').val() && $('#p_diamond_cost-input').val() && $('#p_labor_cost-input').val()) {
                var metal=parseFloat($('#metal_cost-input').val());
                var diamond=parseFloat($('#p_diamond_cost-input').val());
                var p_labor_cost = parseFloat($('#p_labor_cost-input').val())

                $('#price-input').val((metal+diamond+p_labor_cost).toFixed(2));
            }
        }

        $('#weight-input,#metal_karat_cost-input,#p_labor_cost-input,#metal_cost-input').change( function () {
            calculateMetalCost()
        })

        function calculateMetalCost() {
            var metalWeight=$('#weight-input').val();
            var metalCost = $('#metal_karat_cost-input').val();

            if (metalWeight && metalCost) {
                totalMetalCost=parseFloat(metalWeight)*parseFloat(metalCost);
                if(totalMetalCost!=0)
                    $('#metal_cost-input').val(totalMetalCost.toFixed(2));
            }

            if ($('#metal_cost-input').val() && $('#p_diamond_cost-input').val() && $('#p_labor_cost-input').val()) {
                var metal=parseFloat($('#metal_cost-input').val());
                var diamond=parseFloat($('#p_diamond_cost-input').val());
                var p_labor_cost = parseFloat($('#p_labor_cost-input').val())

                $('#price-input').val((metal+diamond+p_labor_cost).toFixed(2));
            }
        }


        myDropzone.on("successmultiple", function(file,resp){
            if(resp.message=="success"){
                //alert("Faild to upload image!");
                
                if (resp.message == "success") {
                    $('.snapshot').hide();
                    $(resp.content).insertAfter('.comments');
                    $('input[name=_filename]').val(resp.filename);
                    $('input[name=title]').val(resp.title);
                }

                $('#productform').submit();
            }
        });

        $('#category').editableSelect({ effects: 'fade' })
            .on('select.editable-select', function (e, li) {
                $('#category_selected').val(li.val()
            );
        });
       
        var mainPath = "{{action('CustomersController@ajaxCustomer')}}";

        $('#supplier-input').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            params:{addParam: 'justCompany'}
        });

        $(document).on('click','.delete-image', function() {
            var _this = $(this);
            var request = $.ajax({
                type: "POST",
                url: "{{action('DropzoneController@deleteImage')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    imageId: $(_this).next().attr('data-id'), 
                    id: $(_this).next().attr('data-pid'),
                    filename: $(_this).next().attr('src')
                },
                success: function (result) {
                    $(_this).parent('.image').remove();
                    if ($('.delete-image').length==0) {
                        $('.snapshot').show();
                        $('.multi-image-container').hide();
                    }

                }
            })

            request.fail( function (jqXHR, textStatus) {
                //alert ("Requeset failed: " + textStatus)
            })
        })

        $('.activeSnapshot').click( function (e) {
            e.preventDefault();
            $('.activeSnapshot').hide();
        })

        $(".uploadPhoto").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
            
            //if ($('.activeSnapshot').is(':visible'))
                myDropzone.processQueue();
        })
        
    })
</script>
@endsection