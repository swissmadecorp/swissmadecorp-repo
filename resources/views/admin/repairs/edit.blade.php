@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 

<link href="/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')

<p><b>Date Received: {{ $repair->created_at->format('m/d/Y') }}</b></p>
<p><b>Date Updated: {{ $repair->updated_at->format('m/d/Y') }}</b></p>
<form method="POST" action="{{route('repairs.update',[$repair->id])}}" accept-charset="UTF-8" id="repairForm">
    @csrf
    @method('PATCH')

<p>Assigned To:  
<input autocomplete="off" name="assigned_to" id="watchmaker-input" class="form-control" type="text" value="{{$repair->assigned_to}}">
</p>

<div class="p-1"  style="clear: both">
    <div class="form-group row firstname">
        <label for="firstname-input" class="col-3 col-form-label">First Name</label>
        <div class="col-9 input-group">
        <input autocomplete="off" name="firstname" id="firstname-input" class="form-control" type="text" value="{{$repair->firstname}}">
        </div>
    </div>
    <div class="form-group row lastname">
        <label for="lastname-input" class="col-3 col-form-label">Last Name</label>
        <div class="col-9 input-group">
            <input autocomplete="off" name="lastname" id="lastname-input" class="form-control" type="text" value="{{$repair->lastname}}">
        </div>
    </div>
    <div class="form-group row company">
        <label for="company-input" class="col-3 col-form-label">Company</label>
        <div class="col-9 input-group">
            <input autocomplete="off" name="company" id="company-input" class="form-control" type="text" value="{{$repair->company}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="address1-input" class="col-3 col-form-label">Address 1</label>
        <div class="col-9 input-group">
            <input name="address1" id="address1-input" class="form-control" type="text" value="{{$repair->address1}}">
        </div>
    </div>
    <div class="form-group row">
        <label for="phone-input" class="col-3 col-form-label">Phone</label>
        <div class="col-9 input-group">
        <input name="phone" id="phone-input" class="form-control" type="text" value="{{$repair->phone}}">
        </div>
    </div>        

    <div class="form-group row">
        <label for="email-input" class="col-3 col-form-label">Email</label>
        <div class="col-9 input-group">
        <input name="email" id="email-input" class="form-control" type="text" value="{{$repair->email}}">
        </div>
    </div>            
</div>


<table class="table table-striped table-bordered hover repair-products">
    <thead>
        <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Image</th>
        <th>Qty</th>
        <th>Serial#</th>
        <th>Cost</th>
        <th>Charge Amt.</th>
        <th>Jobs</th>
        <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php $subtotal=0; $product = $repair->products->first();?>
        
        @foreach ($repair->jobs as $job)
        
        <?php 
        
            $p_image = $product->images->first()->location;
            if (!empty($p_image)) {
                $image=$p_image;
            } else $image = 'no-image.jpg';
        ?>
        <?php 
            $subtotal+=$job->amount;
        ?>
        <tr>
            <td>{{ $job->product_id }}</td>
            <td  style="width:28%">
                <input type="text" name="product_name[]" value="{{ $product->title }}" />
                <input type="hidden" value="{{ $job->id }}" name="job_id[]">
            </td>
            <td>
                <div style="position: relative">
                    <img style="width: 70px" src="/images/{{ $image }}" job_id="{{ $repair->id }}" />
                    <input type=button value="Snapshot" class="activeSnapshot btn btn-primary btn-sm">
                
                    <input type=button value="Take Snapshot" class="takesnapshot btn btn-success btn-sm" >
                    <div class="captureimage_0"></div>
                    <input type="hidden" name="filename[]">
                    <button type="button" class="delete-image">X</button>
                </div>
            </td>
            <td>1</td>
            <td style="text-align: right">{{ $job->serial }}</td>
            <td style="text-align: right"><input type="text" style="width: 80px" value="{{ $job->cost }}" name="cost[]"></td>
            <td style="text-align: right"><input type="text" style="width: 80px" value="{{ $job->amount }}" name="price[]"></td>
            <td>
            
                <?php 
                if ($job->job!="N;") {
                    foreach (explode(",",unserialize($job->job)) as $sjob) {
                        echo $sjob.'<br>';
                    } 
                }
            ?></td>
            <td style="text-align: right;width: 30px">
                <button class="btn btn-danger delete align-center" data-id="{{$job->id}}"><i class="fa fa-trash" aria-hidden="true"></i></button>
            </td>
        </tr>
        <tr>
            <td colspan="8">{{ $job->instructions }}</td>
        </tr>
        <?php $subtotal+=$repair->amount?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="text-align: right;font-weight: bold" colspan="7">Freight</td>
            <td><input name='freight' value="{{ $repair->freight>0 ? $repair->freight : '0.00' }}" id="s-freight-input" style="width: 100px; text-align: right;display:inline" class="form-control" /></td>
        </tr>
        <tr>
            <td style="text-align: right;font-weight: bold" colspan="7">Total</td>
            <td style="text-align: right;">{{ number_format($repair->freight+$subtotal,2) }}</td>
        </tr>
    </tfoot>

