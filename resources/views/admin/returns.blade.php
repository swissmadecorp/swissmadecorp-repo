@extends('layouts.admin-default')

@section ('content')

<div class="alert-info clearfix" style="padding: 3px">
    <div class="float-right">
    <button type="submit" id="printReturn" class="btn btn-primary">Print Return</button>
    </div>
</div>
<hr>

<div class="table-responsive">
<table id="returns" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Order Id</th>
            <th>Return Id</th>
            <th>Name</th>
            <th>Company</th>
            <th>Total</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>

    @foreach ($returns as $return)
        <tr>
            <td></td>
            <td>{{ $return->order_id }}</td>
            <td>{{ $return->returns_id }}</td>
            <td>{{ $return->b_firstname.' '.$return->b_lastname }}</td>
            <td>{{ $return->b_company }}</td>
            <td>${{ number_format($return->amount,2) }}</td>
            <td>{{ date('m/d/Y',strtotime($return->date)) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</div>

@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#returns').dataTable({
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

        $('#returns tbody').on('click', 'td', function () {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'orders/'+data[1]+'/returns/create';
            }
        } );

        $('#printReturn').click ( function(e) {
            e.preventDefault();
            id = table.api().rows( { selected: true } ).data();

            window.location.href="returns/"+id[0][1]+"/print";

        })
    })
</script>
@endsection