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
        <th>Invoice #</th>
        <th>Id</th>
        <th>Product</th>
        <th>Serial</th>
        <th>Retail</th>
        <th>Customer</th>
        <th>Date</th>
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
                url: "{{ route('report.by.product') }}",
                data: function(d){
                    return status
                },
                dataSrc: function(json) {
                    return json.data;
                }   
            },
            columnDefs: [
                { "width": "80px", "targets": 0 },
                { "width": "35%", "targets": 2 },
                { "width": "20%", "targets": 4 },
                { "width": "150px", "targets": 5 }],
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            "order": [[ 0, "asc" ]]
        })

    })
</script>
@endsection