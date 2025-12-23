@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<table id="products" class="table table-striped table-bordered hover"  width="100%">
    <thead>
        <tr>
        <th>Customer</th>
        <th>Amount</th>
        <th>Paid Amount</th>
        </tr>
    </thead>
    <tbody>
    </tbody>

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

        var oTable = $('#products').dataTable({
            "deferRender": true,
            ajax: {
                url: "{{ route('report.by.company') }}",
                data: function(d){
                    return status
                },
                dataSrc: function(json) {
                    return json.data;
                }   
            },
            
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            "order": [[ 0, "asc" ]]
        })

    })
</script>
@endsection