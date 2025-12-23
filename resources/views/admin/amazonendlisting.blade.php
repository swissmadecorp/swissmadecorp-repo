@extends('layouts.admin-default')
@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css"/> 
<link rel="stylesheet" href="//cdn.materialdesignicons.com/2.3.54/css/materialdesignicons.min.css">
<style>
    ul {list-style: none; padding:0}
    ul li {padding: 5px}
    .dt-center {text-align: center}
</style>
@endsection
@section ('content')
<a href="listings" class="btn btn-primary">List Products</a>
<a href="relistitem" class="btn btn-primary">Relist Item</a>
<hr>
<div class="table-responsive">
<table id="products" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Image</th>
            <th>Id</th>
            <th>Title</th>
            <th>Price</th>
        </tr>
    </thead>
    <?php $grandTotal = 0; $qty=0?>
    @if (isset($products))
    <tbody>
    </tbody>
    @endif
</table>
</div>
<div id="reasonDialog" style="display: none" title="Why do you want to end this listing?">
    <form id="formReason" method="post">
        <input type="hidden" id="itemID" name="itemID" value="" />
        <ul>
            <li>
                <div class="pretty p-icon p-jelly p-round">
                    <input type="radio" name="reason" value="Incorrect" />
                    <div class="state p-primary">
                        <i class="icon mdi mdi-check"></i>
                        <label>Start Price or Reserve Price is incorrect</label>
                    </div>
                </div>
            </li>
            <li>
                <div class="pretty p-icon p-jelly p-round">
                    <input type="radio" name="reason" value="LostOrBroken"  />
                    <div class="state p-primary">
                        <i class="icon mdi mdi-check"></i>
                        <label>The item is lost or broken</label>
                    </div>
                </div>
            </li>
            <li>
                <div class="pretty p-icon p-jelly p-round">
                    <input type="radio" name="reason" value="NotAvailable"  />
                    <div class="state p-primary">
                        <i class="icon mdi mdi-check"></i>
                        <label>No longer available for sale</label>
                    </div>
                </div>
            </li>
            <li>
                <div class="pretty p-icon p-jelly p-round">
                    <input type="radio" name="reason" value="OtherListingError"  />
                    <div class="state p-primary">
                        <i class="icon mdi mdi-check"></i>
                        <label>Listing contains an error</label>
                    </div>
                </div>
            </li>
            <li>
                <div class="pretty p-icon p-jelly p-round">
                    <input type="radio" name="reason" value="SellToHighBidder"  />
                    <div class="state p-primary">
                        <i class="icon mdi mdi-check"></i>
                        <label>We want to sell an auction item to the current high bidder</label>
                    </div>
                </div>
            </li>                   
        </ul>
    </form>
</div>
@endsection
@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/b-1.5.1/b-html5-1.5.1/datatables.min.js"></script>
@endsection
@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    $(document).ready( function() {
        var table = $('#products').DataTable({
            "deferRender": true,
            
            ajax: {
                url: "{{ URL::route('displayListItems','_token='.csrf_token()) }}",
                dataSrc: function(json) {
                    return json.data;
                }
            },
            stateSave: true,
            columnDefs: [ {
                orderable: false,
                className: 'td-center',
                targets:   0
            }],
            select: {
                style:    'os',
                selector: 'td:first-child'
            }
        });
        var endItemLink
        $(document).on('click','.endlisting', function (e) {
            e.preventDefault();
            $('#itemID').val($(this).parent('td').children().first().text());
            endItemLink = $(this);
            $( "#reasonDialog" ).dialog( "open" );
        })
        

        
        $(document).on('input','.discount',function() {
            $(this).next().text($(this).val());
        })
        $('table').focusin(function (e) {
            if (e.target.nodeName=='A')
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