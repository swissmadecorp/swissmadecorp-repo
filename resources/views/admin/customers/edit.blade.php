@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

    <?php 
        // echo __DIR__;
        $customer_groups = ['Dealer','Customer'];
    ?>
    
    <form method="POST" action="{{route('customers.update',[$customer->id])}}" accept-charset="UTF-8" id="customerform">
    @csrf
    @method('PATCH')
    <input type="hidden" value="customer" name="blade" />
    <input type="hidden" value="{{$customer->id}}" name="_id" />

    <div class="form-group row">
        <label for="customer-group-input" class="col-3 col-form-label">Customer Group</label>
        <div class="col-9">
            <select class="form-control" name="customer-group" id="customer-group-input">
                @foreach ($customer_groups as $value => $customer_group)
                    <option value="{{ $value }}" <?= $customer->cgroup == $value ? 'selected' : '' ?>>{{ $customer_group }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="firstname-input" class="col-3 col-form-label">First Name</label>
        <div class="col-9">
            <input class="form-control" value="<?= !empty($customer->firstname) ? $customer->firstname : '' ?>" type="text" name="firstname" id="firstname-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="lastname-input" class="col-3 col-form-label">Last Name</label>
        <div class="col-9">
            <input class="form-control" value="<?= !empty($customer->lastname) ? $customer->lastname : '' ?>" type="text" name="lastname" id="lastname-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="company-input" class="col-3 col-form-label">Company</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?= !empty($customer->company) ? $customer->company : '' ?>" type="text" name="company" id="company-input" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="address-input" class="col-3 col-form-label">Address 1</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?= !empty($customer->address1) ? $customer->address1 : '' ?>" type="text" name="address" id="address-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="address2-input" class="col-3 col-form-label">Address 2</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?= !empty($customer->address2) ? $customer->address2 : '' ?>" type="text" name="address2" id="address2-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="phone-input" class="col-3 col-form-label">Phone</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?= !empty($customer->phone) ? $customer->phone : '' ?>" type="text" name="phone" id="phone-input">
        </div>
    </div>    
    <div class="form-group row">
        <label for="country-input" class="col-3 col-form-label">Country</label>
        <div class="col-9">
            @inject('countries','App\Libs\Countries')
            <?= $countries->getAllCountries($customer->country) ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="state-input" class="col-3 col-form-label">State</label>
        <div class="col-9">
            <?= $countries->getAllStates($customer->state) ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="city-input" class="col-3 col-form-label">City</label>
        <div class="col-9">
            <input class="form-control" value="<?= !empty($customer->city) ? $customer->city : '' ?>" type="text" name="city" id="city-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="zip-input" class="col-3 col-form-label">Zip Code</label>
        <div class="col-9">
            <input class="form-control" value="<?= !empty($customer->zip) ? $customer->zip : '' ?>" type="text"  name="zipcode" id="zipcode-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="email-input" class="col-3 col-form-label">Email</label>
        <div class="col-9">
            <input class="form-control" value="<?= !empty($customer->email) ? $customer->email : '' ?>" type="text" name="email" id="email-input">
        </div>
    </div>

    <div class="form-group row">
        <label for="markup-input" class="col-3 col-form-label">Markup</label>
        <div class="col-9">
            <input class="form-control" autocomplete="off"  value="<?= !empty($customer->markup) ? $customer->markup : '' ?>" type="text" name="markup" id="markup-input">
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary uploadPhoto">Save</button>
    <button class="btn btn-primary combine">Combine customers</button>  
    <hr/>

    <h4>Previous Orders</h4>
    <hr/>
    <div class="table-responsive">
    <table id="orders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Order Id</th>
                <th>Status</th>
                <th>Date</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        
        <?php $grandTotal = 0; ?>

        @foreach ($customer->orders as $order)
        <?php $grandTotal += $order->total ?>
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{ $order->status == 0 ? "Unpaid" : "Paid" }}</td>
            <td class="text-right">{{ $order->created_at->format('m/d/Y') }}</td>
            <td class="text-right">${{ number_format($order->total,2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th></th>
            <th class="text-right" >Total</th>
            <th class="text-right" >${{ number_format($grandTotal,2) }}</th>
        </tr>
    </tfoot>
    </table>

</div>


<h4>As Supplier</h4>
    <hr/>
    <div class="table-responsive">
    <table id="orders" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Date</th>
                <th>Prch. Amount</th>
            </tr>
        </thead>
        <tbody>
        

        @foreach ($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->title }}</td>
            <td class="text-right">{{ $product->created_at->format('m/d/Y') }}</td>
            <td class="text-right">${{ number_format($product->p_price,2) }}</td>
        </tr>
        @endforeach
    </tbody>
    </table>

</div>

    @include('admin.errors')
</form>

<div id="combineCustomers" style="display:none">
    <label>Enter customer number to consolidate with this customer. Use this feature if you have identical customers with different customer ids.
    <br><b>Note: </b> Consolidating customers will be permanent and cannot be undone. Make sure you are consolidating the right customers.</label>
    <form action="" class="formName container">
        <div class="form-group">
            <div class="form-group row">
                <label for="customer-id-input" class="col-4 col-form-label">Customer</label>
                <div class="col-8 input-group">
                    <!-- <input class="form-control" type="text" name="customer_id" value="0" id="customer-id-input">   -->
                    <select style="width: 100%" name="customer-id-input" id="customer-id-input"></select>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        @include ('admin.countrystate')
        var table = $('#orders').DataTable({
            "deferRender": true,
            "columns": [
                { "width": "5%" },
                { "width": "15%" },
                { "width": "15%" },
                { "width": "20%" },
            ],
            "createdRow": function( row, data, dataIndex){
                if( data[1] ==  'Unpaid'){
                    $(row).addClass('unpaid');
                }
            }
        });

        $('#orders tbody').on('click', 'td', function () {
            var data = table.row( this ).data();
            var visIdx = $(this).index();
            var dataIdx = table.column.index( 'fromVisible', visIdx );

            window.location.href = '/admin/orders/'+data[0];
        } );

        $.ajax({
            url: "{{ route('get.customers') }}",
            data: {
                sortBy:  'company', 
                currentId: "{{ $customer->id }}"
            },
            success: function (result) {
                for (var i=0;i<result['data'].length; i++) {
                    $("#customer-id-input").append("<option value='"+result['data'][i][1]+"'>"+result['data'][i][2]+"</option>")
                }
            }
        })

        $('.combine').click( function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Combine Duplicate Customers',
                content: $('#combineCustomers').html(),
                // boxWidth: '35%',
                useBootstrap: false,
                buttons: {
                    formSubmit: {
                        text: 'Submit',
                        btnClass: 'btn-blue',
                        action: function () {
                            var request = $.ajax({
                                url: "{{ route('combine.duplicate.customers') }}",
                                data: {
                                    customerid:  this.$content.find('#customer-id-input').val(), 
                                    currentId: "{{ $customer->id }}"
                                },
                                success: function (result) {
                                    location.reload();
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
        })

    })
</script>
@endsection