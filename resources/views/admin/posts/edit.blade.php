@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/css/dropzone.css') }}" rel="stylesheet">
@endsection

@section ('content')
<form method="POST" action="{{route('posts.update',[$post->id])}}" id="postform">
    @csrf
    @method('PATCH')
    <input type="hidden" value="posts" name="blade" />
    <input type="hidden" name="new_id" id="new_id" />
    <input type="hidden" value="{{ $post->id }}" name="_id"/>

    <div class="form-group row">
        <label for="image-name-input" class="col-3 col-form-label">Image</label>
        <div class="col-9">
            @if (!empty($post->image))
            <div class="image-container">
                <div class="image">
                    <div class="delete-image">X</div>
                    <img class="form-control" src="/images/posts/thumbs/<?php echo $post->image ?>" type="text" placeholder="Enter new image name" name="image" id="image-name-input" />
                </div>
            </div>
            <div id="dropzoneFileUpload" style="display:none" class="dropzone"></div>
            @else 
                <div id="dropzoneFileUpload" class="dropzone"></div>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label for="title-name-input" class="col-3 col-form-label">Title *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo $post->title ?>" type="text" placeholder="Enter new title name" name="title" id="title-name-input" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="subtitle-name-input" class="col-3 col-form-label">Sub Title *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo $post->subtitle ?>" type="text" placeholder="Enter new subtitle name" name="subtitle" id="subtitle-name-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="post-name-input" class="col-3 col-form-label">Post *</label>
        <div class="col-9">
            <textarea class="form-control" type="text" placeholder="Enter text for your post" name="posts" id="post-input">{{ $post->post }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary uploadPhoto">Update</button>
    @include('admin.errors')
</form>
@endsection

@section ('footer')
<script src="{{ asset('/js/dropzone.js') }}"></script>
<script src="{{ asset('/js/tinymce/tinymce.min.js') }}"></script>
@endsection

@section ('jquery')
<script>
    tinymce.init({ 
        selector:'textarea', 
        theme: "silver",
        height:300,
        plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table contextmenu paste code',
    ],
        branding: false,
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : '',
        advlist_bullet_styles: "square"  // only include square bullets in list
    });

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

        myDropzone.on("sending", function(file, xhr, formData){
            $('#post-input').val(tinyMCE.get('post-input').getContent());
            formData.append('_form',$('#postform').serialize());
        });

        myDropzone.on("success", function(file,resp){
            if(resp.message=="success"){
                $('#new_id').val(resp.id);
                //$('#postform').submit();
                window.location.href = "/admin/posts"
            }
        });

        $(".uploadPhoto").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
                
            myDropzone.processQueue();
        })

        $('.delete-image').click( function() {
            var _this = $(this);
            var request = $.ajax({
                type: "POST",
                url: "{{route('delete.post.image')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    post_id: "{{ $post->id }}"
                },
                success: function (result) {
                    $(_this).parent('.image').remove();
                    $('#dropzoneFileUpload').show();
                    $('.image-container').hide();
                }
            })

            request.fail( function (jqXHR, textStatus) {
                //alert ("Requeset failed: " + textStatus)
            })
        })
    })        
</script>
@endsection