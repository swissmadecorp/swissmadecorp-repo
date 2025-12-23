@extends ("layouts.default-new")

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

    @if (!session()->has('order'))
    <div class="flex justify-center">
        <div class="container p-[150px]">
            <h3 class="text-3xl mb-5">Multiple page refresh ditected</h3>
            <br>
            <h4 class="text-xl">Your order has already been processed.</h4><br>
            If you would like to modify your order, please call us at 212-840-8463.<br><br>
        </div>
    </div>
    @else
    
        <?php $order=session()->get('order'); ?>
        
        <div class="flex justify-center">

        @inject('countries','App\Libs\Countries')
        <div class="container">
            <h2 class="text-3xl mt-2">Thank you for your Order!</h2>
            <h3 class="text-2xl mb-6">Your order# is {{ $order->id }}</h3>
            
            <div class="col-md-12">
                <div class="bg-gray-50 gap-[12rem] items-start md:flex p-2 rounded-md w-full">
                    <div>
                        <h3 class="text-lg font-bold">Billing Address</h3>
                        <?php if (!empty($order->s_company) && $order->s_company != $order->s_firstname . ' ' . $order->s_lastname) {?>
                            {{ $order->s_company }}<br>
                        <?php } ?>
                        {{ $order->s_firstname }} {{ $order->s_lastname }}<br>
                        {{ $order->s_address1 }}<br>
                        <?php if (!empty($order->s_address2)) {?>
                            {{ $order->s_address2 }}<br>
                        <?php } ?>
                        {{ $order->s_phone }}<br>
                        {{ $order->s_city }}, {{ $countries->getStateByCode($order->s_state) }} {{ $order->s_zip }}<br>
                        {{ $countries->getCountry($order->s_country) }}<br>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold">Shipping Address</h3>
                        <?php if (!empty($order->s_company) && $order->s_company != $order->s_firstname . ' ' . $order->s_lastname) {?>
                            {{ $order->s_company }}<br>
                        <?php } ?>
                        {{ $order->s_firstname }} {{ $order->s_lastname }}<br>
                        {{ $order->s_address1 }}<br>
                        <?php if (!empty($order->s_address2)) {?>
                            {{ $order->s_address2 }}<br>
                        <?php } ?>
                        {{ $order->s_phone }}<br>
                        {{ $order->s_city }}, {{ $countries->getStateByCode($order->s_state) }} {{ $order->s_zip }} <br>
                        {{ $countries->getCountry($order->s_country) }}<br>
                    </div>
                </div>

                
                <hr class="mb-4">
                Payment - {{$order->payment_options}}
                <hr  class="mt-4">
                <table class="table cart w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="py-3 px-6">Image</th>
                        <th class="py-3 px-6">Product Name</th>
                        <th class="py-3 px-6">Quantity</th>
                        <th class="py-3 px-6">Price</th>
                        <th class="py-3 px-6">Total Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0; $totalPrice=0;$tatal=0;
                    
                        foreach ($order->products as $product) {
                            $total = $product->pivot->price *$product->pivot->qty;
                            $totalPrice += $total; ?>
                            <tr>
                                <td style='width: 110px'>
                                    <img src="/images/thumbs/{{$product->images->first()->location}}" />
                                </td>
                                <td>{{$product->pivot->product_name}}</td>
                                <td class='text-center'>{{$product->pivot->qty}}</td>
                                <td class='text-right'>${{number_format($product->pivot->price,2)}}</td>
                                <td class='text-right'>${{number_format($total,2)}}</td>
                            </tr>
                            <?php $i++; 
                        } 
                        
                        if ($order->discount)
                            $discount = $order->discount;
                        else $discount = 0;
                    ?>
                    </tbody>
                    <tfoot>
                        @if ($discount > 0)
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
