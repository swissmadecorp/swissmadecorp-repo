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
        <div class="dropdown-menu" aria-labelledby="dropdownMenu2" style="left: -53px">
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>
<div class="table-responsive">
<table id="inquiries" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th>Product Id</th>
            <th>Contact Name</th>
            <th>Company</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($inquiries as $inquiry)
        <tr>
            <td></td>
            <td>{{ $inquiry->id }}</td>
            <td>{{ $inquiry->product_id }}</td>
            <td>{{ $inquiry->contact_name }}</td>
            <td>{{ $inquiry->company_name }}</td>
            <td>{{ $inquiry->created_at->format('m-d-Y')}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
<form action="inquiries/create">
    <button type="submit" class="btn btn-primary">Create New</button>
</form>

@include('admin.errors')
@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#inquiries').dataTable({
            "deferRender": true,
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            } ],
            select: {
                style:    'os',
                selector: 'td:first-child'
            },

        });

        table.api().column( 1 ).visible( false );
        $('#inquiries tbody').on('click', 'td', function () {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'inquiries/'+data[1];
            }
        } );
        
        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                window.location.href="inquiries/"+id[0][1]+'/destroy';
            }
        })
    })    
</script>
@endsection