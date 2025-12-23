@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/css/dropzone.css') }}" rel="stylesheet">
@endsection

@section ('content')
<form method="POST" action="{{route('category.update',[$category->id])}}" accept-charset="UTF-8" id="categoryForm">
    @csrf
    @method('PATCH')
    <input name="title" value="{{ $category->category_name}}" type="hidden" />
    <input name="_id" value="{{ $category->id}}" type="hidden" />
    <input type="hidden" value="categories" name="blade" />

    <div class="form-group row">
        <label for="category-name-input" class="col-3 col-form-label">Category Name:</label>
        <div class="col-9">
            <input class="form-control" type="text" value="{{ $category->category_name }}" placeholder="Edit existing category name" name="category_name" id="category-name-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="category-location-input" class="col-3 col-form-label">Category Location:</label>
        <div class="col-9">
            <input class="form-control" type="text" value="{{ $category->location }}" placeholder="Edit existing category location" name="category_location" id="category-location-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="category-title-input" class="col-3 col-form-label">Category Title:</label>
        <div class="col-9">
            <input class="form-control" type="text" value="{{ $category->category_title }}" placeholder="Title for your category" name="category_title" id="category-title-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="category-description-input" class="col-3 col-form-label">Category Description:</label>
        <div class="col-9">
            <textarea rows="7" class="form-control" placeholder="Description for your category" name="category_description" id="category-description-input">{{ $category->category_description }}</textarea>
        </div>
    </div>
    <div class="form-group row">
        <label for="image-name-input" class="col-3 col-form-label">Image</label>
        <div class="col-9">
            @if (!empty($category->image_name))
            <div class="image-container" style="height: auto">
                <div class="image">
                    <div class="delete-image">X</div>
                    <img class="form-control" src="/images/categories/thumbs/<?php echo $category->image_name ?>" type="text" name="image" id="image-name-input" />
                </div>
            </div>
            <div id="dropzoneFileUpload" style="display:none" class="dropzone"></div>
            @else 
                <div id="dropzoneFileUpload" class="dropzone"></div>
            @endif
        </div>
    </div>


    <button type="submit" class="btn btn-primary uploadPhoto">Update</button>
    
    @include('admin.errors')
    
@endsection

@section ('footer')
<script src="{{ asset('/js/dropzone.js') }}"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        $('#category-name-input').on('input', function() {
            $('#category-location-input').val($(this).val().replace('&','and').toLowerCase())
            m = $('#category-location-input').val();
            $('#category-location-input').val(m.replace(/[&\/\\#,+()$~%.'":*?<>{}|\s]/g,'-').toLowerCase())
        })

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
                $("form").find("input,textarea").each(function(){
                    if ($(this).attr("name") !==undefined && $(this).attr("name")!='_method')
                        formData.append($(this).attr("name"), $(this).val());
                });

            },
            init: function() {
                var thisDropzone = this;

                // this.on("thumbnail", function(file) {
                //     if (file.width < 500 || file.height < 500) {
                //         file.rejectDimensions();
                //     } else {
                //         file.acceptDimensions();
                //     }
                // });
                // Listen to the sendingmultiple event. In this case, it's the sendingmultiple event instead
                // of the sending event because uploadMultiple is set to true.
                this.on("sendingmultiple", function() {
                    // Gets triggered when the form is actually being sent.
                    // Hide the success button or the complete form.
                });
                this.on("successmultiple", function(files, response) {
                    // Gets triggered when the files have successfully been sent.
                    // Redirect user or notify of success.
                    if(response.message!="success"){
                        alert("Faild to upload image!");
                        return false;
                    }
                    processClick = true
                    $('.uploadPhoto').click();
                });
                this.on("errormultiple", function(files, response) {
                // Gets triggered when there was an error sending the files.
                // Maybe show form again, and notify user of error
                });
            }
        });

        // myDropzone.on("successmultiple", function(file,resp){
        //     if(resp.message=="success"){
        //         //alert("Faild to upload image!");
                
        //         processClick = true;
        //         window.location.href = "/admin/categories"   
        //     }
        // });

        $(".uploadPhoto").click( function(e) {
            if (processClick)
                return true;

            if ($('.dz-image-preview').length > 0) {
                e.preventDefault();
                e.stopPropagation();
            }
            myDropzone.processQueue();
        })

        $(document).on('click','.delete-image', function() {
            var _this = $(this);
            var img = $(this).parent().find('img')
            var request = $.ajax({
                type: "POST",
                url: "{{route('delete.image')}}",
                data: { 
                    blade: "categories",
                    _id: "{{ $category->id}}"
                },
                success: function (result) {
                    $(_this).parent('.image').remove();
                    if ($('.delete-image').length==0) {
                        $('.image-container').hide();
                        $('.dropzone').show();
                    }
                }
            })
            request.fail( function (jqXHR, textStatus) {
                //alert ("Requeset failed: " + textStatus)
            })
        })
    })    
</script>
@endsection
