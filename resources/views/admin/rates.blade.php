@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')
<table id="rates" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Id</th>
            <th>Title</th>
            <th>Rate</th>
            <th>Symbol</th>
            <th>Date</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>

    @foreach ($rates as $rate)
        <tr>
            <td>{{ $rate->id }}</td>
            <td>{{ $rate->currency_name }}</td>
            <td>{{ $rate->rate }}</td>
            <td>{{ $rate->symbol }}</td>
            <td>{{ $rate->created_at->format('m-d-Y') }}</td>
            <td style="text-align: center">
            {!! Form::open(['method' => 'DELETE', 'route' => ['rates.destroy', $rate->id] ]) !!}
                <button type="submit" style="padding: 3px 5px" data-id="{{ $rate->id }}" class="btn btn-danger deleteitem" aria-label="Left Align">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
                {!! Form::close() !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<form action="rates/create">
    <button type="submit" class="btn btn-success">Create New</button>
</form>
@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#rates').DataTable({
            "deferRender": true,
            "columns": [
                { "width": "5%" },
                { "width": "25%" },
                { "width": "5%" }
                { "width": "5%" },
                { "width": "5%" }
            ]
        });

        $('#rates tbody').on('click', 'td', function () {
            var data = table.row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.column.index( 'fromVisible', visIdx );

            if (table.column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'rates/'+data[0]+'/edit';
            }
        } );
        
        $('.deleteitem').click( function(e) {
            e.stopPropagation()
            if (!confirm('Are you sure you want to delete this rate?')) {
                e.preventDefault();
                return false
            }

            return true
        })

    })    
</script>
@endsection