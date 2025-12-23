@extends('layouts.default')

@section('title', 'Account')

@section ('header')
<link href="fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
<h2>Account</h2>
<hr>
<div class="content">
    <div style="background: #fff; padding: 30px 25px;border-radius: 4px;">
        <div class="container">
            <h5>Please enter your zip code and your invoice number below and click the "Find Order" button. If you have an order with us, your order will be listed below.</h5>
            <br>
            
            <form method="POST" action="{{route('orders.find')}}" accept-charset="UTF-8" id="orderForm" autocomplete="off">
            @csrf
            <div class="form-group row">
                <div class="col-12">
                    <label for="zip-input" class="col-form-label">Zip Code</label>
                    <input type="text" id="zip-input" name="zip" class="form-control" required>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-12">
                    <label for="invoice-input" class="col-form-label">Invoice No.</label>
                    
                    <input type="text" name="id" id="invoice-input" class="form-control" required>
                </div>
            </div>


            <br><br>
            <div class="mr-3">
                <button type="submit" class="btn btn-primary update">Find Order</button>
            </div>
            </form>

            @include('admin.errors')
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
    <script src="/fancybox/jquery.fancybox.min.js"></script>
    <script src="/js/parsley.js"></script>
@endsection
