@extends('layouts.admin-default')

@section ('content')

<div class="alert-info clearfix" style="padding: 3px">
    <div class="dropdown float-right">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu" style="left: -53px" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>
<div class="table-responsive">
<table id="customers" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th>Company</th>    
        </tr>
    </thead>

    <tbody>
    
    </tbody>
</table>
</div>
<form action="customers/create">
    <button type="submit" class="btn btn-primary">Create New</button>
</form>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#customers').dataTable({
            "deferRender": true,
            ajax: {
                url: "{{ route('get.customers') }}",
                data: function(d){
                    return status
                },
                dataSrc: function(json) {
                    return json.data;
                }   
            },
            'processing': true,
                'language': {
                    'loadingRecords': '&nbsp;',
                    'processing': '<div class="spinner"></div>'
            },
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
                { "width": "5%" },
                { "width": "10%" },
                { "width": "20%" },
            ]
        });

        table.api().column( 1 ).visible( false );
        $('div.dataTables_filter input').focus();
        
        $('#customers tbody').on('click', 'td', function () {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'customers/'+data[1]+'/edit';
            }
        } );
        
        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                window.location.href="customers/"+id[0][1]+'/destroy';
            }
        })
    })    
</script>
@endsection