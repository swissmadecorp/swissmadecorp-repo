@extends('layouts.admin-default')

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
<table id="reminders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Id</th>
            <th>Page</th>
            <th>Criteria</th>
            <th>Assigned To</th>
            <th>Location</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($reminders as $reminder)
        <tr>
            <td>{{ $reminder->id }}</td>
            <td>{{ $reminder->page }}</td>
            <td>{{ $reminder->criteria }}</td>
            <td>{{ $reminder->assigned_to }}</td>
            <td>{{ $reminder->location }}</td>
            <td>{{ $reminder->action }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>

@include('admin.errors')

<form action="reminders/create">
    <button type="submit" class="btn btn-primary">Create New</button>
</form>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#reminders').dataTable({
            "deferRender": true,
        });

        table.api().column( 0 ).visible( false );
        $('#reminders tbody').on('click', 'td', function () {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'reminders/'+data[0]+'/edit';
            }
        } );
        
        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                window.location.href="reminders/"+id[0][0]+'/destroy';
            }
        })
    })    
</script>
@endsection