@extends ("layouts.default")

@section('title', 'Order Cancellation')

@section ('content')
    <h2>Order Cancellation</h2>
    <hr>

    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            
            <h3>You have canceled the PayPal payment.</h3>
            <br>
            We are sorry that you decided to cancel the order.<br><br>
            If you think you're getting this messege in error, please contact us and we 
            will try to help you.
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
