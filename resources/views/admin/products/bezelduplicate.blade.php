@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')

{{  Form::open(array('action'=>array('ProductsController@storeDuplicate',$product->id),'id' => 'productform')) }}  
    @if (!empty($product))
    <input type="hidden" value="{{$product->categories->category_name. ' ' . $product->p_model. ' ' . $product->p_reference}}" name="title">
    <input type="hidden" value="{{$product->id}}" name="_id">
    @else
    <input type="hidden" value="" name="title">
    @endif

    <input type="hidden" value="2" name="group_id">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Category Name *</label>
                <div class="col-9">
                    <select class="form-control" id="category" name="category"  required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" <?php echo !empty($product) && $product->category_id==$category->id ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="category_selected" id="category_selected" value="{{ $category->id }}" />
                </div>
            </div>
            
            <div class="form-group row">
                <label for="condition-name-input" class="col-3 col-form-label">Condition</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="condition">
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty($product->p_condition) && $product->p_condition==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="model-name-input" class="col-3 col-form-label">Model Name </label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_model) ? $product->p_model : '' ?>" type="text" placeholder="Enter new model name" name="model" id="model-name-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_reference) ? $product->p_reference : '' ?>" type="text" placeholder="Enter reference name or number" name="reference" id="reference-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="serial-input" class="col-3 col-form-label">Serial *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('serial')) ? old('serial') : '' ?>" type="text" placeholder="Enter serial number" name="serial" id="serial-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="color-input" class="col-3 col-form-label">Dial Color *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_color) ? $product->p_color : '' ?>" type="text" placeholder="Enter bezel color" name="color" id="color-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="gender-input" class="col-3 col-form-label">Gender</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="gender">
                        @foreach (Gender() as $key => $gender)
                            <option <?php echo !empty($product->p_gender) && $product->p_gender==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $gender }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="form-group row">
                <label for="strap-input" class="col-3 col-form-label">Strap/Band</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="strap">
                        @foreach (Strap() as $key => $strap)
                            <option <?php echo !empty($product->p_strap) && $product->p_strap==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $strap }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="box-input" class="col-form-label">Box</label>
                    <input type="checkbox" class="ml-3" name="box" <?php echo !empty(old('box')) ? 'checked' : !empty($product->p_box) ? 'checked' : '' ?> >
                </div>

                <div class="col-md-6">
                    <label for="papers-input" class="col-form-label">Papers</label>
                    <input type="checkbox" class="ml-3" name="papers" <?php echo !empty(old('papers')) ? 'checked' : !empty($product->p_papers) ? 'checked' : '' ?>>                    
                </div>
            </div>

        </div> 
        <div class="col-md-6">
            <div class="form-group row">
                <label for="material-name-input" class="col-3 col-form-label">Material *</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="material" required>
                        @foreach (Materials() as $key => $material)
                        <option <?php echo !empty($product->p_material) && $product->p_material==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="price-input" class="col-3 col-form-label">Cost *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" type="text" placeholder="Enter price" name="price" id="price-input" required >
                </div>
            </div>
            <div class="form-group row">
                <label for="retail-input" class="col-3 col-form-label">Retail *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty($product->p_retail) ? $product->p_retail : '' ?>" type="text" placeholder="Enter retail price" name="retail" id="retail-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">Quantity *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_qty) ? $product->p_qty : 0 ?>" type="text" placeholder="Enter amount on hand" name="qty" id="quantity-input" required>
                </div>
            </div>
            <div class="form-group row supplier">
                <label for="supplier-input" class="col-3 col-form-label">Supplier</label>
                <div class="col-9">
                    <input class="form-control supplier" autocomplete="off"  type="text" placeholder="Enter supplier" name="supplier" id="supplier-input" required>
                </div>
            </div>  
 
            <div class="form-group row">
                <label for="status-input" class="col-3 col-form-label">Status</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="status">
                        @foreach (Status() as $key => $status)
                        <option <?php echo !empty($product->p_status) && $product->p_status==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-group row comments">
                <label for="comments-input" class="col-3 col-form-label">Comments</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" value="<?php echo !empty(old('comments')) ? old('comments') : '' ?>" type="text" name="comments" id="comments-input"></textarea>
                </div>
            </div>

            <div class="form-group row smalldescription">
                <label for="smalldescription-input" class="col-3 col-form-label">Small Description</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" value="<?php echo !empty(old('smalldescription')) ? old('smalldescription') : '' ?>" type="text" name="smalldescription" id="smalldescription-input"></textarea>
                </div>
            </div>
        </div>
    </div>

    <?php $i=0; ?>
    
    <div class="row multi-image-container">
        @if (@count($product->images))
        <div class="col-md-6 loadedimages">
            <label for="images-input" class="col-form-label">Images</label>
            <div class="image-container">
                @foreach ($product->images as $image)
                    <div class="image" data-src="">
                        <div class="delete-image">X</div>
                        @if ($image->location)
                            
                            @if (strpos($image->location,'.com'))
                            <a href="" class="image-item" data-src="{{ '//' . $image->location }}">
                                <img alt="{{ $image->title }}" data-id="{{$image->id}}" data-pid="{{$product->id}}" src="{{ '//' . $image->location }}" title="{{ $image->title }}" >
                            </a>
                            @else
                            <a href="" class="image-item" data-src="{{ '/images/' . $image->location }}">
                                <img alt="{{ $image->title }}" data-id="{{$image->id}}" data-pid="{{$product->id}}" src="{{ 'images/thumbs/' . $image->location }}" title="{{ $image->title }}" >
                                <input type="hidden" name="filename[]" value="<?= $image->location ?>" />
                            </a>
                            @endif
                            
                        @endif
                        <div class="position"><input type="text" value="{{$image->position}}" placeholder="image position" name="position_{{$image->id}}" class="position-input" /></div>
                        
                    </div>
                <?php $i++ ?>
                @endforeach
            </div>
            </div>
            @endif

        @if (@count($product->images))
        <div class="col-md-6">
        @else
        <div class="col-md-12 image-holder">
        @endif
            <label for="images-input" class="col-form-label">Image Uploads</label>
            <div id="dropzoneFileUpload" class="dropzone" style="padding:62px 20px" multiple></div>
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
       
        var getPath = "{{action('SuppliersController@ajaxgetSupplier')}}";
        var mainPath = "{{action('SuppliersController@ajaxSupplier')}}";

        $('#supplier-input').dropdown({
            // default is fullname so no need to specify
            searchBy: 'company',
            getPath: getPath,
            mainPath: mainPath,
            success: function(data) {
                $('#supplier-input').val(data['company']);
            }
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