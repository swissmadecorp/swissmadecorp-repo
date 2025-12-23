@extends('layouts.admin-default')

@section ('content')
 {{  Form::open(array('action'=>'CategoriesController@store', 'method' => 'post')) }}  
    <div class="form-group row">
        <label for="category-name-input" class="col-3 col-form-label">Category Name:</label>
        <div class="col-9">
            <input class="form-control" type="text" placeholder="Enter new category name" name="category_name" id="category-name-input">
        </div>
    </div>
  <button type="submit" class="btn btn-primary">Save</button>

  @include('admin.errors')
  
{{  Form::close() }}  
@endsection
