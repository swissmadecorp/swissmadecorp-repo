@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pretty-checkbox@3.0/dist/pretty-checkbox.min.css"/> 
<link rel="stylesheet" href="//cdn.materialdesignicons.com/2.3.54/css/materialdesignicons.min.css">
@endsection

@section ('content')

<div class="container">
        <div class="row">
            <!-- <div class="col-md-8 col-md-offset-2">
                <edit-note :note="{{-- $note --}}"></edit-note>
            </div> -->
        </div>
    </div>
    
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Create New
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
        <a href="products/create" class="dropdown-item" >Watch</a>
        <a href="products/jewelrycreate" class="dropdown-item">Jewelry</a>
        <a href="products/bezelcreate" class="dropdown-item">Bezel</a>
    </div>
</div><br>
<hr/>
<div class="alert-info clearfix" style="padding: 3px">
<div class="float-left">
        <input type="checkbox" id="show_deleted" > Display deleted items
    </div>
    <div class="dropdown float-right">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu" style="left: -114px" aria-labelledby="dropdownMenu2">
            <button class="dropdown-item" type="button" data-id="print">Print Label</button>
            <button class="dropdown-item" type="button" data-id="duplicate">Duplicate</button>
            <hr>
            <button class="dropdown-item" type="button" data-id="invoice">Create Invoice</button>
            <hr>
            <button class="dropdown-item" type="button" data-id="3DPrices">Update 3rd Party Prices</button>
            <hr>
            <button class="dropdown-item" type="button" data-id="delete">Delete</button>
            <button class="dropdown-item restore" style="display:none" type="button" data-id="restore">Restore</button>
            <hr>
            <button class="dropdown-item" type="button" data-id="properties">Properties</button>
        </div>
    </div>
</div>

<br>
<div class="pretty p-icon p-jelly p-round">
    <input type="radio" name="icon_solid" id="on-hand" checked/>
    <div class="state p-primary">
        <i class="icon mdi mdi-check"></i>
        <label>Display on-hand</label>
    </div>
</div>

<div class="pretty p-icon p-jelly p-round">
    <input type="radio" name="icon_solid" id="display-all" />
    <div class="state p-success">
        <i class="icon mdi mdi-check"></i>
        <label>Display not on-hand</label>
    </div>
</div>

<hr/>
<div class="table-responsive">
<table id="products" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th></th>
            <th>Image</th>
            <th>Id</th>
            <th>Name</th>
            <th>Serial#</th>
            <th>Cost</th>
            <th>Price</th>
            <th>Retail</th>
            <th>Qty</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right" style="font-weight:bold">Total</td>
            <td colspan="2" class="text-right"></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-center"></td>
        </tr>
    </tfoot>
</table>
</div>

<div id="contextMenu" class="dropdown clearfix">
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-bottom:5px;">
      <li><a tabindex="-1" href="#">Duplicate</a></li>
      <li><a tabindex="-1" href="#">Create Invoice</a></li>
      <hr>
      <li><a tabindex="-1" href="#">Repair</a></li>
      <hr>
      <li><a tabindex="-1" href="#">Print</a></li>
    </ul>
</div>

