@extends('layouts.admin-default')

@section ('content')

<form action="orders/create">
    @role('superadmin|administrator')
    <button type="submit" class="btn btn-primary">Create New</button>
    @endrole
</form>
<hr/>
<div class="alert-info clearfix" style="padding: 3px">
    <div id="displaystatus" class="float-left" style="line-height:35px">Displaying unpaid orders</div>
    <div class="dropdown float-right">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu actions" style="left: -68px" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button">Edit Invoice</button>
            <hr>
            <button class="dropdown-item" type="button">Print Invoice</button>
            <button class="dropdown-item" type="button">Print Statement</button>
            <!-- <button class="dropdown-item" type="button">Print Statements Due</button> -->
            <button class="dropdown-item" type="button">Print Packing Slip</button>
            <button class="dropdown-item" type="button">Print Appraisal</button>
            <hr>
            <button class="dropdown-item" type="button">Email Invoice</button>
            <hr>
            <button class="dropdown-item" type="button">Payment</button>
            <hr>
            <button class="dropdown-item" type="button">Delete</button>
        </div>
    </div>
</div>
<hr/>


<div class="row yearDiv mb-2">
    <div class="col-10">
        <!-- <ul class="years"> -->
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Order Status
            </button>
            <div class="dropdown-menu status" aria-labelledby="dropdownMenu2">
                <button class="dropdown-item" data-action="unpaid">Display unpaid</button>
                <button class="dropdown-item" data-action="paid">Display paid</button>
                <button class="dropdown-item" data-action="returned">Display returns</button>
                <button class="dropdown-item" data-action="canceled">Display canceled</button>
                <button class="dropdown-item" data-action="all">Display all</button>
            </div>
        </div>
    </div>
</div>
<hr/>
<div class="table-responsive">
<table id="orders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Id</th>
            <th style='width: 100px'>Invoice</th>
            <th>Company</th>
            <th>Status</th>
            <th style="width: 80px">Date</th>
            <th>Amount</th>
            <th>Cust Id</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <td></td>
            <td colspan="2" class="text-right" style="font-weight:bold">Outstanding Amount</td>
            <td colspan="4" class="text-right" style="font-weight:bold"></td>
        </tr>
    </tfoot>
</table>

</div>

<div id="contextMenu" class="dropdown clearfix">
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-bottom:5px;">
      <li><a tabindex="-1" href="#">Print Invoice</a></li>
      <li><a tabindex="-1" href="#">Print Statement</a></li>
      <li><a tabindex="-1" href="#">Print Commercial</a></li>
      <li><a tabindex="-1" href="#">Print Appraisal</a></li>
      <hr>
      <li><a tabindex="-1" href="#">Email Invoice</a></li>
      <li><a tabindex="-1" href="#">Payment</a></li>
    </ul>
</div>

<form action="orders/create">
    @role('superadmin|administrator')
    <button type="submit" class="btn btn-primary">Create New</button>
    @endrole
</form>

<form method="GET" id="paymentForm" style="display: none" action="{{ route('payments.create', array('id' => 0)) }}">
    <button class="btn btn-primary">Payment</button>
</form>

@include('admin.errors')
@endsection