</table>

@if ($repair->status==0)
<br>
<b>Mark as Paid/Complete: </b><input type="checkbox" <?php echo $repair->status==1 ? 'checked' : '' ?> name="paid" style="height: 20px;width: 20px;">
@endif

<div style="clear: both"></div>
<br>

<div class="form-group row">
    <div class="col-12">
        <label for="comments-input" class="col-form-label">Internal Comments</label>
        <textarea type="text" name="comments" id="comments-input" style="height: 150px; overflow-y:auto;overflow-x:hidden" class="form-control">{{ $repair->comments }}</textarea>
    </div>    
</div>

<div class="form-group row">
    <div class="col-12">
        <label for="customer_comments-input" class="col-form-label">Customer Comments</label>
        <textarea type="text" name="customer_comments" id="comments-input" style="height: 150px; overflow-y:auto;overflow-x:hidden" class="form-control">{{ $repair->customer_comments }}</textarea>
    </div>    
</div>

<div class="float-right"><a href="{{ URL::to('admin/repairs/'.$repair->id.'/0/print') }}" class="btn btn-primary pull-left" style="margin-right: 3px;">Customer print</a></div>
<div class="float-right mr-2"><a href="{{ URL::to('admin/repairs/'.$repair->id.'/1/print') }}" class="btn btn-primary pull-left" style="margin-right: 3px;">Watchmaker print</a></div>

<div class="float-right mr-2">
    <button type="submit" class="btn btn-danger update">Update Ticket</button>
</div>

</form>
<div id="product-container"></div>
<div id="search-customer"></div>
@endsection

@section ('footer')
<script src="/js/jquery.autocomplete.min.js') }}"></script>
<script src="/js/webcam.min.js"></script>
<script src="/fancybox/jquery.fancybox.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      

