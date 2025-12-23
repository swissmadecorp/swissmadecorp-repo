@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/lightgallery/css/lightgallery.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')

{{  Form::model($product, array('route' => array('products.update', $product->id), 'method' => 'PATCH', 'id' => 'productform')) }} 
    <input type="hidden" value="{{$product->title}}" name="title">
    <input type="hidden" value="{{$product->id}}" name="_id">
    <input type="hidden" value="1" name="group_id">

    <a href="{{ URL::to('admin/products/'.$product->id.'/print') }}" target="_blank" class="btn btn-primary print">Print Barcode</a>
    <button type="submit" class="btn btn-primary uploadPhoto">Update</button>
    <a style="float:right" href="{{ URL::to('admin/products/create') }}" class="btn btn-primary">Create New</a>
    @if ($product->p_return)
    <a style="float:right;margin-right: 4px" href="/admin/products/{{$product->id}}/printreturn" class="btn btn-primary">Print Return</a>
    @endif
    <hr>
    
    <div class="clearfix mb-4"></div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Title</label>
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Enter title for this product" value="{{ $product->title }}" name="title">
                </div>
            </div>
        </div>
    </div>
    
    <p><b>Created Date: {{ $product->created_at->format('m/d/Y h:i:s a') }}</b></p>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group row">
                <label for="model-name-input" class="col-3 col-form-label">Stock #</label>
                <div class="col-9">
                    <span class="form-control">{{$product->id }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="type-input" class="col-3 col-form-label">Type</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="jewelry_type">
                        @foreach (JewelryType() as $key => $type)
                        <option value="{{ $key }}" <?php echo !empty($product->jewelry_type) && $product->jewelry_type==$key ? 'selected' : '' ?>>{{ $key }}</option>
                        @endforeach
                    </select>
                </div>
            </div>               
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Category Name</label>
                <div class="col-9">
                    <select class="form-control categories" name="p_category" id="category">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" <?php echo !empty($product->category_id) && $product->category_id==$category->id ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <?php if (isset($product->categories->category_name)) { ?>
                        <input type="hidden" name="category_selected" id="category_selected" value="{{ $product->categories->id }}"/>
                    <?php } else { ?>
                        <input type="hidden" name="category_selected" id="category_selected" />
                    <?php } ?>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="condition-name-input" class="col-3 col-form-label">Condition</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_condition">
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty($product->p_condition) && $product->p_condition==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="model-name-input" class="col-3 col-form-label">Model Name</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_model) ? $product->p_model : '' ?>" type="text" placeholder="Enter new model name" name="p_model" id="model-name-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_reference) ? $product->p_reference : '' ?>" type="text" placeholder="Enter reference name or number" name="p_reference" id="reference-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="serial-input" class="col-3 col-form-label">Serial</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_serial) ? $product->p_serial : '' ?>" type="text" placeholder="Enter serial number" name="p_serial" id="serial-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="gender-input" class="col-3 col-form-label">Gender</label>
                <div class="col-9">
                <select class="custom-select form-control" name="p_gender">
                    @foreach (Gender() as $key => $gender)
                        <option <?php echo !empty($product->p_gender) && $product->p_gender==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $gender }}</option>
                    @endforeach
                </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label for="box-input" class="col-form-label">Box</label>
                    <input type="checkbox" name="p_box" <?php echo !empty($product->p_box) ? 'checked' : '' ?> id="box-input">
                </div>
            </div>

        </div>

        <div class="col-md-7">
            <div class="form-group row">
                <label for="material-name-input" class="col-3 col-form-label">Material</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_material" id="material-name-input">
                        @foreach (MetalMaterial() as $key => $material)
                        <option <?php echo !empty($product->p_material) && $product->p_material==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="price-input" class="col-3 col-form-label">Cost *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty($product->p_price) ? $product->p_price : '' ?>" type="text" placeholder="Enter price" name="p_price" id="price-input" required >
                </div>
            </div>
            <div class="form-group row">
                <label for="retail-input" class="col-3 col-form-label">Retail *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" value="<?php echo !empty($product->p_retail) ? $product->p_retail : '' ?>" type="text" placeholder="Enter retail price" name="p_retail" id="retail-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="webprice-input" class="col-3 col-form-label">Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="<?php echo !empty($product->p_newprice) ? $product->p_newprice : '' ?>" type="text" placeholder="Enter website price" name="p_newprice" id="webprice-input">
                </div>
            </div>            
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">Quantity *</label>
                <div class="col-4">
                    <input class="form-control qty" value="<?php echo !empty($product->p_qty) ? $product->p_qty : 0 ?>" type="text" placeholder="Enter amount on hand" name="p_qty" id="quantity-input" required>
                </div>
                <label for="p_return-input" class="col-2 col-form-label">Return</label>
                <div class="col-2">
                    <input class="form-control p_return" style="top: 11px;position: absolute;left: -20px;" <?php echo !empty($product->p_return) ? 'checked' : '' ?> type="checkbox" name="p_return" id="p_return-input">
                </div>
            </div>
            <div class="form-group row" style="position:relative">
                <label for="supplier-input" class="col-3 col-form-label">Supplier *</label>
                <div class="col-4">
                    <input class="form-control supplier" autocomplete="off" value="<?php echo !empty($product->supplier) ? $product->supplier : '' ?>" type="text" placeholder="Enter supplier" name="supplier" id="supplier-input" required>
                </div>
                <label for="supplier-input" class="col-2 col-form-label">Invoice#</label>
                <div class="col-3">
                    <input class="form-control invoice" autocomplete="off" value="<?php echo !empty($product->supplier_invoice) ? $product->supplier_invoice : '' ?>" type="text" placeholder="Enter Supplier Invoice #" name="supplier_invoice" id="invoice-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="slug-input" class="col-3 col-form-label">Slug</label>
                <div class="col-9">
                    <input class="form-control" autocomplete="off" value="<?php echo !empty($product->slug) ? $product->slug : '' ?>" type="text" name="slug" id="slug-input">
                </div>
            </div>      
            <div class="form-group row">
                <label for="status-input" class="col-3 col-form-label">Status</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_status">
                        @foreach (Status() as $key => $status)
                        <option <?php echo !empty($product->p_status) && $product->p_status==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row comments">
                <label for="comments-input" class="col-3 col-form-label">Comments</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" type="text" placeholder="Enter additional comments" name="p_comments" id="comments-input"><?php echo !empty($product->p_comments) ? $product->p_comments : '' ?></textarea>
                </div>
            </div>

            <div class="form-group row smalldescription">
                <label for="smalldescription-input" class="col-3 col-form-label">Small Description</label>
                <div class="col-9">
                <textarea rows="4" class="form-control" type="text" placeholder="Enter small description" name="p_smalldescription" id="smalldescription-input"><?php echo !empty($product->p_smalldescription) ? $product->p_smalldescription : '' ?></textarea>
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
                                <img alt="{{ $image->title }}" data-id="{{$image->id}}" data-pid="{{$product->id}}" src="{{ '/images/thumbs/' . $image->location }}" title="{{ $image->title }}" >
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

    <div class="form-group row snapshot mt-4" >
        <label for="captureimage-input" class="col-3 col-form-label">Capture Image</label>
        <div class="col-9">
            <div class="row">
                <div class="col-6">
                    <div style="width: 278px" id="captureimage"></div>
                </div>
                <div class="col-6">
                    <div id="results"></div>
                </div>
            </div>
            <input type=button value="Activate Snapshot" onClick="activate_snapshot()" class="activeSnapshot">
            <input type=button value="Take Snapshot" onClick="take_snapshot()" id="takesnapshot" >
        </div>
    </div>

    <?php if (count($product->orders)>0 && $product->id != 1) {?>
    <hr>
    <h4>Previous Orders</h4>
    <hr/>
    <div class="table-responsive">
    <table id="orders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Invoice Id</th>
                <th>Customer</th>
                <th>Date Sold</th>
                <th>Serial #</th>
                <th>Sold Amount</th>
            </tr>
        </thead>
        <tbody>      
        @foreach ($product->orders as $invoice)
        <?php 
            $returned='';$product=$invoice->products->find($product->id);
            if (count($invoice->returns))
                $returned = '-';
        ?>
        <tr style="<?= $returned=='-' ? "background: #ffecec" : "" ?>" >
            <td><a href="/admin/orders/{{ $product->pivot->order_id }}">{{ $product->pivot->order_id }}</a></td>
            <td>{{ $invoice->customers->first()->company }}</td>
            <td>{{ $invoice->created_at->format('m/d/Y')}}</td>
            <td>{{ $product->pivot->serial }}</td>
            <td class="text-right"><?= $returned ?>${{ number_format($product->pivot->price,2) }}</td>   
        </tr>
        @endforeach
    </tbody>
    </table>
    </div>
    <?php } ?>
    <hr>
    <a href="{{ URL::to('admin/products/'.$product->id.'/print') }}" target="_blank" class="btn btn-primary print">Print Barcode</a>
    <button type="submit" class="btn btn-primary uploadPhoto">Update</button>
    <a style="float:right" href="{{ URL::to('admin/products/create') }}" class="btn btn-primary">Create New</a>
    
    @include('admin.errors')
    
