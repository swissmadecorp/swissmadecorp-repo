@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<div class="alert-info clearfix" style="padding: 3px">
    <div class="dropdown float-right">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>
<div class="table-responsive">
<table id="users" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>User Id</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        
    @foreach ($users as $user)
        <tr>
            <td></td>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name}}</td>
            <td>{{ $user->username }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</div>
<form action="users/create">
    <button type="submit" class="btn btn-primary">Create New</button>
</form>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
 var table = $('#users').dataTable({
            "deferRender": true,
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0,
            } ],
            "createdRow": function( row, data, dataIndex){
                if( data[5] ==  'Unpaid'){
                    $(row).addClass('unpaid');
                }
            },
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            "columns": [
                { "width": "5%" },
                { "width": "14%" },
                { "width": "20%" },
                { "width": "20%" },
                { "width": "20%" },
                { "width": "20%" }
            ]
        });

        $('#users tbody').on('click', 'td', function () {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'users/'+data[1]+'/edit';
            }
        } );

        $('.dropdown-menu button').click( function(e) {        
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;

            _id = id[0][1]
            
            if (e.currentTarget.innerText == 'Delete') {
                if (confirm('Are you sure you want to delete selected product?')) {
                    window.location.href="users/"+_id+'/destroy';
                }
            }
        })

    })    
</script>
@endsection