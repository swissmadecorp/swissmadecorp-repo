@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')
<!-- <a href="endlisting" class="btn btn-primary">Active Listings</a> -->
<a href="relistitem" class="btn btn-primary submit_selected">Submit Selected</a>
<a href="relistitem" class="btn btn-primary mr-1 remove_selected">Remove Selected</a>

<hr>
<div class="table-responsive">
<table id="products" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Image</th>
            <th>Id</th>
            <th>Name</th>
            <th>Retail</th>
            <th>Qty</th> 
        </tr>
    </thead>

    <?php $grandTotal = 0; $qty=0?>
    
    <tbody>

    </tbody>
    
</table>
</div>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";

    $(document).ready( function() {
        var table = $('#products').DataTable({
            "deferRender": true,
            
            ajax: {
                url: "{{ route('walmart.products') }}",
                dataSrc: function(json) {
                    return json.data;
                }
            },
            stateSave: true,
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            }],
            "aaSorting": [[ 5, 'desc'],[ 2, 'desc']],
            select: {
                style:    'os',
                selector: 'td:first-child'
            }            
        });

        $(document).on('input','.discount',function() {
            $(this).next().text($(this).val());
        })

        $('.submit_selected').click( function(e) {
            e.preventDefault();
            data = table.rows( { selected: true } ).data();
            var _ids = [];
            $.each (data, function(key,value) {
                id=data[key][2];
                
                _ids.push(id);
            })
            var request = $.ajax({
                type: 'post',
                url: "{{ route('submit.product') }}",
                data: {ids: _ids },
                success: function (result) {
                    $.alert (result)
                }
            })
            request.fail( function (jqXHR, textStatus) {
                $.alert (textStatus)
            })
        })

        $('.remove_selected').click( function(e) {
            e.preventDefault();
            data = table.rows( { selected: true } ).data();
            var _ids = [];
            $.each (data, function(key,value) {
                id=data[key][2];
                
                _ids.push(id);
            })
            var request = $.ajax({
                type: 'post',
                url: "{{ route('retire.product') }}",
                data: {ids: _ids },
                success: function (result) {
                    $.alert (result)
                }
            })
            request.fail( function (jqXHR, textStatus) {
                $.alert (textStatus)
            })
        })

        $('table').focusin(function (e) {
            if (e.target.nodeName=='A' || e.target.className.indexOf('discount')!=-1)
                return

            var _this=$('div.dataTables_filter input');
            setTimeout( function () {
                selectText(_this);
            },100)            
        })

        $('div.dataTables_filter input').focus();
       
    })    
</script>
@endsection