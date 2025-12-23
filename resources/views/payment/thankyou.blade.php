@extends ("layouts.default")

@section('title', 'Order Confirmation')

@section ('header')
    <style>
    .table-borderless td,
        .table-borderless th {
            border: 0 !important;
            padding: 0 !important;
        }
    </style>
@endsection

@section ('content')
    @if (!isset($order))
    <h3>Multiple page refresh ditected</h3>
    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            <div class="col-md-12">
                <br>
                <h4>Your order has already been processed.</h4><br>
                If you would like to modify your order, please call us at 212-840-8463.<br><br>
                Please do not click the back button.<br>Going back will make us charge you twice.

            </div>
        </div>
    </div>

    @else

    <h2>Thank you for your Order!</h2>
    <h3>Your order# is {{ $order->id }}</h3>
    <div class="stepper-wrapper">
        <div class="stepper-item completed">
            <div class="step-counter">1</div>
            <div class="step-name">Shopping Cart</div>
        </div>
        <div class="stepper-item completed">
            <div class="step-counter">2</div>
            <div class="step-name">Shipping Information</div>
        </div>
        <div class="stepper-item completed">
            <div class="step-counter">3</div>
            <div class="step-name">Payment Information</div>
        </div>
        <div class="stepper-item active">
            <div class="step-counter">4</div>
            <div class="step-name">Order Confirmation</div>
        </div>
    </div>

    <hr>

    @inject('countries','App\Libs\Countries')
    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            <div class="col-md-12">
                <table class="table table-borderless table-condensed table-hover">
                    <tr>
                        <td>
                        <b>Ship To Address</b>
                        </td>
                        <td><b>Payment Method</b></td>
                    </tr>
                    @isset($order->s_company)
                    <tr><td colspan="2">{{ $order->s_company }}</td></tr>
                    @endisset
                    <tr>
                        <td>{{ $order->s_firstname }} {{ $order->s_lastname}}</td>
                        <td>{{ $order->payment_options }}</td>
                    </tr>
                    <tr><td colspan="2">{{ $order->s_address1 }}</td></tr>
                    <tr><td colspan="2">{{ $order->s_city }}, {{ $order->s_state }} {{ $order->s_zip }}</td></tr>
                    <tr><td colspan="2">{{ $order->s_phone }}</td></tr>
                    <tr><td colspan="2">{{ $countries->getCountry($order->s_country) }}</td></tr>

                </table> 
                <br><hr>
                <table class="table table-bordered table-sm">
                    <thead>
                    <tr>
                        <th scope="col" class="table-active">Image</th>
                        <th scope="col" class="table-active">Product Name</th>
                        <th scope="col" class="table-active">Quantity</th>
                        <th scope="col" class="table-active">Price</th>
                        <th scope="col" class="table-active">Total Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0; $totalPrice=0;$tatal=0;
                    
                        foreach ($order->products as $product) {
                            $total = $product->pivot->price *$product->pivot->qty;
                            $totalPrice += $total;
                            echo "<tr><td style='width: 110px'><img src='/images/thumbs/".$product->images->first()->location."' /></td><td>".$product->pivot->product_name."</td>";
                            echo "<td class='text-center'>".$product->pivot->qty."</td>";
                            echo "<td class='text-right'>$".number_format($product->pivot->price,2)."</td>";
                            echo "<td class='text-right'>$".number_format($total,2)."</td>";
                            $i++;
                            echo '</tr>';
                        }
                        if ($order->discount)
                            $discount = $order->discount;
                        else $discount = 0;
                    ?>
                    </tbody>
                    <tfoot>
                        @if ($discount)
                        <?php $totalPrice -= $discount; ?>
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Discount:</td>
                            <td class='text-right' style="color: red">-{{ number_format($discount,2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Sub Total:</td>
                            <td class='text-right'>{{ number_format($totalPrice,2) }}</td>
                        </tr>
                        @if ($order->taxable>0)
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Tax (NYS):</td>
                            <td class='text-right'>{{number_format($totalPrice * ($order->taxable/100),2)}}</td>
                        </tr>
                        @endif
                        @if ($order->freight)
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Shipping:</td>
                            <td class='text-right'>{{number_format($order->freight,2)}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Total Amount:</td>
                            <td class='text-right'>${{number_format($order->total,2)}}</td>
                        </tr>
                    </tfoot>
                </table>
        
                @if ($order->payment_options == "Wire Transfer")
                    <h4>Our Bank Wire Information</h4>
                    <b>SWISS MADE CORP</b><br>
                    15 West 47th Street<br>
                    Suite 503<br>
                    New York, NY 10036<br><br>

                    <b>Bank of America</b><br>
                    550 5th Avenue<br>
                    New York, NY 10036<br>
                    Routing #: 021000322<br>
                    Account#: 483082594740<br>
                    US Wire Code: 026009593<br>
                    International Swift Code (IN US DOLLARS): BOFAUS3N<br>
                @endif
            </div>

        </div>
    </div>
    @endif
@endsection

@section ('footer')
    <script>
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };
    </script>
    <script src="{{ asset('/public/fancybox/jquery.fancybox.min.js') }}"></script>
    <script src="{{ asset('/public/js/parsley.js') }}"></script>
@endsection
