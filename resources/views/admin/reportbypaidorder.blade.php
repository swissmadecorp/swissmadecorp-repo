@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<!-- <div class="clearfix" style="padding: 3px">
    <div class="float-right" style="margin-left: 3px"><button type="submit" id="printPaid" class="btn btn-secondary">Print Orders</button></div>
    <div class="float-right"><button type="submit" id="printPaidWithProducts" class="btn btn-secondary">Print Product Orders</button></div>
</div> -->
<!-- <hr> -->
<table id="paidOrders" class="table table-striped table-bordered hover">
    <thead>
        <tr>
        <th>Order Id</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Total Cost</th>
        <th>Total Amount</th>
        <th>Total Profit</th>
        </tr>
    </thead>
    <tbody>

        
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td class="text-right" style="font-weight:bold">Total</td>
            <td class="text-right" style="font-weight:bold"></td>
            <td class="text-right" style="font-weight:bold"></td>
            <td class="text-right" style="font-weight:bold;color: green"></td>
        </tr>
    </tfoot>
</table>
@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>

    $(document).ready( function() {
        var asInitVals = new Array();

       var oTable=$('#paidOrders').dataTable({
            ajax: {
                url: "{{ route('report.by.paid') }}",
                dataSrc: function(json) {
                    return json.data.data;
                }   
            },
            "deferRender": true,
            "initComplete": function(settings, json) {
                var api = this.api();
                $( api.column( 3 ).footer() ).html(json.data.totals[0])
                $( api.column( 4 ).footer() ).html(json.data.totals[1])
                $( api.column( 5 ).footer() ).html(json.data.totals[2])
                $('div.dataTables_filter input').focus();
            },
            "order": [[ 0, "desc" ]]
       });
        
       $('#paidOrders tbody').on('click', 'td', function () {
            if ($(this).parents('table').attr('id')=='paidOrders')
                cTable = poTable;
            else if ($(this).parents('table').attr('id')=='orders')
                cTable = oTable;
            else cTable = mTable;

            var data = cTable.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = cTable.api().column.index( 'fromVisible', visIdx );

            window.location.href = 'orders/'+data[0];
            
        } );
        
        $('#printPaid').click ( function(e) {
            e.preventDefault();
            id = oTable.api().rows( { selected: true } ).data();
            
            window.location.href="reports/print/paid";
        })

        $('#printPaidWithProducts').click ( function(e) {
            e.preventDefault();
            id = oTable.api().rows( { selected: true } ).data();
            
            window.location.href="reports/print/paidwithproducts";
            
        })

    })
</script>
@endsection