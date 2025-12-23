@section ("jquery")

<script>
    window.ParsleyConfig = {
        errorsWrapper: '<div></div>',
        errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
        errorClass: 'has-error',
        successClass: 'has-success'
    };
    
    $(document).ready( function() {

        $('.inquire').click( function () {
            var _this = $(this);
            $('.inquiry-form')[0].reset();

            $.fancybox.open({
                src: "#product-inquiry",
                type: 'inline',
                beforeShow: function() {
                    $('.img-panel img').attr('src', $('.image img').attr('src'));
                    $('.img-panel .caption').text($('.title').text());
                    $('.img-panel .price').text('Price: '+$('.p_price').text());
                    if ($('.p_retail').length > 0)
                        $('.img-panel .retail').text('Retail: '+$('.p_retail').text());
                    else $('.img-panel .retail').hide();
                }
            });
        })


        Parsley.on('form:submit', function() {
            $.ajax ( {
                type: 'post',
                dataType: 'json',
                url: $('.inquiry-form').attr('action'),
                data: {inquiry: $('.inquiry-form').serialize(),_token: "{{csrf_token()}}"},
                success: function(response) {
                    // if ($.isEmptyObject(response.error)) {
                    if (response.error=='success') {
                        $.fancybox.close();
                        $.fancybox.open({
                            src: "<div><p style='padding: 30px 20px;width: 90%'>Your inquiry has been submitted successfully. Someone will get back to you as soon as possible.</p></div>",
                            type: 'html',
                        });
                    } else {
                        build = '';
                        for (i=0;i<response.error.length;i++) {
                            build = build+'<p style="margin:7px 18px 10px">'+response.error[i]+'</p>'
                        }
                        $.fancybox.open({
                            src: '<div style="padding: 30px 20px;width: 300px"><div class="popup-header"><h3 style="padding: 12px; text-align: left">There was an error</h3></div>'+build+'</div>',
                            type: 'html',
                        });
                    }
                }
            })

            return false;
        });
    });
</script>

@endsection

<div id="product-inquiry" style="max-width: 900px;display:none">
    <div class="popup-header">
        <h3 style="padding: 12px; text-align: left">Product Inquiry</h3>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-3 img-panel">
                <img src="" style="width:170px" class="mt-1" />
                <div class="caption"></div>
                <div class="price"></div>
                <div class="retail"></div>
            </div>
            <div class="col-md-9 form-panel">
                
                <div class="pb-2">Send an inquiry by filling out the form below</div>

                {{ Form::open(array('url' => 'ajaxInquiry', 'data-parsley-validate', 'class' => 'inquiry-form')) }}
                    <input type="hidden" value="{{ $product->id }}" name="product_id" id="product_id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('contact_name', 'Your Name') }}
                                {{ Form::text('contact_name', null, array('class' => 'form-control')) }}
                            </div>
                            <div class="form-group" id="company-group">
                                {{ Form::label('company_name', 'Company Name') }}
                                {{ Form::text('company_name', null, array(
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'data-parsley-required-message' => 'Company Name is required',
                                        'data-parsley-trigger'          => 'change focusout',
                                        'data-parsley-class-handler'    => '#company-group',
                                        'data-parsley-minlength'        => '2')) 
                                }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('email', 'Email Address') }}
                                {{ Form::text('email', null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('phone', 'Phone Number') }}
                                {{ Form::text('phone', null, 
                                        [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'data-parsley-required-message' => 'Phone Number is required',
                                        'data-parsley-trigger'          => 'change focusout',
                                        'data-parsley-class-handler'    => '#company-group',
                                        ]) 
                                }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('notes', 'Additional Notes') }}
                                {{ Form::textarea('notes', null, ['class' => 'form-control','rows' => 4, 'cols' => 40]) }}
                            </div>
                            <div class="g-recaptcha" data-sitekey="{{config('recapcha.key_v2') }}"></div>
                            @if ($errors->has('g-recaptcha-response'))
                                <span class="invalid-feedback" style="display: block;">
                                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                                </span>
                            @endif
                            <div class="pb-3 float-right">
                                {{ Form::submit('Send Inquiry', array('class' => 'btn btn-primary submit-inquiry')) }}
                            </div>
                        </div>
                        
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>