@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/multiselect/chosen.min.css') }}" rel="stylesheet">
<link href="{{ asset('/multiple-select/js/bootstrap-multiselect.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section ('footer')
<script src="{{ asset('/multiselect/chosen.jquery.js') }}"></script>
<script src="{{ asset('/multiple-select/js/bootstrap-multiselect.min.js') }}"></script>
@endsection

@section ('content')
{{  Form::open(array('route'=>array('discountrules.store'))) }}  
    <div class="form-group row">
        <label for="rule_name-input" class="col-3 col-form-label">Rule Name</label>
        <div class="col-9">
            <input class="form-control" autofocus value="<?php echo !empty(old('rule_name')) ? old('rule_name') : '' ?>" type="text" name="rule_name" id="rule_name-input" >
        </div>
    </div>
    <div class="form-group row">
        <label for="meta-title-input" class="col-3 col-form-label">Meta Title</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('title')) ? old('title') : '' ?>" type="text" name="title" id="meta-title-input" >
        </div>
    </div>    
    <div class="form-group row description">
        <label for="description-input" class="col-3 col-form-label">Description</label>
        <div class="col-9">
            <textarea rows="4" class="form-control" value="<?php echo !empty(old('description')) ? old('description') : '' ?>" type="text" name="description" id="description-input"></textarea>
        </div>
    </div>
    <div class="form-group row">
        <label for="amount-input" class="col-3 col-form-label">Amount</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('amount')) ? old('amount') : '' ?>" type="text" name="amount" id="amount-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="free_shipping-input" class="col-3 col-form-label">Free Shipping</label>
        <div class="col-9 input-group">
        <select data-placeholder="Choose a job ..." class="chosen-select" name="free_shipping" id="free_shipping-input">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>    

    <div class="form-group row">
        <label for="action-input" class="col-3 col-form-label">Action</label>
        <div class="col-9">
            <select data-placeholder="Choose a job ..." class="chosen-select" name="action" id="action-input">
                @foreach (DiscountRules() as $key => $discount)
                <option value="{{$key}}">{{$discount}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="is_active-input" class="col-3 col-form-label">Is Active</label>
        <div class="col-9 input-group">
            <select data-placeholder="Choose a job ..." class="chosen-select" name="is_active" id="is_active-input" >
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="discount_code-input" class="col-3 col-form-label">Discount Code</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('discount_code')) ? old('discount_code') : '' ?>" type="text" name="discount_code" id="discount_code-input">
        </div>
    </div>

    <div class="form-group row">
        <label for="start_date-input" class="col-3 col-form-label">Start Date</label>
        <div class="col-9 input-group">
            <input type="text" class="form-control" name="start_date" value="<?php echo !empty(old('start_date')) ? old('start_date') : '' ?>" id="start_date">
        </div>
    </div>
    
    <div class="form-group row">
        <label for="end_date-input" class="col-3 col-form-label">End Date</label>
        <div class="col-9 input-group">
            <input type="text" class="form-control" name="end_date" value="<?php echo !empty(old('end_date')) ? old('end_date') : '' ?>" id="end_date">
        </div>
    </div>

    <div class="form-group row">
        <label for="cp-input" class="col-3 col-form-label">Products</label>
        <div class="col-9 input-group">
            <select style="display: none;" id="cp-input" name="product[]" multiple>
                <option value="multiselect-all"> Select all</option>
                @foreach ($products as $product)
                <option value="{{$product->id}}">{{$product->id}}-{{$product->title}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>

    @include('admin.errors')
{{  Form::close() }}  
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        $( "#start_date" ).datepicker();
        $( "#end_date" ).datepicker();

        $('#cp-input').multiselect({
            includeSelectAllOption: true,
            enableCaseInsensitiveFiltering: true,
            maxHeight: 300
        });

        var config = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : { allow_single_deselect: true },
            '.chosen-select-no-single' : { disable_search_threshold: 10 },
            '.chosen-select-no-results': { no_results_text: 'Oops, nothing found!' },
            '.chosen-select-rtl'       : { rtl: true },
            '.chosen-select-width'     : { width: '95%' },
            'no_results_text'          : "No result found. Press enter to add "
        }

        initChosen()
        function initChosen() {
            for (var selector in config) {
                $(selector).chosen(config[selector]);
            }
        }
    })
</script>
@endsection