@extends ("layouts.default")

@section('title', 'Contact Us')

@section ('header')
<link href="fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')

<h2>Contact Us</h2>
<hr>
    <div class="container" style="padding: 25px; background: #fff">
        <div class="row">
            <div class="col-md-4">
                <div class="contact-address box-shadow">
                    <i class="fas fa-map-marker-alt"></i>
                    <h6>Swiss Made Corp.</h6>
                    <h6>15 W 47th Street, Ste # 503</h6>
                    <h6>New York, NY 10036</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-address box-shadow">
                    <i class="fas fa-phone"></i>
                    <h6><a href="tel:212-840-8463">Phone: 212-840-8463</a></h6>
                    <h6><a href="tel:555-555-1212">Fax: 212-391-8463</a></h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-address box-shadow">
                    <i class="fas fa-envelope"></i>
                    <h6><a href="mailto:info@swissmadecorp.com">info@swissmadecorp.com</a></h6>
                </div>
            </div>
        
        
            <div class="col-sm-12 border p-2 mt-3">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.2171298816847!2d-73.98165538422266!3d40.75724887932698!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c258fef868bda1%3A0xb7ebc4fcc65c5822!2s15+W+47th+St%2C+New+York%2C+NY+10036!5e0!3m2!1sen!2sus!4v1500582532892" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
            </div>
        </div>
    </div>

@endsection

@section ('footer')
    <script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    
    <script src="{{ asset('/public/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/public/js/parsley.js') }}"></script>
@endsection