<div id="properties" style="display:none">
    <label>Enter value for each selected product.</label>
    <form action="" class="formName container">
        <div class="form-group">
            <div class="form-group row">
                <label for="status-input" class="col-3 col-form-label">Status</label>       
                <div class="col-9">
                    <select class="custom-select form-control p_status" name="p_status">
                    <option value="none">No Change</option>
                    @foreach (Status() as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="price-input" class="col-3 col-form-label">Price</label>
                <div class="col-9 input-group">
                    <div class="input-group-addon">$</div>
                    <input class="form-control" type="text" name="p_newprice" value="0" id="webprice-input">  
                </div>
            </div>
        </div>
    </form>
</div>

<div id="repair-form" style="display:none;">
    <label>Create or update the product for repair.</label>
    <form action="" class="formName container">
        <div class="form-group">
            <div class="row">
                <label for="assigned-to-input" class="col-4 col-form-label">Watchmaker</label>       
                <div class="col-12">
                    <input class="form-control" type="text" id="assigned-to-input">  
                </div>
            </div>
            <div class="row">
                <label for="jobs-input" class="col-4 col-form-label">Assigned Jobs</label>
                <div class="col-12">
                    <input class="form-control" type="text" id="jobs-input">  
                </div>
            </div>
            <div class="row">
                <label for="notes-input" class="col-4 col-form-label">Notes</label>
                <div class="col-12">
                    <textarea rows="4" class="form-control" type="text" id="notes-input"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <label class="form-check-label col-12">
                        <input type="checkbox" id="isCompleted" class="form-check-input" value=""> Mark as completed
                    </label>
                </div>
            </div>
            
        </div>
    </form>
</div>

@endsection

@section ('footer')
<script type="text/javascript" src="/js/jquery.scannerdetection.js"></script>
<script type="text/javascript" src="/js/jquery.lazy.min.js"></script>
@endsection

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var table;
    
    $(document).ready( function() {

        function initTable(action,display) {
            if (!action)
                action = 'active';

            if (!display)
                display = 'Display on-hand';

            table = $('#products').dataTable({
                "deferRender": true,
                "drawCallback": function( settings ) {
                    //lazy.update()
                    var lazy = $("#products img").Lazy({
                        effect: "fadeIn",
                        effectTime: 800,
                        threshold: 0,
                        visibleOnly: true
                    });

                   
                },
                ajax: {
                    url: "{{ route('get.all.products') }}",
                    data: {action:action,display:display},
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                scrollX:        true,
                //scrollY: 200,
                scroller: {
                    loadingIndicator: true
                },
                scrollCollapse: true,
                autoWidth:      true,  
                paging:         true,
                "initComplete": function(settings, json) {
                    var api = this.api();
                    $( api.column( 5 ).footer() ).html(json.total)
                    $( api.column( 8 ).footer() ).html(json.qty);
                    $('div.dataTables_filter input').focus();
                },

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
                "aaSorting": [[ 2, 'desc']],
                select: {
                    style:    'os',
                    selector: 'td:first-child'
                }            
            });

        }

        //document.oncontextmenu = function() {return false;};
        
        var $contextMenu = $("#contextMenu");
        var rowSel

        $("body").on("contextmenu", "table tr", function(e) {
            $contextMenu.css({
                display: "block",
                left: e.pageX-200,
                top: e.pageY-40
            });
            //debugger;
            rowSel = $(this)
            return false;
        });

        $('html').click(function(e) {
            $contextMenu.hide();
        });
    
        $("#contextMenu li a").click(function(e){
            e.preventDefault();
            var f = $(rowSel).children(0).eq(2).find("a");

            if ($(this).html() == "Duplicate") {
                window.location.href= f.attr("href").replace("edit","duplicate")
            } else if ($(this).html() == "Print") {
                printItemBarcode(f.html());
            } else if ($(this).html() == "Repair") {
                displayRepairDialog(f.html())
            } else {
                window.location.href="orders/create/?id="+f.html();
            }

            //debugger;
        });
        
        $('table').focusin(function (e) {
            if ($('#products').find('#newprice').length>0 || $('#products').find('#newqty').length>0)
                return

            if (e.target.nodeName=='A')
                return

            var _this=$('div.dataTables_filter input');
            setTimeout( function () {
                selectText(_this);
            },100)            
        })

        //table.state.clear()
        $(document).on('mouseenter', 'span.hide', function () {
                $(this).css('opacity',1)
            }).on('mouseleave', 'span.hide', function () {
                $(this).css('opacity',0)
            })
        
        $(document).on('click','.cancelprice,.cancelqty', function() {
            if ($('#newqtycontainer').length>0)
                $('#newqtycontainer').remove();

            if ($('#newpricecontainer').length>0)
                $('#newpricecontainer').remove();
        })

        $(document).on('click','.updateprice', function() {
            _this=$(this);
            $.ajax({
                type: "GET",
                url: "{{ URL::route('updatePrice') }}",
                data: { 
                    _token: csrf_token,
                    id: _this.attr('data-id'),
                    amount: _this.prev().val()
                },
                success: function (result) {
                    if (result.error=='success') {
                        parent = $('#newpricecontainer').parent();
                        $(parent).text('$'+parseFloat(result.amount).formatMoney(0, '.', ',')+' (' + result.discount + '%)');
                        $('#newpricecontainer').remove();
                    } else {
                        alert (result.error);
                    }
                }
            })
        })

        initTable('active');
        $('#show_deleted').click( function () {
            table.api().destroy();
            if ($(this).is(':checked')) {
                $('.restore').show();
                initTable('deleted');
            } else {
                $('.restore').hide();
                initTable('active');
            }
        });

        $('#on-hand').prop('checked',true);
        $('#display-all,#on-hand').click( function() {
            //if ($(this).is(':checked')) return
            table.api().destroy();
            initTable('active',$(this).next().find('label').text());
        })

        $(document).on('click','.updateqty', function() {
            _this=$(this);
            $.ajax({
                type: "GET",
                url: "{{ URL::route('updateQty') }}",
                data: { 
                    _token: csrf_token,
                    id: _this.attr('data-id'),
                    qty: _this.prev().val()
                },
                success: function (result) {
                    parent = $('#newqtycontainer').parent();
                    $(parent).text(result);
                    $('#newqtycontainer').remove();
                }
            })
        })

        $('#products tbody').on('click', 'td', function (e) {
            var data = table.api().row( this ).data();
            var visIdx = $(this).index();

            if (visIdx==6) {
                if ($('#newqtycontainer').length>0)
                    $('#newqtycontainer').remove();

                if ($('#products').find('#newprice').length==0) {
                    id = $(data[2]).text().replace(/\s/g,'')
                    $(this).append('<div id="newpricecontainer"><input type="text" id="newprice" style="width: 60px" /><button class="btn btn-primary btn-sm updateprice mr-1" data-id="'+id+'"><i class="fa fa-check"></i></button><button class="btn btn-danger btn-sm cancelprice"><i class="fa fa-times"></i></button></div>')
                    $('#newprice').focus();
                }

                return
            }

            if (visIdx==8) {
                if ($('#newpricecontainer').length>0)
                    $('#newpricecontainer').remove();

                if ($('#products').find('#newqty').length==0) {
                    id = $(data[2]).text().replace(/\s/g,'')
                    $(this).append('<div id="newqtycontainer"><input type="text" id="newqty" style="width: 60px" /><button class="btn btn-primary btn-sm updateqty mr-1" data-id="'+$(data[2]).text()+'"><i class="fa fa-check"></i></button><button class="btn btn-danger btn-sm cancelqty"><i class="fa fa-times"></i></button></div>')
                    $('#newqty').focus();
                }
                return
            }

            if (visIdx==1 || visIdx==2)
                if ($(data[visIdx])[0].tagName=="A") return

            if (visIdx==3 && e.target.className == 'repair_link' ) return
                

            var dataIdx = table.api().column.index( 'fromVisible', visIdx );

            if (table.api().column( this ).index() != 0) {
                //alert( 'You clicked on '+data[1].replace(' *','')+'\'s row' );
                id = $(data[2]).text();
                
                group_id=$(this).parents('tr').find('td').eq(0).find('span').text();
                if (group_id==0)
                    window.location.href = 'products/'+id.replace(/\s/g,'')+'/edit';
                else if (group_id==1)
                    window.location.href = 'products/'+id.replace(/\s/g,'')+'/jewelryedit';
                else
                    window.location.href = 'products/'+id.replace(/\s/g,'')+'/bezeledit';
            }
        } );
    
        function globalProperties(ids) {
            $.confirm({
                title: 'Global Properties',
                content: $('#properties').html(),
                boxWidth: '30%',
                useBootstrap: false,
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function () {
                            var request = $.ajax({
                                url: "{{ route('change.global.properties') }}",
                                data: {
                                    newprice:  this.$content.find('#webprice-input').val(), 
                                    ids: ids,
                                    status: this.$content.find('.p_status').val(), 
                                },
                                success: function (result) {
                                    $.alert (result)
                                }
                            })
                            request.fail( function (jqXHR, textStatus) {
                                $.alert (textStatus)
                            })
                        }
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    
                }
            });

        }

        function displayRepairDialog(elem) {
            var request = $.ajax({
                url: "{{ route('repair.status') }}",
                data: {id:  elem},
                success: function (result) {
                    $.confirm({
                        title: 'At Repair Center',
                        content: $('#repair-form').html(),
                        boxWidth: '40%',
                        useBootstrap: false,
                        buttons: {
                            formSubmit: {
                                text: 'Submit',
                                btnClass: 'btn-blue',
                                action: function () {
                                    var assigned_to = this.$content.find('#assigned-to-input').val();
                                    if(!assigned_to){
                                        $.alert('Provide a valid service personal.');
                                        return false;
                                    }
                                
                                    var request = $.ajax({
                                        method: "post",
                                        url: "{{ route('repair.update') }}",
                                        data: {
                                            watchmaker:  assigned_to,
                                            repair_reason: this.$content.find('#jobs-input').val(),
                                            repair_notes: this.$content.find('#notes-input').val(),
                                            completed: this.$content.find('#isCompleted').is(":checked"),
                                            product_id: elem
                                        },
                                        success: function (result) {
                                            $.alert (result)
                                        }
                                    })
                                    request.fail( function (jqXHR, textStatus) {
                                        $.alert (textStatus)
                                    })
                                }
                            },
                            cancel: function () {
                                //close
                            },
                        },
                        onContentReady: function () {
                            // bind to events
                            var jc = this;
                            
                            this.$content.find('#assigned-to-input').val(result[0])
                            this.$content.find('#jobs-input').val(result[1])
                            this.$content.find('#notes-input').val(result[2])

                            this.$content.find('form').on('submit', function (e) {
                                // if the user submits the form by pressing enter in the field.
                                e.preventDefault();
                                jc.$$formSubmit.trigger('click'); // reference the button and click it
                            });
                        }
                    });                    
                }
            })
            request.fail( function (jqXHR, textStatus) {
                $.alert (textStatus)
            })

        }

        $(document).on('click','.repair_link', function(e) {
            e.preventDefault();
            product_id = $(this).parent('td').prev().find('a').text();
            displayRepairDialog(product_id)
        })

        function globalPriceChange() {
            $.confirm({
                title: 'Global Pricer',
                content: '' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>Enter a new percent rate for 3rd Party prices</label>' +
                '<input type="text" autofocus placeholder="Percent" class="name form-control" required />' +
                '</div>' +
                '</form>',
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function () {
                            var name = this.$content.find('.name').val();
                            if(!name){
                                $.alert('Provide a valid percent rate.');
                                return false;
                            } else if (name.charAt(0) == '0' || name.charAt(0) == '.') {
                                $.alert('Enter number without zeros or decimal in front of digits.');
                                return false;
                            }
                         
                            var request = $.ajax({
                                url: "{{ route('global.price.change') }}",
                                data: {percent:  this.$content.find('.name').val()},
                                success: function (result) {
                                    $.alert (result)
                                }
                            })
                            request.fail( function (jqXHR, textStatus) {
                                $.alert (textStatus)
                            })
                        }
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });

        }

        function printItemBarcode(id) {
            var printWindow = window.open("products/"+id+"/print", "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400");
            var printAndClose = function() {
                if (printWindow.document.readyState == 'complete') {
                    clearInterval(sched);
                }
            }
             var sched = setInterval(printAndClose, 1000);
        }

        $('.dropdown-menu button').click( function(e) {
            e.preventDefault();
            
            data = table.api().rows( { selected: true } ).data();
            var dataId= $(e.currentTarget).attr('data-id'); 
            if (dataId == '3DPrices') {
                globalPriceChange();
                return false;
            } 

            if (data[0] == undefined) {
                $.alert ('No product selected.');
                return;
            }

            id = $(data[0][2]).text().replace(/\s|\*/gi,'')

            if (dataId == 'delete') {
                if (confirm('Are you sure you want to delete selected product?')) {
                    window.location.href="products/"+id+'/destroy';
                }
            }else if (dataId == 'print') {
                printItemBarcode(id);
                
            }else if (dataId == 'restore') {
                if (confirm('Are you sure you want to restore selected product?')) {
                    window.location.href="products/"+id+'/restore';
                }
            }else if (dataId == 'duplicate') {
                window.location.href="products/"+id+'/duplicate';
            }else if (dataId == 'invoice') {
                var _ids = [];
                
                //var data = table.$('input, select').serialize();
                $.each (data, function(key,value) {
                    id=$(data[key][2]).text().replace(/\s/g,'');
                    
                    _ids.push(id);
                })
                window.location.href="orders/create/?id[]="+_ids.toString().replace(/\,/g,'&id[]=');
            } else if (dataId == 'properties') {
                var _ids = [];
                $.each (data, function(key,value) {
                    id=$(data[key][2]).text().replace(/\s/g,'');
                    
                    _ids.push(id);
                })

                globalProperties(_ids);
                return false;
            }

        })

        // $('#products_filter input').on('keydown', null, 'F9', function(){
        //     window.location.href="products/create/";
        // });
    })    
</script>
@endsection