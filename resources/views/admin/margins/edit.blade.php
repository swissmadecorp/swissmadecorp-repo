@extends('layouts.admin-default')

@section ('content')
 {{  Form::model($category, array('route' => array('category.update', $category->id), 'method' => 'PATCH')) }} 
    <div class="form-group row">
        <label for="category-name-input" class="col-3 col-form-label">Category Name:</label>
        <div class="col-9">
            <input class="form-control" type="text" value="{{ $category->category_name }}" placeholder="Edit existing category name" name="category_name" id="category-name-input">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    
    @include('admin.errors')
    
{{  Form::close() }}  
@endsection

