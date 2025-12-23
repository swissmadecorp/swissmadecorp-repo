@extends('layouts.admin-default')

@section ('content')

<form method="POST" action="{{route('users.update',[$user->id])}}" accept-charset="UTF-8" id="userform">
    @csrf
    @method('PATCH')
    <div class="form-group row">
        <label for="name" class="col-3 col-form-label">Name</label>
        <div class="col-9">
            <input id="name" class="form-control" name="name" type="text" value="{{$user->name}}">
        </div>    
    </div>
    <div class="form-group row">
        <label for="username" class="col-3 col-form-label">Username</label>
        <div class="col-9">
        <input id="username" class="form-control" name="username" type="text" value="{{$user->username}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="email" class="col-3 col-form-label">Email</label>
        <div class="col-9">
        <input id="email" class="form-control" name="email" type="text" value="{{$user->email}}">
        </div>
    </div>
    <div class='form-group row'>
        <label for="roles" class="col-3 col-form-label">Roles</label>

        <div class="col-9">
        <?php $userRoles = $user->roles->toArray(); ?>
        @foreach ($roles as $role)
        <input type="checkbox" name="roles[]" value="{{$role->id}}" <?= (in_array($role->name,array_column($userRoles,'name'))) ? 'checked="checked"' : "" ?>>
        <label for="{{$role->name}}" class="col-3 col-form-label">{{ucfirst($role->name)}}</label><br>
        @endforeach
        </div>
    </div>    
    <div class="form-group row">
        <label for="password" class="col-3 col-form-label">Password</label>
        <div class="col-9">
        <input id="password" class="form-control" name="password" type="password" placeholder="Enter new password" value="">
        </div>    
    </div>
        <div class="form-group row">
        <label for="password_confirmation" class="col-3 col-form-label">Password Confirmation</label>
        <div class="col-9">
        <input id="password_confirmation" class="form-control" name="password_confirmation" type="password" placeholder="Confirm your new password" value="">
        </div>
    </div>

    <button type="submit" class="btn-primary">Save</button>
    @include('admin.errors')
</form>
@endsection