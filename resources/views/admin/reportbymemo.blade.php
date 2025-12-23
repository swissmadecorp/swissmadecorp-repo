@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<!-- <div class="alert-info clearfix" style="padding: 3px">
    <div class="float-right">
        <button type="submit" id="printMemos" class="btn btn-primary">Print Memos</button>
        
    </div>
    </div>
<hr> -->
<table id="memos" class="table table-striped table-bordered hover">
    <thead>
        <tr>
        <th>Order Id</th>
        <th>Memo Date</th>
        <th>Customer</th>
        <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php $subtotal=0;$grandTotal = 0;?>
        @foreach ($memos as $memo)
            <?php $grandTotal += $memo->total ?>
            <?php $subtotal = $memo->total ?>
            

        <tr>
            <td>{{ $memo->id }}</td>
            <td>{{ $memo->created_at->format('m-d-Y') }}</td>
            <td>{{$memo->s_company != '' ? $memo->s_company : $memo->s_firstname . ' '.$memo->s_lastname }}</td>
            <td class="text-right">${{ number_format($subtotal,2) }}</td>
        </tr>
        @endforeach
        
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td class="text-right" style="font-weight:bold">Row Total</td>
            <td colspan="2" class="text-right" style="font-weight:bold">$ {{number_format($grandTotal,2) }}</td>
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
        
        var mTable = $('#memos').dataTable({
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
                    .column( 3 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                // Total over this page
                pageTotal = api
                    .column( 3, { page: 'current'} )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                // Update footer
                $( api.column( 3 ).footer() ).html(
                    '$'+pageTotal.format() +' ( $'+ total.format() +' total )'
                );
            },
            "order": [[ 0, "desc" ]]
       });
       
        $('#printMemos').click ( function(e) {
            e.preventDefault();
            id = oTable.api().rows( { selected: true } ).data();
            
            if (e.currentTarget.innerText == 'Print Memos') {
                window.location.href="reports/printmemos";
            }

        })
    })
</script>
@endsection