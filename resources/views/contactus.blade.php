@extends ("layouts.default-new")

@section('title', 'Contact Us')

@section ('content')
<div>
    <div class="flex justify-center">
        <div class="container p-5">
            <h1 class="text-3xl uppercase pb-2">Contact us</h1>
            <hr>
            <div class="sm:flex justify-between p-5">
                <div class="contact-address box-shadow flex flex-col items-center justify-between h-40 py-4 group">
                    <i class="fas fa-map-marker-alt text-2xl text-center group-hover:text-red-600"></i>
                    <div class="text-center space-y-1">
                        <h6>Swiss Made Corp.</h6>
                        <h6>15 W 47th Street, Ste # 503</h6>
                        <h6>New York, NY 10036</h6>
                    </div>
                </div>
            
            
                <div class="contact-address box-shadow flex flex-col items-center justify-between h-40 py-4 group">
                    <i class="fas fa-phone text-2xl text-center group-hover:text-red-600"></i>
                    <div class="text-center space-y-1">
                        <h6><a href="tel:212-840-8463">Phone: 212-840-8463</a></h6>
                        <h6><a href="tel:555-555-1212">Fax: 212-391-8463</a></h6>
                    </div>
                </div>
            
            
                <div class="contact-address box-shadow flex flex-col items-center justify-between h-40 py-4 group">
                    <i class="fas fa-envelope text-2xl text-center group-hover:text-red-600"></i>
                    <div class="text-center space-y-1">
                        <h6><a href="mailto:info@swissmadecorp.com">info@swissmadecorp.com</a></h6>
                    </div>
                </div>
            </div>
        
        
            <div class="col-sm-12 border p-2 mt-3">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.2171298816847!2d-73.98165538422266!3d40.75724887932698!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c258fef868bda1%3A0xb7ebc4fcc65c5822!2s15+W+47th+St%2C+New+York%2C+NY+10036!5e0!3m2!1sen!2sus!4v1500582532892" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
            </div>
               
        </div>
    </div>
</div>
@endsection