@section ('jquery')
<script>
    var status='';

    $(document).ready( function() {
        
        if (!localStorage.getItem("currentorderstatus"))
            status = 'unpaid';
        else status = localStorage.getItem("currentorderstatus");

        Number.prototype.format = function(n, x) {
            var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
            return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
        };

        $('.status button').click( function () {
            status = $(this).attr('data-action');
            localStorage.setItem("currentorderstatus", status); //$(this).attr('data-action');
            table.api().ajax.reload(null,true); //.url("/admin/ajaxorderstatus").load();
            //table.ajax.reload( null, true )
        })

        var table = $('#orders').dataTable({
            "deferRender": true,
            ajax: {
                url: "/admin/ajaxorderstatus?action="+status,
                data: function(d){
                    d.action = status;
                },
                dataSrc: function(json) {
                    $('#displaystatus').html(json.displaystatus);
                    return json.data;
                }   
            },
            scrollX:        true,
                //scrollY: 200,
            scroller: {
                    loadingIndicator: true
            },
            scrollCollapse: true,
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
                targets:   6
            }],
            "aaSorting": [[ 1, 'desc']],
            "createdRow": function( row, data, dataIndex){
                if( data[4] ==  'Unpaid' || data[4] ==  'Pending insurance'){
                    //$('.actions ').children().eq(3).show()
                    $(row).addClass('unpaid');
                } else if (data[4] == 'Insured')
                    $(row).addClass('insured');
                    //$('.actions ').children().eq(3).hide()
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
                    .column( 6 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                // Total over this page
                pageTotal = api
                    .column( 6, { page: 'current'} )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );
    
                // Update footer
                $( api.column( 6 ).footer() ).html(
                    '$'+pageTotal.format() +' ( $'+ total.format() +' total )'
                );
            },
            select: {
                style:    'os',
                selector: 'td:first-child'
            }
        });

        table.api().column( 7 ).visible( false );

        $(document).on('click', '.shipping',  function (e) {
            e.preventDefault();
            
            var printWindow = window.open($(this).attr('href'), "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=0,left=500,width=600,height=800");            
        })

        $('div.dataTables_filter input').focus();
        $('#orders tbody').on('click', 'td', function (e) {
            if (e.target.className == "fab fa-fedex fa-lg")
                return
                
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (visIdx==1)
                if ($(data[visIdx])[0].tagName=="A") return

            id = $(data[1]).text().replace(/\s|\*/gi,'')
            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1]+'\'s row' );
                if (id.indexOf(' ')>0)
                    text =  id.substr(0,data[1].indexOf(' '))
                else text= id;

                window.location.href = 'orders/'+text;
            }
        } );
        
        function printInvoice(id,command) {
            var printWindow, showWindow
            
            if (command == 'invoice') {
                showWindow = "/admin/orders/"+id+"/print", "_blank";
            } else if (command == 'statement') {
                showWindow = "/admin/orders/"+id+"/"+status+"/printstatement";
            } else if (command == 'slip') {
                showWindow = "/admin/orders/"+id+"/print/packingslip";
            } else if (command == 'appraisal') {
                showWindow = "/admin/orders/"+id+"/print/appraisal";
            } else if (command == 'commercial') {
                showWindow = "/admin/orders/"+id+"/print/commercial";
            }
            
            printWindow = window.open(showWindow, "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=0,left=500,width=600,height=800");
            
            var printAndClose = function() {
                if (printWindow.document.readyState == 'complete') {
                    printWindow.print();
                    clearInterval(sched);
                }
            }
            var sched = setInterval(printAndClose, 1000);
        }

        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            id = table.api().rows( { selected: true } ).data();
            
            if (id[0] == undefined) return;

            _id = $(id[0][1]).text().replace(/\s|\*/gi,'')
            if (_id.indexOf(' ')>0)
                text =  _id.substr(0,_id.indexOf(' '))
            else text=_id;

            if (e.currentTarget.innerText == 'Delete') {
                if (confirm('Are you sure you want to delete selected product?')) {
                    window.location.href="orders/"+text+'/destroy';
                }
            } else if (e.currentTarget.innerText == 'Payment') {
                $('#paymentForm').attr('action','/admin/orders/'+text+'/payments/create')
                $('#paymentForm').submit();
            } else if (e.currentTarget.innerText == 'Email Invoice') {
                if (_id.length > 1) {
                    var ids = [];
                    for (var i=0;i < id.length;i++) {
                        s_id = $(id[i][1]).text().replace(/\s|\*/gi,'')
                        if (s_id.indexOf(' ')>0)
                            text =  s_id.substr(0,s_id.indexOf(' '))
                        else text=s_id;
                        
                        ids.push(text);
                    }
                    window.location.href='orders/'+ids+'/printmulti';
                } else {
                    window.location.href='orders/'+text+'/print/email';
                }
            } else if (e.currentTarget.innerText == 'Edit Invoice') {
                $('#paymentForm').attr('action','/admin/orders/'+text+'/edit')
                $('#paymentForm').submit();
            } else if (e.currentTarget.innerText == 'Print Invoice') {
                if (_id.length > 1) {
                    var ids = [];
                    for (var i=0;i < id.length;i++) {
                        s_id = $(id[i][1]).text().replace(/\s|\*/gi,'')
                        if (s_id.indexOf(' ')>0)
                            text =  s_id.substr(0,s_id.indexOf(' '))
                        else text=s_id;
                        
                        ids.push(text);
                    }
                    printInvoice(ids,'invoice')
                    //window.location.href='orders/'+ids+'/print';
                } else {
                    printInvoice(text,'invoice')
                }
                
            } else if (e.currentTarget.innerText == 'Print Appraisal') {
                printInvoice(text,'appraisal')
            } else if (e.currentTarget.innerText == 'Print Statement') {
                printInvoice(id[0][7],'statement')
            } else if (e.currentTarget.innerText == 'Print Packing Slip') {
                printInvoice(text,'slip')
            } 
            
        })

        var $contextMenu = $("#contextMenu");
        var rowSel

        $("body").on("contextmenu", "table tr", function(e) {
            $contextMenu.css({
                display: "block",
                left: e.pageX-200,
                top: e.pageY-40
            });
            //debugger;
            //$(rowSel).removeClass('unpaid');
            rowSel = $(this)
            //$(rowSel).removeClass('unpaid');
            return false;
        });

        $('html').click(function(e) {
            //$(rowSel).addClass('unpaid');
            $contextMenu.hide();
        });
    
        $("#contextMenu li a").click(function(e){
            e.preventDefault();
            var f = $(rowSel).children(0).eq(1).find("a");

            if ($(this).html() == "Print Invoice") {
                printInvoice(f.html(),'invoice');
            } else if ($(this).html() == "Print Statement") {
                printInvoice(f.attr('data-custid'),'statement');
            } else if ($(this).html() == 'Email Invoice') {
                window.location.href='orders/'+f.html()+'/printmulti';
            } else if ($(this).html() == 'Print Commercial') {
                printInvoice(f.html(),'commercial');
            } else if ($(this).html() == 'Print Appraisal') {
                printInvoice(f.html(),'appraisal');
            } else {
                $('#paymentForm').attr('action','/admin/orders/'+f.html()+'/payments/create')
                $('#paymentForm').submit();
            }
            //debugger;
        });

        $('.years li').click( function (e) {
            alert (e.val());
        })
    
        if ($.QueryString["print"]) { // This is being set in OrderController::store
            var queryString = $.QueryString["print"];
            var printWindow = window.open("/admin/orders/"+queryString+"/print", "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=0,left=500,width=600,height=800");
                var printAndClose = function() {
                    if (printWindow.document.readyState == 'complete') {
                        printWindow.print();
                        clearInterval(sched);
                    }
                }
                var sched = setInterval(printAndClose, 1000);

                window.history.pushState('data','Title','orders');
        }

    })    
</script>
@endsection