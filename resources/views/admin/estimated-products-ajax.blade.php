
@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@stop

@section ('content')
<table id="products" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th style="width: 40px"></th>
            <th style="width: 40px">Id</th>
            <th style="width: 40px">Category</th>
            <th style="width: 300px">Model/Reference</th>
            @if ($selection=='all')
            <th style="width: 60px">Serial No.</th>
            @endif
            <th style="width: 60px">Price</th>
            <th style="width: 60px">Retail</th>
        </tr>
    </thead>
    <tbody>
        
        @foreach ($products as $product)
            <tr>
                <td></td>
                <td>{{ $product->id }}</td>
                <td>{{ $product->categories->category_name }}</td>
                <td>{{ $product->p_model . ' '.$product->p_reference }}</td>
                @if ($selection=='all')  <?php // this variable is coming from EstimatesController ?>
                <td>{{ $product->p_serial }}</td>
                @endif
                <td class="text-right">${{ number_format($product->p_price,0) }}</td>
                <td class="text-right">${{ number_format($product->p_retail,0) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="width: 40px"></td>
            <td></td>
            <td><input type="text" name="search_model" id="search_model" class="search_init"></td>
            <td><input type="text" name="search_ref" id="search_ref" class="search_init"></td>
            @if ($selection=='all')  <?php // this variable is coming from EstimatesController ?>
            <td><input type="text" name="search_serial" id="search_serial" class="search_init"></td>
            @endif
            <td></td>
            <td></td>
        </tr>
    </tfoot>

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
        var asInitVals = new Array();
        var inputs = $('tfoot').find('input[type=text]');

        var table = $('#products').removeAttr('width').dataTable({
            "deferRender": true,
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0,
                width: "3%"
            }, {width: "18", targets: 2}
            , {width: "36%", targets: 3}
            , {width: "18%", targets: 4}
            , {width: "11%", targets: 5} ],
            "autoWidth": false,
            fixedColumns: true,
            select: {
                style:    'os',
                selector: 'td:first-child'
            },

        });

        table.api().column( 1 ).visible( false );
        table.on( 'select', function ( e, dt, type, indexes ) {
            if ( type === 'row' ) {
                var data = table.rows( indexes ).data().pluck( 4 )
                if (data[0] == 0) {
                    alert("The selected product cannot be added because it's not in stock");
                    table.rows( indexes ).deselect();
                // do something with the ID of the selected items
                }
            }
        } );

        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                window.location.href="products/"+id[0][1]+'/destroy';
            }
        })

        $("tfoot input").keyup( function () {
        /* Filter on the column (the index) of this element */
        
            if ($(this).attr('id')=='search_model')
                index=2
            else if ($(this).attr('id')=='search_ref') 
                index=3
            else 
                index=4

            table.fnFilter( this.value, index );
        } );

         /*
        * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
        * the footer
        */
        $("tfoot input").each( function (i) {
            asInitVals[i] = this.value;
        } );
        
        $('.addselected').click(function () {
            var _ids = [];
            ids = table.api().rows( { selected: true } ).data();
            
            $.each (ids, function(key,value) {
                if (ids[key][4] != 0)
                    _ids.push(ids[key][1]);
            })
            
            if (_ids.length == 0) {
                alert('The selected product(s) cannot be added');
                return
            }

            $.ajax({
                type: "GET",
                url: "{{action('ProductsController@ajaxgetProduct')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    _ids: _ids,
                    _blade: blade
                },
                success: function (result) {
                
                    $('.estimate-products tbody').prepend(result);
                    $.fancybox.close();
                    
                }
            })
        })

    })    
</script>
@endsection