@section ('jquery')
<script>
    var csrf_token = "{{csrf_token()}}";
    var blade = 'edit';
    
    Webcam.set({
        width: 320,
        height: 240,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    
    $(document).ready( function() {

        $(document).on('click','.activeSnapshot', function () {
            var row=$(this).parents('tr');
            div = row.find('td:eq(2)').find('div');
            Webcam.attach( '.'+div.attr('class') );

            $(this).hide();
            row.find('td:eq(2)').find('.takesnapshot').show();
        })

        $(document).on('click','.takesnapshot', function() {
            // take snapshot and get image data

            var row=$(this).parents('tr');
            Webcam.snap( function(data_uri) {
                // display results in page
                
                var product_name=row.find('td:eq(2)').find('input').val();
                if (product_name=='') {
                    alert ('Please enter Product Name first.')
                    return;
                }

                var request = $.ajax({
                    type: "POST",
                    url: "{{route('capture.image')}}",
                    data: { 
                        _token: "{{csrf_token()}}",
                        captured_image: data_uri,
                        title: product_name,
                        blade: 'repair'
                    },
                    success: function (result) {
                        if (result.error == false) {
                            row.find('td:eq(1)').find('div').hide();
                            row.find('td:eq(1)').find('.takesnapshot').hide();
                            
                            row.find('td:eq(1)').append('<img />');
                            newimg = row.find('td:eq(1)').find('img')
                            newimg.attr('src','/images/'+result.filename)
                            newimg.css('width','80px');
                            row.find('td:eq(2)').find('input').val(result.filename);
                        } else
                            alert (result.message);
                    }
                })

            } );
        })
    
        var getPath = "{{route('get.customer.info')}}";
        var mainPath = "{{route('get.customer.byID')}}";

        function fillInData(data) {
            $('#customer_id').val(data.id);
            for (name in data) {
                if (name != 'id') {
                    if (data[name]) {
                        $('#'+ name +'-input').val(data[name]);
                    } else {
                        $('#'+ name +'-input').val('');
                    }   
                }
            }
        }

        $(document).on('click','.delete-image', function() {
            var _this = $(this);
            var img = $(this).parent().find('img')
            var request = $.ajax({
                type: "POST",
                url: "{{route('delete.image')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    job_id: img.attr('job_id'),
                    filename: img.attr('src')
                },
                success: function (result) {
                    $(_this).parent().find('img').remove()
                    $(_this).hide()
                }
            })
            request.fail( function (jqXHR, textStatus) {
                //alert ("Requeset failed: " + textStatus)
            })
        })
        
        $('#firstname-input').devbridgeAutocomplete({
            serviceUrl: mainPath,
            showNoSuggestionNotice : true,
            minChars: 3,
            zIndex: 900,
            onSelect: function (suggestion) {
                $.ajax({
                    type: "GET",
                    url: getPath,
                    data: { 
                        _token: csrf_token,
                        _id: suggestion.data,
                        _searchBy: $(this).attr('id')
                    },
                    success: function (result) {
                        if (result) {
                            fillInData(result);
                        }
                    }
                })
            }
        });

        $('.billing input').on('input propertychange', function() {
            id=$(this).attr('id');
            $('#s'+id.substr(1)).val($(this).val());
        })

        $(".addnew").on('click', function(e) {
            e.preventDefault();
            $.ajax({
                type: "GET",
                url: "{{route('ajax.products')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                },
                success: function (result) {
                    $('#product-container').html(result.content+result.jquery);

                    $.fancybox.open({
                        src: "#product-container",
                        type: 'inline',
                        width: 980,
                    });
                }
            })
        });

        $('.fancybox-close-small').click( function () {
            $.fn.fancybox.close()
        })

        paymentOptions('{{$repair->method}}');
        $('#payment-input').change( function () {
            paymentOptions(this.value);
        })

        function paymentOptions(method) {
            $('#payment-options-name-input option').each (function () {
                if (method=='Invoice') {
                    if (this.value=='None')
                        $(this).hide()
                    else $(this).show()
                } else {
                    if (this.value!='None') {
                        $(this).hide()
                        $('#payment-options-name-input option').eq(7).prop('selected','')
                    } else { 
                        $(this).show()
                        $('#payment-options-name-input option').eq(7).prop('selected','selected')
                    }
                }
            })
            if (method=='Invoice') 
                 $('#payment-options-name-input option').eq(0).prop('selected','selected')

        }

        $(".delete").on('click', function(e) {
            _this = $(this);
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this item from the repair?')) return

            $.ajax({
                type: "GET",
                url: "{{route('delete.repair.product')}}",
                data: { 
                    _token: "{{csrf_token()}}",
                    repairid: "{{$repair->id}}",
                    productid: _this.attr('data-id')
                },
                success: function (result) {
                    if (result=='success') {
                        location.reload(true);
                    } else 
                        alert('There were some errors while deleting this product.')
                }
            })
        });
        
        $(document).on('click','.deleteitem', function(e) {
            e.preventDefault();
            $(this).parents('tr').remove();
        })

    })

</script>
@endsection