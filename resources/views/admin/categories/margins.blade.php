@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')
<h4>Products Purchased</h4>
<table id="products" class="table table-striped table-bordered hover">
    <thead>
        <tr>
        <th>Product</th>
        <th>Purchased</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
        <tr>
            <td>{{ $product->p_model }}</td>
            <td><?php echo $product->product_id ? 'Yes' : 'No' ?></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td><input type="text" name="search_product" value="Search product" class="search_init"></td>
            <td><input type="text" name="search_purchased" value="Search purchased" class="search_init"></td>
            
        </tr>
    </tfoot>
</table>

<hr>
<div>
    <h4>Unpaid Orders</h4>
    <div class="clearfix" style="padding: 3px">
        <div class="float-right">
            <button type="submit" id="printUnpaid" class="btn btn-secondary">Print Orders</button>
            
        </div>
        </div>
    <hr>
    <table id="orders" class="table table-striped table-bordered hover">
        <thead>
            <tr>
            <th>Order Id</th>
            <th>Customer</th>
            <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0;$subtotal=0;?>
            @foreach ($orders as $order)
            <?php $isPaid = false?>
            <?php $grandTotal += $order->total ?>
            <?php $subtotal = $order->total ?>
            
            @foreach($order->payments as $payment)
                <?php $subtotal -= $payment->amount ?>
                <?php $grandTotal -= $subtotal ?>
            @endforeach
            <?php if ($subtotal==0) $isPaid=true; ?>
        <tr>
            <td>{{ $order->id }}</td>
            <td>{{$order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname }}</td>
            <td class="text-right" style="<?= $isPaid ? "color:green" : "color:red" ?>">${{ number_format($subtotal,2) }}</td>
        </tr>
        @endforeach
            
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td class="text-right" style="font-weight:bold">Total</td>
                <td class="text-right" style="font-weight:bold">${{ number_format($grandTotal,2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<hr>
<div>
    <h4>Paid Orders</h4>
    <div class="clearfix" style="padding: 3px">
        <div class="float-right">
            <button type="submit" id="printPaid" class="btn btn-secondary">Print Orders</button>
            
        </div>
    </div>
    <hr>
    <table id="paidOrders" class="table table-striped table-bordered hover">
        <thead>
            <tr>
            <th>Order Id</th>
            <th>Customer</th>
            <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $grandTotal = 0;$subtotal=0;?>
            @foreach ($paidOrders as $order)
                <?php $grandTotal += $order->total ?>
                <?php $subtotal = $order->total ?>
                
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{$order->s_company != '' ? $order->s_company : $order->s_firstname . ' '.$order->s_lastname }}</td>
                <td class="text-right" style="color: green">${{ number_format($subtotal,2) }}</td>
            </tr>
            @endforeach
            
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td class="text-right" style="font-weight:bold">Total</td>
                <td class="text-right" style="font-weight:bold">${{ number_format($grandTotal,2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        var asInitVals = new Array();

        var oTable = $('#products').dataTable({
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            "order": [[ 1, "desc" ]]
        })

       $('#orders').dataTable();

        $("tfoot input").keyup( function () {
        /* Filter on the column (the index) of this element */
            oTable.fnFilter( this.value, $("tfoot input").index(this) );
        } );

         /*
        * Support functions to provide a little bit of 'user friendlyness' to the textboxes in
        * the footer
        */
        $("tfoot input").each( function (i) {
            asInitVals[i] = this.value;
        } );
        
        $("tfoot input").focus( function () {
            if ( this.className == "search_init" )
            {
                this.className = "";
                this.value = "";
            }
        } );
        
        $("tfoot input").blur( function (i) {
            if ( this.value == "" )
            {
                this.className = "search_init";
                this.value = asInitVals[$("tfoot input").index(this)];
            }
        } );
    })
</script>
@endsection