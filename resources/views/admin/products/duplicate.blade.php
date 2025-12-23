@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')

<form method="POST" action="{{route('products.store')}}" accept-charset="UTF-8" id="productform"> 
    @csrf
    <input type="hidden" value="0" name="group_id">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <label for="title-input" class="col-3 col-form-label">Title</label>
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Leave blank to auto generate title" name="title" id="title-input">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group row">
                <label for="category-name-input" class="col-3 col-form-label">Category Name *</label>
                <div class="col-9">
                    <select class="form-control" id="category" name="p_category"  required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" <?php echo !empty($product) && $product->category_id==$category->id ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="category_selected" id="category_selected" value="{{ $product->categories->id }}" />
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
                <label for="model-name-input" class="col-3 col-form-label">Model Name </label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_model) ? $product->p_model : '' ?>" type="text" placeholder="Enter new model name" name="p_model" id="model-name-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="casesize-input" class="col-3 col-form-label">Case Size</label>
                <div class="col-9">
                    <input class="form-control" type="text" value="<?php echo !empty($product->p_casesize) ? $product->p_casesize : '' ?>" name="p_casesize" id="casesize-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_reference) ? $product->p_reference : '' ?>" type="text" placeholder="Enter reference name or number" name="p_reference" id="reference-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="serial-input" class="col-3 col-form-label">Serial *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('serial')) ? old('serial') : '' ?>" type="text" autofocus placeholder="Enter serial number" name="p_serial" id="serial-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="color-input" class="col-3 col-form-label">Dial Color *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_color) ? $product->p_color : '' ?>" type="text" placeholder="Enter bezel color" name="p_color" id="color-input" >
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
            <div class="form-group row">
                <label for="strap-input" class="col-3 col-form-label">Strap/Band</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_strap">
                        @foreach (Strap() as $key => $strap)
                            <option <?php echo !empty($product->p_strap) && $product->p_strap==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $strap }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="form-group row">
                <label for="clasp-input" class="col-3 col-form-label">Clasp Type</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_clasp">
                        @foreach (Clasps() as $key => $clasp)
                        <option <?php echo !empty($product->p_clasp) && $product->p_clasp==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $clasp }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="form-group row">
                <label for="year-input" class="col-3 col-form-label">Production Year</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('year')) ? old('year') : '' ?>" type="text" name="p_year" id="year-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="water_resistance-input" class="col-3 col-form-label">Water Resistance</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->water_resistance) ? $product->water_resistance : '' ?>" type="text" name="water_resistance" id="water_resistance-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="bezel-features-input" class="col-3 col-form-label">Bezel Features</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->bezel_features) ? $product->bezel_features : '' ?>" type="text" name="bezel_features" id="bezel_features-input" >
                </div>
            </div>            
            <?php 
                $ranTimes = ceil(count($custom_columns)/2);
                for ($i=0; $i < count($custom_columns);$i++) {
                    $column = $custom_columns[$i]; ?>
                    <div class="form-group row">
                        <label for="{{$column}}-input" class="col-3 col-form-label">{{ucwords(str_replace(['-','c_'], ' ', $column))}}</label>
                        <div class="col-9">
                        <input  class="form-control" type="text" name="{{$column}}" id="{{$column}}-input" value="<?= !empty($product->$column) ? $product->$column : '' ?>" />
                        </div>
                    </div> 
                    <?php if ($ranTimes==$i+1) break; 
                } ?>
            <div class="row">
                <div class="col-md-6">
                    <label for="box-input" class="col-form-label">Box</label>
                    <input type="checkbox" class="ml-3" <?php echo !empty($product->p_box) ? "checked" : '' ?> name="p_box" id="box-input">
                </div>

                <div class="col-md-6">
                    <label for="papers-input" class="col-form-label">Papers</label>
                    <input type="checkbox" class="ml-3" name="p_papers"<?php echo !empty($product->p_papers) ? "checked" : '' ?> id="papers-input">
                </div>
            </div>

        </div> 
        <div class="col-md-7">
            <div class="form-group row">
                <label for="material-name-input" class="col-3 col-form-label">Case&nbsp;Material&nbsp;*</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_material" required>
                        @foreach (Materials() as $key => $material)
                        <option <?php echo !empty($product->p_material) && $product->p_material==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="bezelmaterial-name-input" class="col-3 col-form-label">Bezel&nbsp;Material&nbsp;*</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_bezelmaterial" id="bezelmaterial-name-input" required>
                        @foreach (BezelMaterials() as $key => $bezelmaterial)
                        <option <?php echo !empty($product->p_bezelmaterial) && $product->p_bezelmaterial==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $bezelmaterial }}</option>
                        @endforeach
                    </select>
                </div>
            </div>                 
            <div class="form-group row">
                <label for="price-input" class="col-3 col-form-label">Cost *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" type="text" value="<?php echo !empty($product->p_price) ? $product->p_price : '' ?>" placeholder="Enter price" name="p_price" id="price-input" required >
                </div>
            </div>
            <div class="form-group row">
                <label for="retail-input" class="col-3 col-form-label">Retail *</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <div class="percent-container">
                        <input class="form-control" value="<?php echo !empty($product->p_retail) ? $product->p_retail : '' ?>" type="text" placeholder="Enter retail price" name="p_retail" id="retail-input" >
                        <button><i class="fas fa-percent"></i></button>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="webprice-input" class="col-3 col-form-label">Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="0" type="text" name="p_newprice" id="webprice-input">
                </div>
            </div>  
            <div class="form-group row">
                <label for="price3P-input" class="col-3 col-form-label">3rd Party Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="0" type="text" name="p_price3P" id="price3P-input">
                </div>
            </div>                      
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">On Hand *</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty($product->p_qty) ? $product->p_qty : 1 ?>" type="text" placeholder="Enter amount on hand" name="p_qty" id="quantity-input" required>
                </div>
            </div>
            <div class="form-group row supplier">
                <label for="supplier-input" class="col-3 col-form-label">Supplier</label>
                <div class="col-4">
                    <input class="form-control supplier" autocomplete="off"  type="text" value="<?php echo !empty($product->supplier) ? $product->supplier : '' ?>" placeholder="Enter supplier" name="supplier" id="supplier-input" required>
                </div>
                <label for="supplier_invoice-input" class="col-2 col-form-label">Invoice#</label>
                <div class="col-3">
                    <input class="form-control supplier_invoice" autocomplete="off"  type="text" name="supplier_invoice" id="supplier_invoice-input">
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
                    <textarea rows="4" class="form-control" value="<?php echo !empty(old('comments')) ? old('comments') : '' ?>" type="text" name="p_comments" id="comments-input"></textarea>
                </div>
            </div>
            <div class="form-group row smalldescription">
                <label for="smalldescription-input" class="col-3 col-form-label">Small Description</label>
                <div class="col-9">
                    <textarea rows="4" class="form-control" value="<?php echo !empty(old('smalldescription')) ? old('smalldescription') : '' ?>" type="text" name="p_smalldescription" id="smalldescription-input"></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="movement-name-input" class="col-3 col-form-label">Movement</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="movement" id="movement-name-input" required>
                        @foreach (Movement() as $key => $movement)
                        <option <?php echo !empty($product->movement) && $product->movement==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $movement }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="dial-style-name-input" class="col-3 col-form-label">Dial Style</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_dial_style" id="dial-style-name-input" required>
                        @foreach (DialStyle() as $key => $dialstyle)
                        <option value="{{$key}}">{{ $dialstyle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @for ($i=$ranTimes; $i < count($custom_columns);$i++)
                @php $column = $custom_columns[$i] @endphp
                <div class="form-group row">
                    <label for="{{$column}}-input" class="col-3 col-form-label">{{ucwords(str_replace(['-','c_'], ' ', $column))}}</label>
                    <div class="col-9">
                    <input  class="form-control" type="text" name="{{$column}}" id="{{$column}}-input" value="<?= !empty($product->$column) ? $product->$column : '' ?>" />
                    </div>
                </div>

            @endfor            
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
                                <input type="hidden" name="filename[]" value="<?= $image->location ?>" />
                            </a>
                            @endif
                            
                        @endif
                        <div class="position"><input type="text" value="{{$image->position}}" placeholder="image position" name="p_position_{{$image->id}}" class="position-input" /></div>
                        
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
    </form>
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

        $('.percent-container button').click ( function(e) {
            e.preventDefault();
            var retail = $('.percent-container input').val();
            $.confirm({
                title: 'Retail Price Calculator',
                content: '' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>Enter a new percent rate</label>' +
                '<input type="text" autofocus placeholder="Percent" class="percent_value form-control" required />' +
                '<label>Enter retail price</label>' +
                '<input type="text" placeholder="Retail Price" value="'+retail+'" class="retail_value form-control" required />' +
                '</div>' +
                '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Calculate',
                        btnClass: 'btn-blue',
                        action: function () {
                            var percent_value = this.$content.find('.percent_value').val();
                            var retail_value = this.$content.find('.retail_value').val();
                            if(!percent_value || !retail_value){
                                $.alert('Provide percent rate and retail value.');
                                return false;
                            } else if (retail_value.charAt(0) == '0' || retail_value.charAt(0) == '.' || percent_value.indexOf('%')>-1) {
                                $.alert('Enter number without zeros, decimals, or percent signs in front or end of digits.');
                                return false;
                            }
                         
                            calc = Math.ceil(retail_value - (retail_value * (percent_value / 100)));
                            $('#price-input').val(calc);
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

        function checkforBoxPapers() {
            var box = $('#box-input').is(':checked');
            var papers = $('#papers-input').is(':checked');

            if (!box && !papers)
                return false;
            else return true;
        }

        
        function confirmToPrint() {
            $.confirm({
                content: "Would you like to print the barcode?",
                buttons: {
                    print:  function() {
                        $('#printAfterSave').val('1');
                        $('.uploadPhoto').click();
                    },
                    no: function() {
                        $('.uploadPhoto').click();
                    },
                    cancel: function() {}
                }
            })
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

                confirmToPrint();
                $('#productform').submit();
            }
        });

        $('#webprice-input').on('input', function() {
            //if ($('#retail-input').val()>0) {
                //var dec = $(this).val()/($('#retail-input').val());
                //dec = parseFloat(dec).toFixed(2);
                //$('#price3P-input').val(parseInt($('#retail-input').val() - ($('#retail-input').val() * (1-dec-0.07) )))
                $('#price3P-input').val(parseInt($('#webprice-input').val()) + parseInt($('#webprice-input').val() * 0.028 ));
           // }
        })
        
        $('#productform').submit( function () {
            if (checkforBoxPapers())
                return true
            else {
                if (confirm("Box or Papers have not been selected. Are you sure you want to proceed?"))
                    return true
                else return false
            }
        })

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
                    filename: $(_this).next().attr('src'),
                    isduplicate: true
                },
                success: function (result) {
                    $(_this).parent('.image').remove();
                    if ($('.delete-image').length==0) {
                        $('.snapshot').show();
                        //$('.multi-image-container').hide();
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

        $(".uploadPhoto").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
            
            //if ($('.activeSnapshot').is(':visible'))
                myDropzone.processQueue();
        })
        
    })
</script>
@endsection