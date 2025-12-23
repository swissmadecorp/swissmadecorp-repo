@extends('layouts.admin-default')

@section ('header')
<link href="{{ asset('/public/multiselect/chosen.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section ('footer')
<script src="{{ asset('/public/multiselect/chosen.jquery.js') }}"></script>
@endsection

@section ('content')

<form method="POST" action="{{route('discountrules.update',[$discountrule->id])}}" accept-charset="UTF-8">
    @csrf
    @method('PATCH')
    <div class="form-group row">
        <label for="rule_name-input" class="col-3 col-form-label">Rule Name</label>
        <div class="col-9">
            <input class="form-control" value="{{ $discountrule->rule_name ?? $discountrule->rule_name }}" type="text" name="rule_name" id="rule_name-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="title-input" class="col-3 col-form-label">Meta Title</label>
        <div class="col-9">
            <input class="form-control" value="<?= !empty($discountrule->title) ? $discountrule->title : '' ?>" type="text" name="title" id="title-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="description-input" class="col-3 col-form-label">Description</label>
        <div class="col-9">
            <textarea rows="4" class="form-control" type="text" placeholder="Enter discount description" name="description" id="description-input">{{ !empty($discountrule->description) ? $discountrule->description : ''}} </textarea>
        </div>
    </div>    
    <div class="form-group row">
        <label for="amount-input" class="col-3 col-form-label">Amount</label>
        <div class="col-9">
            <input class="form-control" value="<?= $discountrule->amount ?? $discountrule->amount ?>" type="text" name="amount" id="amount-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="free_shipping-input" class="col-3 col-form-label">Free Shipping</label>
        <div class="col-9 input-group">
        <select data-placeholder="Choose a job ..." class="chosen-select" name="free_shipping" id="free_shipping-input">
                <option value="0">No</option>
                <option value="1" {{ $discountrule->free_shipping ?? 'selected' }}>Yes</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="action-input" class="col-3 col-form-label">Action</label>
        <div class="col-9">
            <select data-placeholder="Choose a job ..." class="chosen-select" name="action" id="action-input">
                @foreach (DiscountRules() as $key => $discount)
                <option value="{{$key}}" {{ $discountrule->action == $key ?? 'selected' }}>{{$discount}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="is_active-input" class="col-3 col-form-label">Is Active</label>
        <div class="col-9 input-group">
            <select data-placeholder="Choose a job ..." class="chosen-select" name="is_active" id="is_active-input">
                <option value="0">No</option>
                <option value="1" {{ $discountrule->is_active ?? 'selected' }}>Yes</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="discount_code-input" class="col-3 col-form-label">Discount Code</label>
        <div class="col-9 input-group">
            <input class="form-control" value="{{ $discountrule->discount_code ?? $discountrule->discount_code }}" type="text" name="discount_code" id="discount_code-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="start_date-input" class="col-3 col-form-label">Start Date</label>
        <div class="col-9 input-group">
            <input class="form-control" value="{{ $discountrule->start_date ?? date('m/d/Y',$discountrule->start_date) }}" type="text" name="start_date" id="start_date">
        </div>
    </div>
    <div class="form-group row">
        <label for="end_date-input" class="col-3 col-form-label">End Date</label>
        <div class="col-9 input-group">
            <input class="form-control" value="{{ $discountrule->end_date ?? date('m/d/Y', $discountrule->end_date) }}" type="text" name="end_date" id="end_date">
        </div>
    </div>    

    <div class="form-group row">
        <label for="product-input" class="col-3 col-form-label">Products</label>
        <div class="col-9">
            @if ($discountrule->product)
            <?php $array_product = unserialize($discountrule->product);?>
            <select data-placeholder="Choose product(s) ..." size="20" class="chosen form-control" name="product[]" id="product-input" multiple>
                @foreach ($products as $product)
                <option value="{{$product->id}}" <?= in_array($product->id,$array_product) ? "selected" : "" ?>>{{$product->id}}-{{$product->title}}</option>
                @endforeach
            </select>
            @else
                <select data-placeholder="Choose product(s) ..." size="20" class="chosen form-control" name="product[]" id="product-input" multiple>
                @foreach ($products as $product)
                <option value="{{$product->id}}">{{$product->id}}-{{$product->title}}</option>
                @endforeach
            </select>
            @endif
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-4">Update</button>
    @include('admin.errors')
</form>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        $( "#start_date" ).datepicker();
        $( "#end_date" ).datepicker();

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
@endsection;