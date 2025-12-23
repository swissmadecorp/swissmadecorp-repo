@extends('layouts.default')

@section('title', 'Testimony')

@section ('header') 
<link href="{{ asset('/public/lightgallery/css/lightgallery.css') }}" rel="stylesheet">    
@endsection

@section ('content')

<h2 class="text-center"><b>Testimony</b></h2>
<div class="mt-5"></div>

@if ($thankyou == 0 && $error == 0)
{{ Form::open(array('route'=>array('testimonies.store'), 'id' => 'testimonyForm')) }}
    <input type="hidden" name="code" value="<?= $_GET['code'] ?>">
    <div class="container">
        <p>Hello {{ $order->s_firstname . ' ' . $order->s_lastname }},</p>

        <P>Thanks again for shopping at Swiss Made Corp. How are you liking your purchase so far?</P>
        @foreach ($order->products as $product)
            <b>{{$product->pivot->product_name}}</b><br>
        @endforeach

        <br>
    
        <p>We would appreciate if you could leave feedback about your experience with our service, the website, and the checkout process using the fields below. 
        It would be incredibly helpful to us and other buyers as well.</p>

        <div class="form-group row">
            <div class="col-12">
            <label for="s_state-input" class="col-form-label">Your name</label>
                <select name="fullname" class="form-control">
                    <option value="{{$order->s_firstname . ' ' . $order->s_lastname}}">{{$order->s_firstname . ' ' . $order->s_lastname}}</option>
                    <option value="Remain anonymous">Remain anonymous</option>
                </select>
            </div>
        </div>
        
        <div class="form-group row">
            <div class="col-12">
                <label for="s_state-input" class="col-form-label">Title</label>
                <input type="text" value="" name="title" class="form-control"/>
            </div>
        </div>
        
        <div class="form-group row">
            <div class="col-12">
                <label for="comments-input" class="col-form-label">Feedback</label>
                <textarea type="text" name="feedback" rows="5" id="comments-input" class="form-control"></textarea>
            </div>    
        </div>

        <input type="submit" class="float-right btn btn-danger update" value="Submit"/><br>
        
    </div>
{{ Form::close() }}
@elseif ( $error == 1 )
    <div style="height: 500px" class="container">
    <h3>Thank you so much for your feedback. Your feedback will go to an appropriate department for review.</h3>
    </div>
@elseif ( $error == 2 )
    <div style="height: 500px" class="container">
    <h3>Thank you so much for taking the time to leave you feedback. However, we could not find your original order. </h3><br>
    <h5>If you received an email from us, please follow the link to be redirected to this page so that the system can locate your order</h5> 
    </div>
@else
    <div style="height: 500px" class="container">
    <h3>You have already left us a feedback.</h3> <br>
    <h5>Again, thank you for taking the time. We're looking forward to seeing you again in the future.</h5> 
    </div>
@endif

@endsection