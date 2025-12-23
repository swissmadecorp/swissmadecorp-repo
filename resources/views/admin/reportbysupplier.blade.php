@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<div id="baseDateControl">
 <div class="dateControlBlock">
        Between <input type="text" name="dateStart" id="dateStart" class="datepicker" size="8" /> and 
        <input type="text" name="dateEnd" id="dateEnd" class="datepicker" size="8"/>
    </div>
</div>

<table id="suppliers" class="table table-striped table-bordered hover">
    <thead>
        <tr>
        <th>Product Id</th>
        <th style="width:30px">Model</th>
        <th style="width:30px">Serial</th>
        <th>Date</th>
        <th>Supplier</th>
        <th>Price</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td class="text-right" style="font-weight:bold">Row Total</td>
            <td colspan="4" class="text-right" style="font-weight:bold"></td>
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

       var sTable=$('#suppliers').dataTable({
            ajax: {
                url: "{{ route('report.by.supplier') }}",
                dataSrc: function(json) {
                    return json.data;
                }   
            },
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
                $( api.column( 3 ).footer() ).html(
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
        
        $("#dateStart").keyup ( function() { 
            sTable.fnDraw(); 
        } );
        $("#dateStart").change( function() { 
            sTable.fnDraw(); 
        } );
        $("#dateEnd").keyup ( function() { sTable.fnDraw(); } );
        $("#dateEnd").change( function() { sTable.fnDraw(); } );
    })
</script>
@endsection