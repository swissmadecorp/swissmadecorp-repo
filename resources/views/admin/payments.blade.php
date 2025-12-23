@extends('layouts.admin-default')

@section ('content')

<div class="table-responsive">
<table id="orders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th style='width: 100px'>Customer Id</th>
            <th>Company</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

</div>

@include('admin.errors')
@endsection

@section ('jquery')
<script>
    var status='';

    $(document).ready( function() {
        
        var table = $('#orders').dataTable({
            "deferRender": true,
            ajax: {
                url: '{{ route('get.invoice.payments') }}',
                dataSrc: function(data) {
                    return data;
                }   
            },
            "aaSorting": [],
        });

        $('#orders tbody').on('click', 'td', function (e) {
            
            var data = table.api().row( this ).data();
            
            id = data;
            if (table.api().column( this ).index() != 0) {
                window.location.href = 'payments/'+id[0]+'/edit';
            }
        } );
    })    
</script>
@endsection