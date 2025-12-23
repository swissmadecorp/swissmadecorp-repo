@extends('layouts.admin-default')

@section ('header')
<link href="/multiselect/chosen.min.css" rel="stylesheet">
@endsection

@section ('content')

<form method="POST" action="{{route('reminders.store')}}" accept-charset="UTF-8" id="reminderForm">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="form-group row">
                <label for="page-name-input" class="col-3 col-form-label">Page Name *</label>
                <div class="col-9">
                    <select class="form-control" id="page" name="page" required>
                        <option value="product">Product</option>
                        <option value="order">Order</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="criteria-input" class="col-3 col-form-label">Criteria</label>
                <div class="col-9">
                    <input class="form-control" type="text" name="criteria" id="criteria-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="assignedto-input" class="col-3 col-form-label">Assign To</label>
                <div class="col-9">
                    <input class="form-control" type="text" name="assigned_to" id="assignedto-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="location-input" class="col-3 col-form-label">Location</label>
                <div class="col-9">
                    <input class="form-control" type="text"  name="location" id="location-input">
                </div>
            </div>
            <div class="form-group row">
                <label for="action-input" class="col-3 col-form-label">When action is</label>
                <div class="col-9">
                    <select class="form-control" id="action" name="action" required>
                        <option value="new">New</option>
                        <option value="update">Update</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="condition-input" class="col-3 col-form-label">Condition</label>
                <div class="col-9">
                    <select class="chosen-select" name="product_condition[]" multiple>
                        @foreach (Conditions() as $key => $condition)
                        <option <?php echo !empty(old('product_condition')) && old('product_condition')==$key ? 'selected' : !empty($product->category_id) && ($product->category_id==$category->id ? 'selected' : '') ?> value="{{ $key }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="condition-input" class="col-3 col-form-label">Box/Paper</label>
                <div class="col-9">
                    <select class="chosen-select" name="boxpapers[]" multiple>
                        <option>Box</option>
                        <option>Papers</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </div> 


    @include('admin.errors')
    </form>
    <div id="search-customer"></div>

@endsection

@section ('footer')
<script src="/multiselect/chosen.jquery.js"></script>
@endsection

@section ('jquery')
<script>

    $(document).ready( function() {
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