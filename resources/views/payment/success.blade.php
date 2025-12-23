@extends ("layouts.default")

@section('title', 'Order Confirmation')

@section ('content')
    @if (Session::has('exchange_rate'))
        <?php 
        $rate = session('exchange_rate')['rate'];
        $symbol = session('exchange_rate')['symbol'].' '; ?>
    @else
        <?php $rate = 1; $symbol = "$ "; ?>
    @endif

    <h2>Order Confirmation</h2>
    <hr>

    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            @if (isset($response['error']))
                <h3>Uncofirmed Address</h3>
                <br>
                Dear {{$response['SHIPTONAME']}},<br><br>
                Unfortuantely your PayPal Address is not confirmed.<br>
                At this time we only ship to a confirmed address.
                <br><br>Please fix the problem with PayPal and try again.
                <br><br>We apologize for any inconvenience this may have caused you.

            @else
            <div class="col-md-12">
                <table>
                    <tr>
                        <td>
                        <b>Ship To Address</b>
                        </td>
                    </tr>
                    @isset($response['BUSINESS'])
                    <tr><td>{{ $response['BUSINESS'] }}</td></tr>
                    @endisset
                    <tr><td>{{ $response['SHIPTONAME'] }}</td></tr>
                    <tr><td>{{ $response['SHIPTOSTREET'] }}</td></tr>
                    <tr><td>{{ $response['SHIPTOCITY'] }}, {{ $response['SHIPTOSTATE'] }} {{ $response['SHIPTOZIP'] }}</td></tr>
                    <tr><td>{{ $response['PHONENUM'] }}</td></tr>
                    <tr><td>{{ $response['SHIPTOCOUNTRYNAME'] }}</td></tr>
                </table> 
                <br><hr>
                <table class="table table-bordered table-sm">
                    <thead>
                    <tr>
                        <th scope="col" class="table-active">Product Name</th>
                        <th scope="col" class="table-active">Quantity</th>
                        <th scope="col" class="table-active">Price</th>
                        <th scope="col" class="table-active">Total Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0; $totalPrice=0;$tatal=0;
                    
                    foreach ($response as $key => $product) {
                        if (isset($response['L_AMT'.$i])) {
                            $total = $response['L_AMT'.$i]*$response['L_QTY'.$i];
                            $totalPrice += $total;
                            if ($response['L_NAME'.$i]=="Discount") {
                                echo "<tr><td style='color: red;'>".$response['L_NAME'.$i]."</td>";
                                echo "<td style='color: red;' class='text-center'>-".$response['L_QTY'.$i]."</td>";
                                echo "<td style='color: red;' class='text-right'>".$symbol.number_format($response['L_AMT'.$i],2)."</td>";
                                echo "<td style='color: red;' class='text-right'>".$symbol.number_format($total,2)."</td>";
                            } else {
                                echo "<tr><td>".$response['L_NAME'.$i]."</td>";
                                echo "<td class='text-center'>".$response['L_QTY'.$i]."</td>";
                                echo "<td class='text-right'>".$symbol.number_format($response['L_AMT'.$i],2)."</td>";
                                echo "<td class='text-right'>".$symbol.number_format($total,2)."</td>";
                            }
                            $i++;
                            echo '</tr>';
                            
                            continue;
                        }
                    

                        if (!isset($response['L_AMT'.$i])) {
                            break;
                        }

                    }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="font-weight-bold text-right">Sub Total:</td>
                            <td class='text-right'>{{ number_format($totalPrice,2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="font-weight-bold text-right">Overnight Shipping:</td>
                            <td class='text-right'>{{$symbol.number_format($response['SHIPPINGAMT'],2)}}</td>
                        </tr>
                        @if ($response['TAXAMT']>0)
                        <tr>
                            <td colspan="3" class="font-weight-bold text-right">Tax (NYS):</td>
                            <td class='text-right'>{{$symbol.number_format($response['TAXAMT'],2)}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="font-weight-bold text-right">Total Amount:</td>
                            <td class='text-right'>{{$symbol.number_format($response['AMT']+$response['TAXAMT']+$response['SHIPPINGAMT'],2)}}</td>
                        </tr>
                    </tfoot>
                </table>
        
            </div>

            <div class="col-md-4">
                <?php //$data = array('token'=>$response['TOKEN'],'payerId'=>$response['PAYERID']) ?>

            {{  Form::open(array('route'=>array('payment.checkout','token'=>$response['TOKEN']))) }} 
                {{Form::submit('Complete Checkout', $attributes = array("class"=>"form-control btn btn-primary"))}}
            {{  Form::close() }}  
            
            </div>

            @endif
        </div>
    </div>
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
