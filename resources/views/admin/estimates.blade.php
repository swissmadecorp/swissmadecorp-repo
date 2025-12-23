@extends('layouts.admin-default')

@section ('content')

<div class="alert-info clearfix" style="padding: 3px">
    <div class="dropdown float-right">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu" style="left: -93px" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button">Edit PO</button>
            <button class="dropdown-item" type="button">Print PO</button>
            <hr>
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>
<div class="table-responsive">
<table id="estimates" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th>Company</th>
            <th>PO</th>
            <th>Date</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        
    @foreach ($estimates as $po)
        <tr>
            <td></td>
            <td>{{ $po->id }}</td>
            <td>{{ $po->b_company }}</td>
            <td>{{ $po->po }}</td>
            <td>{{ $po->created_at->format('m-d-y') }}</td>
            <td class="text-right">${{ number_format($po->total,2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</div>
<form action="estimates/create">
    <button type="submit" class="btn btn-primary">Create New</button>
</form>

<form method="GET" id="paymentForm" style="display: none" action="{{ route('payments.create', array('id' => 0)) }}">
    <button class="btn btn-primary">Payment</button>
</form>

@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#estimates').DataTable({
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
                { "width": "5%" },
                { "width": "25%" },
                { "width": "15%" },
                { "width": "10%" },
                { "width": "10%" }
            ]
        });

        $('#estimates tbody').on('click', 'td', function () {
            var data = table.row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.column.index( 'fromVisible', visIdx );

            if (table.column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'estimates/'+data[1];
            }
        } );
        
        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.rows( { selected: true } ).data();
            
            if (e.currentTarget.innerText == 'Print Statements Due') {
                $('#paymentForm').attr('action','/admin/estimates/printstatementsdue')
                $('#paymentForm').submit();
            }

            if (id[0] == undefined) return;

            if (e.currentTarget.innerText == 'Delete') {
                if (confirm('Are you sure you want to delete selected product?')) {
                    window.location.href="estimates/"+id[0][1]+'/destroy';
                }
            } else if (e.currentTarget.innerText == 'Payment') {
                $('#paymentForm').attr('action','/admin/estimates/'+id[0][1]+'/payments/create')
                $('#paymentForm').submit();
            } else if (e.currentTarget.innerText == 'Edit PO') {
                $('#paymentForm').attr('action','/admin/estimates/'+id[0][1]+'/edit')
                $('#paymentForm').submit();                
            } else if (e.currentTarget.innerText == 'Print PO') {
                $('#paymentForm').attr('action','/admin/estimates/'+id[0][1]+'/print')
                $('#paymentForm').submit();
            } else if (e.currentTarget.innerText == 'Print Statement') {
                $('#paymentForm').attr('action','/admin/estimates/'+id[0][1]+'/printstatement')
                $('#paymentForm').submit();
            }
            
        })
    })    
</script>
@endsection