@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')

<?php 
$isDuplicate=false;
if ( strpos(request()->route()->getActionName(),'duplicate') > 0 ) {
    $isDuplicate = true;  
} else 
    $product='';
    $arr=array('route'=>array('products.store'),'id' => 'productform');
?>

{{  Form::open($arr) }}  
    @if (!empty($product))
    <input type="hidden" value="{{$product->categories->category_name. ' ' . $product->p_model. ' ' . $product->p_reference}}" name="title">
    <input type="hidden" value="{{$product->id}}" name="_id">
    @else
    <input type="hidden" value="" name="title">
    @endif
    
    <input type="hidden" value="1" name="group_id">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Category Name *</label>
                <div class="col-9">
                    <select class="form-control" id="category" name="p_category" required>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" <?php echo (!empty(old('p_category')) && old('p_category')==$category->id ? 'selected' : !empty($product) && $product->category_id==$category->id) ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="category_selected" id="category_selected" />
                </div>
            </div>
            <div class="form-group row">
                <label for="type-input" class="col-3 col-form-label">Type</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="jewelry_type">
                        @foreach (JewelryType() as $key => $type)
                        <option <?php echo !empty(old('jewelry_type')) && old('jewelry_type')==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>            
            <div class="form-group row">
                <label for="condition-name-input" class="col-3 col-form-label">Condition</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_condition">
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty(old('p_condition')) && (old('p_condition')==$key ? 'selected' : !empty($product->category_id) && $product->category_id==$category->id) ? 'selected' : '' ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="model-name-input" class="col-3 col-form-label">Model Name </label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_model')) ? old('p_model') : '' ?>" type="text" name="p_model" id="model-name-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_reference')) ? old('p_reference') : '' ?>" type="text"  name="p_reference" id="reference-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="serial-input" class="col-3 col-form-label">Serial</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_serial')) ? old('p_serial') : '' ?>" type="text" name="p_serial" id="serial-input">
                </div>
            </div>            
            <div class="form-group row">
                <label for="gender-input" class="col-3 col-form-label">Gender</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_gender">
                        @foreach (Gender() as $key => $gender)
                        <option <?php echo (!empty(old('p_gender')) && old('p_gender')==$key ? 'selected' : !empty($product->p_gender) && $product->category_id==$category->id) ? 'selected' : '' ?> value="{{ $key }}">{{ $gender }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="box-input" class="col-form-label">Box</label>
                    <input type="checkbox" class="ml-3" name="p_box" <?php echo (!empty(old('p_box')) ? 'checked' : !empty($product->p_box)) ? 'checked' : '' ?> >
                </div>
            </div>
        </div> 

        <div class="col-md-6">
            <div class="form-group row">
                <label for="material-name-input" class="col-3 col-form-label">Material *</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_material" required>
                        @foreach (MetalMaterial() as $key => $material)
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
                <label for="retail-input" class="col-3 col-form-label">Retail</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty(old('p_retail')) ? old('p_retail') :  '0' ?>" type="text" name="p_retail" id="retail-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="webprice-input" class="col-3 col-form-label">Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="<?php echo !empty(old('p_newprice')) ? old('p_newprice') : 0 ?>" type="text" name="p_newprice" id="webprice-input">
                </div>
            </div>            
            <div class="form-group row">
                <label for="webprice-input" class="col-3 col-form-label">Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="<?php echo !empty(old('p_newprice')) ? old('p_newprice') : 0 ?>" type="text" name="p_newprice" id="webprice-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">Quantity *</label>
                <div class="col-9">
                    <input class="form-control" value="1" autocomplete="off" value="<?php echo !empty(old('p_qty')) ? old('p_qty') : '' ?>" type="text" name="p_qty" id="quantity-input" required>
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
            url: "{{route('capture.image')}}",
            data: { 
                _token: "{{csrf_token()}}",
                captured_image: data_uri,
                _form: $('#productform').p_serialize(),
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
            url: "{{route('upload.image')}}",
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
       
        var mainPath = "{{route('get.customer.byID')}}";

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
                url: "{{route('delete.image')}}",
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