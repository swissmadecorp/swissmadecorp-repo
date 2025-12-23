@extends('layouts.admin-default')

@section('content')

<div class='col-lg-12 col-lg-offset-4'>

<form method="POST" action="{{route('permissions.store')}}">

<div class="form-group">
    <label for="name">Name</label>
    <input id="name" class="form-control" name="name" type="text"">
</div><br>
@if(!$roles->isEmpty()) <?php //If no roles exist yet ?>
    <h4>Assign Permission to Roles</h4>
    @foreach ($roles as $role) 
    <input type="checkbox" name="roles[]" value="{{$role->id}}">
    <label for="{{$role->name}}" class="col-3 col-form-label">{{ucfirst($role->name)}}</label><br>
    @endforeach
@endif
<br>
<button type="submit" class="btn btn-primary">Add</button>

</form>
</div>

@endsection