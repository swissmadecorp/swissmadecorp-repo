<div id="sell-watch-inquiry" style="width: 600px; max-width: 100%;display:none">
    <div class="popup-header">
        <h3 style="padding: 12px; text-align: left">Sell Your Watch</h3>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 form-panel">
                
                <div class="pb-2">Want to sell your watch quickly? Please fill in all the informtion below and someone will conctact you.</div>
                <form method="POST" action="https://swissmadecorp.com/SellYourWatch" accept-charset="UTF-8" data-parsley-validate="" class="sell-watch-form" novalidate="" enctype="multipart/form-data" siq_id="autopick_3635">
                    @csrf
                    <input type="hidden" name="blade" value="sell-watch-form">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="sell_contact_name">Your Name</label>
                                <input type="text" id="sell_contact_name" name="sell_contact_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sell_email">Email Address</label>
                                <input class="form-control" required="required" 
                                    data-parsley-required-message="Email Address is required" 
                                    data-parsley-trigger="change focusout" 
                                    data-parsley-class-handler="#company-group" 
                                    name="sell_email" type="text" id="sell_email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sell_phone">Phone Number</label>
                                <input type="text" id="sell_phone" name="sell_phone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="sell_notes">Watch Details</label>
                                <textarea name="sell_notes" id="sell_notes" cols="40" rows="4"
                                    class ='form-control'
                                    data-parsley-required-message='Please describe the watch' 
                                    data-parsley-trigger='change focusout' 
                                    data-parsley-class-handler='#company-group'
                                    required>

                                </textarea>
                            </div>
                            <div class="form-group">
                                <label for="upload_photos">You can upload up to 6 photos at a time</label>
                                <div id="dropzoneFileUpload" style="padding:35px 20px" class="dropzone" required></div>
                            </div>
                            
                            
                            <div class="pb-3 float-right">
                            <input type="submit" class="btn btn-primary submit-sell" value="Submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>

<script>
    
    $(document).ready( function() {
        var $selector = $('.submit-sell'),form = $selector.parsley();
        Dropzone.autoDiscover = false;
        
        var myDropzone = new Dropzone("div#dropzoneFileUpload", {
            url: "{{url('uploadCustomerFiles')}}",
            maxFilesize: 10, // MB
            maxFiles: 6,
            parallelUploads: 6,
            acceptedFiles: 'image/*',
            dictDefaultMessage:'Drop files here or click to upload manually',
            addRemoveLinks: true,
            autoProcessQueue:false,
            uploadMultiple: true,
            sending: function(file, xhr, formData) {
                $(".sell-watch-form").find("input").each(function(){
                    if ($(this).attr("name") !==undefined && $(this).attr("name")!='_method')
                        formData.append($(this).attr("name"), $(this).val());
                });

                formData.append('sell_notes',$('#sell_notes').val());

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

                $.fancybox.close()
                $.fancybox.open({
                    src: "<div><p style='padding: 30px 20px;width: 90%'>Your inquiry has been submitted successfully. Someone will get back to you as soon as possible.</p></div>",
                    type: 'html',
                });
            }
        });

        $('.sell-your-watch button').click( function () {
            var _this = $(this);
            myDropzone.removeAllFiles();
            $('.sell-watch-form')[0].reset();

            $.fancybox.open({
                src: "#sell-watch-inquiry",
                type: 'inline',
            });
        })
        
        $(".submit-sell").click( function(e) {
            if ($('.dz-image-preview').length > 0)
                e.preventDefault();
            
            //if ($('.activeSnapshot').is(':visible'))
                myDropzone.processQueue();
        })

    })
</script>