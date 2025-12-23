@extends('layouts.admin-default')

@section ('header')
<link href="/css/dropzone.css" rel="stylesheet">
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
@endsection

@section ('content')

<form method="POST" action="{{route('products.store')}}" accept-charset="UTF-8" id="productform">
@csrf
    <input type="hidden" name="printAfterSave" id="printAfterSave" value="0">
    <input type="hidden" value="0" name="group_id">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <label for="title-input" class="col-3 col-form-label">Title</label>
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Enter title for this product or leave blank to auto generate" name="title" id="title-input">
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
                            <option value="{{ $category->id }}" <?php echo (!empty(old('p_category')) && old('p_category')==$category->id ? 'selected' : !empty($product) && $product->category_id==$category->id) ? 'selected' : '' ?>>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="category_selected" id="category_selected" />
                </div>
            </div>
            
            <div class="form-group row">
                <label for="condition-name-input" class="col-3 col-form-label">Condition&nbsp;*</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_condition" required>
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty(old('p_condition')) && (old('p_condition')==$key ? 'selected' : !empty($product->category_id) && $product->category_id==$category->id) ? 'selected' : '' ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>     
            <div class="form-group row">
                <label for="model-name-input" class="col-3 col-form-label">Model Name</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_model')) ? old('p_model') : '' ?>" type="text" name="p_model" id="model-name-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="case-size-input" class="col-3 col-form-label">Case Size</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_casesize')) ? old('p_casesize') : '' ?>" type="text" name="p_casesize" id="case_size-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="reference-input" class="col-3 col-form-label">Reference</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_reference')) ? old('p_reference') : '' ?>" type="text"  name="p_reference" id="reference-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="serial-input" class="col-3 col-form-label">Serial&nbsp;*</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_serial')) ? old('p_serial') : '' ?>" type="text" name="p_serial" id="serial-input" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="color-input" class="col-3 col-form-label">Dial&nbsp;Color&nbsp;*</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_color')) ? old('p_color') : '' ?>" type="text" name="p_color" id="color-input" required>
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
                        <option <?php echo (!empty(old('p_clasp')) && old('p_clasp')==$key ? 'selected' : !empty($product->p_clasp) && $product->category_id==$category->id) ? 'selected' : '' ?> value="{{ $key }}">{{ $clasp }}</option>
                        @endforeach
                    </select>            
                </div>
            </div>
            <div class="form-group row">
                <label for="year-input" class="col-3 col-form-label">Production Year</label>
                <div class="col-9">
                    <input class="form-control" value="<?php echo !empty(old('p_year')) ? old('p_year') : '' ?>" type="text" name="p_year" id="year-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="water_resistance-input" class="col-3 col-form-label">Water Resistance</label>
                <div class="col-9">
                    <input class="form-control" type="text" name="water_resistance" id="water_resistance-input" >
                </div>
            </div>
            <div class="form-group row">
                <label for="bezel-features-input" class="col-3 col-form-label">Bezel Features</label>
                <div class="col-9">
                    <input class="form-control" type="text" name="bezel_features" id="bezel_features-input" >
                </div>
            </div>
            <?php 
                $ranTimes = ceil(count($custom_columns)/2);
                for ($i=0; $i < count($custom_columns);$i++) {
                    $column = $custom_columns[$i];?>
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
                    <input type="checkbox" class="ml-3" name="p_box" <?php echo (!empty(old('p_box')) ? 'checked' : !empty($product->p_box)) ? 'checked' : '' ?> >
                </div>

                <div class="col-md-6">
                    <label for="papers-input" class="col-form-label">Papers</label>
                    <input type="checkbox" class="ml-3" name="p_papers" <?php echo (!empty(old('p_papers')) ? 'checked' : !empty($product->p_papers)) ? 'checked' : '' ?>>                    
                </div>
            </div>

        </div> 

        <div class="col-md-7">
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
                <label for="bezelmaterial-name-input" class="col-3 col-form-label">Bezel Material *</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="p_bezelmaterial" required>
                        @foreach (BezelMaterials() as $key => $bezelmaterial)
                        <option <?php echo !empty(old('p_bezelmaterial')) && old('p_bezelmaterial')==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $bezelmaterial }}</option>
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
                    <div class="percent-container">
                        <input class="form-control" value="<?php echo !empty(old('p_retail')) ? old('p_retail') :  '0' ?>" type="text" name="p_retail" id="retail-input">
                        <button><i class="fas fa-percent"></i></button>
                    </div>
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
                <label for="price3P-input" class="col-3 col-form-label">3rd Party Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" style="background: #ffe5e5" value="<?php echo !empty(old('p_price3P')) ? old('p_price3P') : 0 ?>" type="text" name="p_price3P" id="price3P-input">
                </div>
            </div>                        
            <div class="form-group row">
                <label for="quantity-input" class="col-3 col-form-label">On Hand *</label>
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
            <div class="form-group row">
                <label for="movement-name-input" class="col-3 col-form-label">Movement</label>
                <div class="col-9">
                    <select class="custom-select form-control" name="movement" id="movement-name-input" required>
                        @foreach (Movement() as $key => $movement)
                        <option value="{{$key}}">{{ $movement }}</option>
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
                @php $column = $custom_columns[$i];@endphp
                <div class="form-group row">
                <label for="{{$column}}-input" class="col-3 col-form-label">{{ucwords(str_replace(['-','c_'], ' ', $column))}}</label>
                    <div class="col-9">
                    <input  class="form-control" type="text" name="{{$column}}" id="{{$column}}-input" value="<?= !empty($product->$column) ? $product->$column : '' ?>" />
                    </div>
                </div>

            @endfor
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
    </form>
    <div id="search-customer"></div>

@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/js/dropzone.js"></script>
<script src="/editable-select/jquery-editable-select.js"></script>

<script src="js/webcam.min.js"></script>
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
                
                if (!result.title)
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

        var processClick = false;
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

                processClick = true;
                confirmToPrint();
            }
        });

        $(".uploadPhoto").click( function(e) {
            if (processClick)
                return true;

            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
            else if ($('#printAfterSave').val() != 1) {
                e.preventDefault();
                confirmToPrint();
            }
            myDropzone.processQueue();
        })

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

        function confirmToPrint() {
            $.confirm({
                content: "Would you like to print the barcode?",
                buttons: {
                    formSubmit: {
                        text: 'Print',
                        btnClass: 'btn-blue',
                        action: function () {
                            $('#printAfterSave').val('1');
                            $('form').submit();
                        }
                    },
                    no: function() {
                        $('form').submit();
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

            })
        }

        $('#category').editableSelect({ effects: 'fade' })
            .on('select.editable-select', function (e, li) {
                $('#category_selected').val(li.val());
        });
       
        var mainPath = "{{route('get.customer.byID')}}";

        $('#supplier-input').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            params:{addParam: 'justCompany'}
        });

        $('#webprice-input').on('input', function() {
            //if ($('#retail-input').val()>0) {
                //var dec = $(this).val()/($('#retail-input').val());
                //dec = parseFloat(dec).toFixed(2);
                //$('#price3P-input').val(parseInt($('#retail-input').val() - ($('#retail-input').val() * (1-dec-0.07) )))
                $('#price3P-input').val(parseInt($('#webprice-input').val()) + parseInt($('#webprice-input').val() * 0.05 ));
           // }
        })
        
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
        
    })
</script>
@endsection