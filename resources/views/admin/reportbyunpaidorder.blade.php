@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<!-- <div class="clearfix" style="padding: 3px">
    <div class="float-right" style="margin-left: 3px">
        <a href="reports/print/unpaid" class="btn btn-secondary">Print Unpaid Orders</a>
    </div>
    <div class="float-right">
        <a href="reports/print/paidwithproducts/unpaid" class="btn btn-secondary">Print Product Orders</a>
    </div>
</div>
<hr> -->
<table id="unpaidOrders" class="table table-striped table-bordered hover">
    <thead>
        <tr>
        <th>Order Id</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Total Cost</th>
        <th>Total Amount</th>
        <th>Oustanding</th>
        </tr>
    </thead>
    <tbody>
        <?php $grandTotal=0;$subtotal=0;$cost=0;$totalcost=0;$profit=0;$totalprofit=0;$orderAmount=0;?>
        @foreach ($orders as $order)
            @foreach ($order->products as $product)
                <?php 
                    $orderAmount+=$product->p_price;
                    $cost+=$product->p_price; 
                ?>
            @endforeach
            
            <?php $totals = $order->total ?>
            @if ($order->status==0)
                @foreach ($order->payments as $payment)
                    <?php 
                        $totals = $totals - $payment->amount;
                    ?>
                @endforeach
            @else
                <?php $totals=0 ?>
            @endif
            <?php $grandTotal += $order->total ?>
            <?php $subtotal = $order->total ?>
            <?php $totalcost+=$cost ?>
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ $order->created_at->format('m/d/Y') }}</td>
            <td>{{$order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname }}</td>
            <td class="text-right">${{ number_format($cost,2) }}</td>
            <td class="text-right">${{ number_format($subtotal,2) }}</td>
            <td class="text-right" style="color: green">${{ number_format($totals,2) }}</td>
        </tr>
        <?php $cost=0;$profit=0;$orderAmount=0;?>
        @endforeach
        
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td class="text-right" style="font-weight:bold">Outstanding Amount</td>
            <td colspan="3" class="text-right" style="font-weight:bold"></td>
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
        $( "#dateStart" ).datepicker();
        $( "#dateEnd" ).datepicker();

        Number.prototype.format = function(n, x) {
            var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
            return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
        };

       var uoTable = $('#unpaidOrders').dataTable({
            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api(), data;
    
                // Remove the formatting to get integer data for summation
                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };
    
                // Total over all pages
                total = api
                    .column( 5 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                // Total over this page
                pageTotal = api
                    .column( 5, { page: 'current'} )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                // Update footer
                $( api.column( 5 ).footer() ).html(
                    '$'+pageTotal.format() +' ( $'+ total.format() +' total )'
                );
            },
            "order": [[ 0, "desc" ]]
       });

        // The plugin function for adding a new filtering routine
        $.fn.dataTableExt.afnFiltering.push(
            function(oSettings, aData, iDataIndex){
                if (oSettings.nTable.id == 'suppliers') {
                    var dateStart,dateEnd;

                    if ($("#dateStart").val()!='')
                        dateStart = parseDateValue($("#dateStart").val());
                    if ($("#dateEnd").val()!='')
                        dateEnd = parseDateValue($("#dateEnd").val());
                    // aData represents the table structure as an array of columns, so the script access the date value 
                    // in the first column of the table via aData[0]

                    if (dateStart || dateEnd) {
                        var evalDate= parseDateValue(aData[3]);
                    
                        if (evalDate >= dateStart && evalDate <= dateEnd) {
                            return true;
                        }
                        else {
                            return false;
                        }
                    } return true
            } else 
                return true;
        });

        // Function for converting a mm/dd/yyyy date value into a numeric string for comparison (example 08/12/2010 becomes 20100812
        function parseDateValue(rawDate) {
            var dateArray= rawDate.split("/");
            var parsedDate= dateArray[2] + dateArray[0] + dateArray[1];
            return parsedDate;
        }
    
    })
</script>
@endsection