{{  Form::close() }}  

<div id="search-customer"></div>
@endsection

@section ('footer')
<script src="/js/dropzone.js"></script>
<script src="/lightgallery/js/lightgallery-all.min.js"></script>
<script src="/lightgallery/js/lg-thumbnail.min.js"></script>
<script src="/js/webcam.min.js"></script>
<script src="/editable-select/jquery-editable-select.js"></script>

<!-- Configure a few settings and attach camera -->
<script language="JavaScript">

    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    //Webcam.attach( '#captureimage' );
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
                        $(result.content).insertBefore('.multi-image-container .col-md-12');
                        $('.image-holder').removeClass('col-md-12').addClass('col-md-6');
                    } else {
                        $('.image-container').append(result.content2);
                    }
                    $('input[name=_filename]').val(result.filename);
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
            }
        });

        myDropzone.on("successmultiple", function(file,resp){
            if(resp.message!="success"){
                alert("Faild to upload image!");
                return false;
            }
            
            $("form").submit();
            //window.location.href = "/admin/products"
        });

        //$('.categories option[value="'+$(".categories option:selected").val()+'"]').attr('selected', 'selected');
        $(".categories").focus().find(":selected")[0].scrollIntoView(false);

        $('.print').click( function (e) {
            e.preventDefault();
            
            var printWindow = window.open("{{ URL::to('admin/products/'.$product->id.'/print') }}", "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400");
            var printAndClose = function() {
                if (printWindow.document.readyState == 'complete') {
                    clearInterval(sched);
                    //printWindow.print();
                    //printWindow.close();
                }
            }
            var sched = setInterval(printAndClose, 1000);
        })

        $(".uploadPhoto").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
                
            myDropzone.processQueue();
        })

        $('#category').editableSelect({ effects: 'fade' })
            .on('select.editable-select', function (e, li) {
                $('#category_selected').val(li.val()
            );
        });

        var getPath = "{{action('CustomersController@ajaxgetCustomer')}}";
        var mainPath = "{{action('CustomersController@ajaxCustomer')}}";

        $('#supplier-input').dropdown({
            // default is fullname so no need to specify
            searchBy: 'company',
            getPath: getPath,
            mainPath: mainPath,
            success: function(data) {
                $('#supplier-input').val(data['company']);
            }
        });

        $('#category').editableSelect({ effects: 'fade' })
            .on('select.editable-select', function (e, li) {
                if (typeof li !== 'undefined')
                    $('#category_selected').val(li.val());
        });

        $('#category').on('input',function() {
            if (!$('#category').val())
                $('#category_selected').val('');
        })

        $('.image-container').lightGallery({
            selector: '.image-item',
            mode: 'lg-fade',
            mousewheel: true,
            download: false,
            share: false,
            fullScreen: false,
            thumbnail:true,
            animateThumb: false,
            showThumbByDefault: false
        })

        $('.p_return').click ( function () {
            if ($(this).prop('checked')) {
                if (confirm ('You are about to return this product back to the supplier. Are you sure you want to do this?')) {
                    $('.qty').val(0);
                }
            }
        })

        $(document).on('click','.delete-image', function() {
            var _this = $(this);
            var img = $(this).parent().find('img')
            var request = $.ajax({
                type: "POST",
                url: "{{action('DropzoneController@deleteImage')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    imageId: img.attr('data-id'), 
                    id: img.attr('data-pid'),
                    filename: img.attr('src')
                },
                success: function (result) {
                    $(_this).parent('.image').remove();
                    if ($('.delete-image').length==0) {
                        $('.snapshot').show();
                        $('.multi-image-container .loadedimages').remove();
                        $('.multi-image-container .col-md-6').addClass('col-md-12').removeClass('col-md-6');
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
        
        if ($('.delete-image').length==0) {
                $('.snapshot').show();
            }
    })
</script>
@endsection