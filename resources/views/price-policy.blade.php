@extends ("layouts.default1")

@section('title', 'Price Policy')

@section ('header')
<link href="{{ asset('/public/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
@endsection

@section ('content')
    <h1>Price Policy</h1>
    <hr>
    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            <div class="row" style="padding-top: 10px">
                <div class="col-sm-12">
                    <p>
                        Our website, swissmadecorp.com and its prices, are for watch dealers only. 
                        If you are a watch dealer, you are welcome to purchase our products from our 
                        website for discounted prices.   
                    </p>
                    <p>
                        <b>If you are a watch dealer you must:</b>
                        <ul>
                            <li>provide references from other watch dealers and</li>
                            <li>provide a watch dealer's Tax-ID.</li>
                        </ul>
                    </p>
                    <p>
                        Swiss Made Corp. generally does not sell to general public but sometimes does make an
                        exception. If you are interested in one or more of our watches, you may
                        contact us and we will give you a price quote. Swiss Made Corp. does not 
                        offer a <b>buy now</b> feature simply because Swiss Made Corp. does not accept 
                        any form of payments accept for a Wire Transfer. 
                    </p>
                    <p>
                        Swiss Made Corp. reserves the right to change these terms and conditions 
                        at any time without prior notice.
                    </p>
                    <p>
                        If you have questions, please contact us by mail <a href="mailto:info@swissmadecorp.com">info@swissmadecorp.com</a> 
                        or you may contact us by phone 212-840-8463.
                    </p>
                </div>
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
