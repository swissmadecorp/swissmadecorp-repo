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
        <div class="dropdown-menu" aria-labelledby="dropdownMenu2"  style="left: -53px">
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>
<div class="table-responsive">
<table id="categories" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th>Category Name</th>
            <th>Created At</th>
        </tr>
    </thead>

    <tbody>
        
        @foreach ($categories as $category)
            <tr>
                <td></td>
                <td>{{ $category->id }}</td>
                <td>{{ $category->category_name }}</td>
                <td>{{ $category->created_at->toFormattedDateString() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
<form action="categories/create">
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
        var table = $('#categories').dataTable({
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },
            "columns": [
                { "width": "10%" },
                { "width": "10%" },
                null,
                { "width": "20%" }
            ]
        });

        $('div.dataTables_filter input').focus();
        table.api().column( 1 ).visible( false );
        
        $('#categories tbody').on('click', 'td', function () {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'categories/'+data[1]+'/edit';
            }
        } );
        
        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
                        
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                window.location.href="categories/"+id[0][1]+'/destroy';
            }
        })
    })    
</script>
@endsection