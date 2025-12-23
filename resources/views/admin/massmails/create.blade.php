@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/public/multiselect/chosen.min.css') }}" rel="stylesheet">
@endsection

@section ('content')

<form method="POST" action="{{route('massmail.store')}}" id="massmailform">
    @csrf
    <input type="hidden" name="new_id" id="new_id" />

    <div class="form-group row">
        <label for="title-input" class="col-3 col-form-label">Title *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('title')) ? old('title') : '' ?>" type="text" placeholder="Enter title" name="title" id="title-input" required>
        </div>
    </div>  

    <div class="form-group row">
        <label for="subtitle-input" class="col-3 col-form-label">Categories</label>
        <div class="col-9">
            <select data-placeholder="Choose category ..." class="chosen-select form-control" name="category[]" multiple>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->category_name}}</option>
            @endforeach
            </select> 
        </div>
    </div>  

    <div class="form-group row">
        <label for="massmail-input" class="col-3 col-form-label">Content</label>
        <div class="col-9">
            <textarea rows="4" class="form-control" value="<?php echo !empty(old('massmails')) ? old('massmails') : '' ?>" type="text" placeholder="Enter massmail" name="massmails" id="massmail-input"></textarea>
        </div>
    </div>


    <button type="submit" class="btn btn-primary sendEmails">Send Emails</button>
    @include('admin.errors')
</form>
@endsection

@section ('footer')
<script src="{{ asset('/public/multiselect/chosen.jquery.js') }}"></script>
<script src="{{ asset('/public/js/tinymce/tinymce.min.js') }}"></script>
@endsection

@section ('jquery')
<script>

    var template = '';

    $.ajax({
        url: "{{route('mail.load.template')}}",
        async: false,
        success: function (result) {
            template = result;
        }
    })
        tinymce.init({ 
            selector:'#massmail-input', 
            relative_urls: false,
            verify_html : false,
            remove_script_host : false,
            save_enablewhendirty: true,
            entity_encoding: "raw",
            document_base_url: 'https://swissmadecorp.com/',
            valid_children: '+body[style]',
            valid_elements : '+*[*]',
            plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
            imagetools_cors_hosts: ['picsum.photos'],
            menubar: 'file edit view insert format tools table help',
            toolbar: 'code | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
            toolbar_sticky: true,
            autosave_ask_before_unload: true,
            autosave_interval: "30s",
            autosave_prefix: "{path}{query}-{id}-",
            autosave_restore_when_empty: false,
            autosave_retention: "2m",
            image_advtab: true,
            content_css: [
                '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
            ],
            link_list: [
                { title: 'My page 1', value: 'http://www.tinymce.com' },
                { title: 'My page 2', value: 'http://www.moxiecode.com' }
            ],
            image_list: [
                { title: 'My page 1', value: 'http://www.tinymce.com' },
                { title: 'My page 2', value: 'http://www.moxiecode.com' }
            ],
            image_class_list: [
                { title: 'None', value: '' },
                { title: 'Some class', value: 'class-name' }
            ],
            save_onsavecallback: function () { alert('Saved'); },
            //setup: (editor) => {
                //editor.on('init', function () {
                    //this.setContent('The init function knows on which editor its called - this is for ' + editor.id);
                    //loadProducts(editor);
                //});

            //},
            branding: false,
            force_br_newlines : true,
            force_p_newlines : false,
            forced_root_block : '',
            advlist_bullet_styles: "square",  // only include square bullets in list
            importcss_append: true,
            height: 400,
            file_picker_callback: function (callback, value, meta) {
                /* Provide file and text for the link dialog */
                if (meta.filetype === 'file') {
                callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
                }

                /* Provide image and alt text for the image dialog */
                if (meta.filetype === 'image') {
                callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
                }

                /* Provide alternative source and posted for the media dialog */
                if (meta.filetype === 'media') {
                callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
                }
            },

            templates: [
                   { title: 'Standard Template', description: 'Load Standard Template', content: template },
            //     { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
            //     { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
            ],
            // template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
            // template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
            height: 600,
            //image_caption: true,
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
            noneditable_noneditable_class: "mceNonEditable",
            toolbar_drawer: 'sliding',
            contextmenu: "link image imagetools table",
        });

    $(document).ready( function() {
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
    })
</script>
@endsection