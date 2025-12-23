@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<form action="get" id="form">
<input type="checkbox" name="company" checked /> Include Company Name<br>
<input type="checkbox" name="calculate" /> Calculate Discount Price<br>
<input type="checkbox" name="discount" /> Include Discount Column<br>
<input type="checkbox" name="serial" /> Include Serial # Column<br>
<input type="checkbox" name="include_cost" /> Include Cost

</form>
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
            <th>Cost</th>
            <th>Qty</th> 
            <th>Discount</th>
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

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>

<script type="text/javascript" src="/js/jquery.scannerdetection.js"></script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var table

    $(document).ready( function() {
        table = $('#products').DataTable({
            ajax: {
                url: "{{ route('export.products') }}",
                dataSrc: function(json) {
                    return json.data;
                }
            },
            "deferRender": true,
            select: true,
            rowId: 'id',
            dom: 'Bfrtip',
            columns: [
                { data: 'sel' },
                { data: 'image' },
                { data: 'id' },
                { data: 'name' },
                { data: 'retail' },
                { data: 'cost' },
                { data: 'qty' },
                { data: 'discount' }
            ],
            buttons: [ {
                //extend: 'excelHtml5',
                text: 'Export to Excel',
                action: function ( e,dt,node,config ){
                    var _ids = [];
                    ids = table.rows( { selected: true } ).data();
                    
                    //var data = table.$('input, select').serialize();
                    $.each (ids, function(key,value) {
                        id=$(ids[key]["id"]).text().replace(/<[^>]*>?/gm, '');;
                        discount=$(table.$('.discount_'+id)).val();
                            
                        _ids.push({id:id,discount: discount});
                    })

                    $.ajax({
                        type: "GET",
                        url: "{{ route('export.to.excel') }}",
                        data: { 
                            _token: csrf_token,
                            ids: _ids,
                            form: $('#form').serialize()
                        },
                        success: function (result) {
                            if (result.error==0)
                                window.open(result.filename, "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400");
                            else alert (result.message);
                        }
                    })
                }
            }, 
            {
                text: 'Reload table',
                action: function () {
                    table.ajax.reload();
                }
            }
            //{
            //     text: 'Clear selections',
            //     action: function ( e,dt,node,config ){
            //         table.rows('.selected').deselect();
            //     }
            // } 
            ],
            stateSave: true,
            columnDefs: [ {
                orderable: false,
                className: 'select-checkbox',
                targets:   0
            },
            {
                className: 'condition-container',
                targets:   3
            }],
            "aaSorting": [ 2, 'desc'],
            select: {
                style:    'os',
                selector: 'td:first-child'
            }            
        });

        $('#products tbody').on('mouseenter', 'td', function () {
            if ($(this).index()==5)
                $(this).find('span').show()
        }).on('mouseleave', 'td', function () {
            if ($(this).index()==5)
                $(this).find('span').hide()
        })
        
        $(document).on('input','.discount',function() {
            $(this).next().text($(this).val());
        })

        var changes = false;
        window.onbeforeunload = function() {
            if (changes)
            {
                var message = "Are you sure you want to navigate away from this page?\n\nYou have started writing or editing a post.\n\nPress OK to continue or Cancel to stay on the current page.";
                if (confirm(message)) return true;
                else return false;
            }
        }

        $(document).on('click','input',function() {
            indx = $(this).parents('tr').index();
            table.row(":eq("+indx+")", { page: 'current' }).select();
            changes = true
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