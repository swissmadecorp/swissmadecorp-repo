@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/css/dropzone.css') }}" rel="stylesheet">
@endsection

@section ('content')
{{ Form::open(array('route'=>array('categories.store'), 'id' => 'categoryForm')) }}
    <input type="hidden" value="categories" name="blade" />
    <input type="hidden" name="new_id" id="new_id" />
    
    <div class="form-group row">
        <label for="category-name-input" class="col-3 col-form-label">Category Name:</label>
        <div class="col-9">
            <input class="form-control" type="text" autofocus placeholder="Enter new category name" name="category_name" id="category-name-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="category-location-input" class="col-3 col-form-label">Category Location:</label>
        <div class="col-9">
            <input class="form-control" type="text" autofocus placeholder="Enter new category location" name="category_location" id="category-location-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="image-input" class="col-3 col-form-label">Image</label>
        <div class="col-9">
            <div id="dropzoneFileUpload" class="dropzone"></div>
        </div>
    </div>

  <button type="submit" class="btn btn-primary uploadPhoto">Save</button>

  @include('admin.errors')
  
{{  Form::close() }}  
@endsection

@section ('footer')
<script src="{{ asset('/js/dropzone.js') }}"></script>
@endsection

@section ('jquery')
<script>

$(document).ready( function() {
        Dropzone.autoDiscover = false;
        
        var myDropzone = new Dropzone("div#dropzoneFileUpload", {
            url: "{{route('upload.image')}}",
            maxFilesize: 10, // MB
            maxFiles: 1,
            parallelUploads: 1,
            dictDefaultMessage:'Drop a single file or click to upload manually',
            addRemoveLinks: true,
            autoProcessQueue:false,
            uploadMultiple: true,
            params: {
                _token: "{{csrf_token()}}",
                _id: 0
            },
        });

        myDropzone.on("sending", function(file, xhr, formData){
            formData.append('_form',$('#categoryForm').serialize());
        });

        myDropzone.on("success", function(file,resp){
            if(resp.message=="success"){
                //alert("Faild to upload image!");
                $('#new_id').val(resp.id);
                //$('#postform').submit();
                window.location.href = "/admin/categories"
            }
        });

        $(".uploadPhoto").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
                
            myDropzone.processQueue();
        })

    })
</script>
@endsection