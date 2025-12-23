@extends ("layouts.default")

@section('title', 'Order Confirmation')

@section ('header')
    <style>
    .table-borderless td,
        .table-borderless th {
            border: 0 !important;
            padding: 0 !important;
        }
    </style>
@endsection

@section ('content')

<h3>Unsuccessful credit card attempt.</h3>
<div style="background: #fff; padding: 25px;border-radius: 4px;">
    <div class="container">
        <div class="col-md-12">
            <br>
            <h4 class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</h4>
            <br>
            <h5 style="line-height: 30px">We apologize but it seems that the credit card information that you entered may have been entered wrong. 
                <br>Please check if all the numbers entered are matching the card numbers including date, 
                year, and the secret code and try again.</h5>

            <br>
            <p>Please hit the back button to re-enter your credit card.</p>
            <button class="btn btn-primary" onclick="javascript:window.history.back();">Back</button>
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
