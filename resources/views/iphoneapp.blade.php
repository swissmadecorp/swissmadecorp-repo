@extends('layouts.default')

@section('title', 'iPhone App')

@section ('header')
<link href="{{ asset('/public/fancybox/jquery.fancybox.min.css') }}" rel="stylesheet">
@endsection

@section ('content')
<h2>iPhone App</h2>
<hr>
<div class="content">
    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            <h5>Hello and welcome to our our iPhone application. Here you are able to download our iPhone app.</h5>
            
            <p>Take advantage of our inventory app that is specifically designed for dealers who want to simplify browsing experience.

            Please click on download link below and install the program. 
            
            <p>If you have any issues or you want something added to the app, please contact us.</p>

            <p>Thank you, and we hope you enjoy our app.</p>
            
            <p><a href="itms-services://?action=download-manifest&url=https://swissmadecorp.com/public/app/manifest.plist"">Download</a></p>

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
