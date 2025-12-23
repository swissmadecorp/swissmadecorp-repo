@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/css/dropzone.css') }}" rel="stylesheet">
@endsection

@section ('content')
{{  Form::open(array('route'=>array('rates.store'), 'id' => 'rateform')) }}
    <div class="form-group row">
        <label for="currency-name-input" class="col-3 col-form-label">Currency Title *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('currency_name')) ? old('currency_name') : '' ?>" type="text"  name="currency_name" autofocus id="currency-name-input" required>
        </div>
    </div>  

    <div class="form-group row">
        <label for="symbol-input" class="col-3 col-form-label">Symbol *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('symbol')) ? old('symbol') : '' ?>" type="text" name="symbol" id="symbol-input" required>
        </div>
    </div>  

    <div class="form-group row">
        <label for="rate-input" class="col-3 col-form-label">Rate *</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('rate')) ? old('rate') : '' ?>" type="text" name="rate" id="rate-input" required>
        </div>
    </div>  

    <button type="submit" class="btn btn-primary uploadPhoto">Save</button>
    @include('admin.errors')
{{  Form::close() }}  
@endsection

@section ('footer')
<script src="{{ asset('/js/dropzone.js') }}"></script>
<script src="{{ asset('/js/tinymce/tinymce.min.js') }}"></script>
@endsection