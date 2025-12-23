
@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@stop

@section ('content')
<table id="products" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th>Name</th>
            <th>Serial#</th>
            <th>Price</th>
            <th>Retail</th>
            <th>Qty</th>            
        </tr>
    </thead>
    <tbody>
        
        @foreach ($products as $product)
            <tr>
                <td></td>
                <td>{{ $product->id }}</td>
                <td>{{ $product->title }}</td>
                <td>{{ $product->p_serial }}</td>
                <td>${{ number_format($product->p_price,0) }}</td>
                <td>${{ number_format($product->p_retail,0) }}</td>
                <td>{{ $product->p_qty }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<button class="btn btn-primary addselected">Add Selected</button>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#products').DataTable({
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
            "columns": [
                { "width": "5%" },
                { "width": "4%" },
                { "width": "30%" },
                { "width": "10%" },
                { "width": "20%" },
                { "width": "20%" },
                { "width": "20%" }
            ]
        });

        /*$('#products tbody').on('click', 'td', function () {
            var data = table.row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.column.index( 'fromVisible', visIdx );

            if (table.column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                window.location.href = 'products/'+data[1]+'/edit';
            }
        } );*/
        
        table.on( 'select', function ( e, dt, type, indexes ) {
            if ( type === 'row' ) {
                var data = table.rows( indexes ).data().pluck( 6 )
                if (data[0] == 0) {
                    alert("The selected product cannot be added because it's not in stock");
                    table.rows( indexes ).deselect();
                // do something with the ID of the selected items
                }
            }
        } );
        
        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                window.location.href="products/"+id[0][1]+'/destroy';
            }
        })

        $('.addselected').click(function () {
            var _ids = [];
            ids = table.rows( { selected: true } ).data();
            
            $.each (ids, function(key,value) {
                _ids.push(ids[key][1]);
            })
            
            // debugger;
            $.ajax({
                type: "GET",
                url: "{{ route('ajax.product') }}",
                data: { 
                    _token: "{{csrf_token()}}",
                    _ids: _ids,
                    _blade: blade
                },
                success: function (result) {
                
                    $('.order-products tbody').prepend(result);
                    $.fancybox.close();
                    
                }
            })
        })

    })    
</script>
@endsection