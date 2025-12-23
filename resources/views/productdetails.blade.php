@extends ("layouts.default-new")

@section ('header')
<link href='/calendar-master/dist/css/pignose.calendar.min.css' rel="stylesheet">
<link href='/lightslider/css/lightslider.css' rel="stylesheet">
<link href='/lightgallery/css/lightgallery.css' rel="stylesheet">
<script src="/js/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="/calendar-master/dist/js/pignose.calendar.full.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render={{config('recapcha.key') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    ul.return-policy-text {list-style: disc;margin: 15px;padding:0 0 0 15px}
    ul.return-policy-text li {padding: 3px}
</style>

@endsection

@section("content")
    @livewire('product-details') 
@endsection

@section ('footer')
<!-- PARSLEY -->
<script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    <script src="/js/parsley.js"></script>
    <script src="/fancybox/jquery.fancybox.min.js"></script>
    <script src="/lightgallery/js/lightgallery-all.min.js"></script>
    <script src="/lightgallery/js/lg-thumbnail.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.umd.min.js"></script>
@endsection

