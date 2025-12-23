@extends('layouts.admin-default')

@section ('content')

<form action="discountrules/create">
    @role('superadmin|administrator')
    <button type="submit" class="btn btn-primary">Create New</button>
    @endrole
</form>
<hr/>
<div class="alert-info clearfix" style="padding: 3px">
    <div class="dropdown float-right">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu" style="left: -53px" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button">Edit</button>
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>

<div class="table-responsive">
<table id="discount_rules" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th style='width: 100px'>Rule Name</th>
            <th>Action</th>
            <th>Status</th>
            <th>Free Shipping</th>
            <th>Discount Code</th>
            <th style="width: 80px">Date</th>
            
        </tr>
    </thead>
  
</table>

</div>

@include('admin.errors')
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var table = $('#discount_rules').dataTable({
            "deferRender": true,
            ajax: {
                url: "{{ route('discount.rules') }}",

                dataSrc: function(json) {
                    return json.data;
                }   
            },
            'processing': true,
                'language': {
                    'loadingRecords': '&nbsp;',
                    'processing': '<div class="spinner"></div>'
                },
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0,
            },{
                className: 'dt-body-right',
                targets:   7
            }],
            "aaSorting": [[ 7, 'desc']],
            select: {
                style:    'os',
                selector: 'td:first-child'
            }
        });

        $('#discount_rules tbody').on('click', 'td', function (e) {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                text= data[1];

                window.location.href = 'discountrules/'+text+'/edit';
            }
        } );

        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;
            if (confirm('Are you sure you want to delete selected product?')) {
                $.ajax({
                    type: "DELETE",
                    url: "discountrules/"+id[0][1],
                    success: function(result) {
                        // do something
                        window.location.href="discountrules"
                    }
                });

                //window.location.href="discountrules/"+id[0][1]+'/destroy';
            }
        })

    })    
</script>
@endsection