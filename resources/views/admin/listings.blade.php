@extends('layouts.admin-default')

@section ('content')
<a href="endlisting" class="btn btn-primary">Active Listings</a>
<a href="relistitem" class="btn btn-primary">Relist Item</a>
<a href="#" class="btn btn-primary startAutomate">Start Automate</a>
<hr>
<div class="table-responsive">
<table id="products" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Image</th>
            <th>Id</th>
            <th>Condition</th>
            <th>Name</th>
            <th>Price</th>
            <th>Qty</th> 
        </tr>
    </thead>

    <?php $grandTotal = 0; $qty=0?>
    @if (isset($products))
    <tbody>

    </tbody>
    @endif
</table>
</div>

@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";

    $(document).ready( function() {
        var table = $('#products').dataTable({
            "deferRender": true,
            
            ajax: {
                url: "{{ route('ebay.products') }}",
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
            "aaSorting": [[ 2, 'desc']],
            select: {
                style:    'os',
                selector: 'td:first-child'
            }            
        });

        $(document).on('input','.discount',function() {
            $(this).next().text($(this).val());
        })

        $('.startAutomate').click( function(e) {
            e.preventDefault();
            
            data = table.api().rows( { selected: true } ).data();
            if (data.length > 0) {
                id = $(data[0][2]).text().replace(/\s|\*/gi,'')
                var _ids = [];
                $.each (data, function(key,value) {
                    id=$(data[key][2]).text().replace(/\s/g,'');
                    
                    _ids.push(id);
                })
            }

            var request = $.ajax({
                url: "{{ route('ebay.automate.item') }}",
                data: {ids:  _ids},
                success: function (result) {
                    $.alert ("Submitted")
                }
            })
            request.fail( function (jqXHR, textStatus) {
                $.alert (textStatus)
            })
            //window.location.href="orders/create/?id[]="+_ids.toString().replace(/\,/g,'&id[]=');
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