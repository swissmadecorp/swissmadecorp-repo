@extends('layouts.admin-default')

@section('content')

<div class='col-lg-12 col-lg-offset-4'>
    <form method="POST" action="{{route('permissions.update',[$permission->id])}}">
    @csrf
    @method('PATCH')

    <div class="form-group">
        <label for="name">Permission Name</label>
        <input id="name" class="form-control" name="name" type="text" value="{{$permission->name}}">
    </div>
    <br>
    
    <button type="submit" class="btn btn-primary">Save</button>

    </form>

</div>

@endsection