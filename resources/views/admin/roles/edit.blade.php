@extends('layouts.admin-default')

@section('content')

<div class='col-lg-12 col-lg-offset-4'>
    <form method="POST" action="{{route('roles.update',[$role->id])}}">
    @csrf
    @method('PATCH')

    <div class="form-group">
        <label for="name">Role Name</label>
        <input class="form-control" name="name" type="text" value="{{$role->name}}" id="name">
    </div>

    <h5><b>Assign Permissions</b></h5>
    <?php $userPermissions=$role->permissions->toArray() ?>
    @foreach ($permissions as $permission)
        <input type="checkbox" name="permissions[]" value="{{$permission->id}}" <?= (in_array($permission->name,array_column($userPermissions,'name'))) ? 'checked="checked"' : "" ?>>
        <label for="{{$permission->name}}" class="col-3 col-form-label">{{ucfirst($permission->name)}}</label><br>
    @endforeach
    <br>
    <button type="submit" class="btn-primary">Save</button>

    @include('admin.errors')
    </form>
</div>

@endsection