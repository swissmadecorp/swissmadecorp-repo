@extends('layouts.admin-default')

@section ('content')

<div class="col-lg-12 col-lg-offset-1">
    <a href="{{ route('users.index') }}" class="btn btn-default pull-right">Users</a>
    <a href="{{ route('roles.index') }}" class="btn btn-default pull-right">Roles</a></h1>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>Permissions</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                <tr>
                    <td>{{ $permission->name }}</td> 
                    <td>
                    <a href="{{ URL::to('admin/permissions/'.$permission->id.'/edit') }}" class="btn btn-sm btn-info pull-left" style="margin-right: 3px;">Edit</a>

                    <form method="POST" action="{{route('permissions.destroy',[$permission->id])}}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>


                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <a href="{{ URL::to('admin/permissions/create') }}" class="btn btn-success">Add Permission</a>

</div>

@endsection