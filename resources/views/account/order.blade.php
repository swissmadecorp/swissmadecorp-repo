@extends('layouts.default')

@section('title', 'My Order')

@section ('content')

<hr>

<h3>Your order# is {{ $order->id }}</h3>
    <hr>

    @inject('countries','App\Libs\Countries')
    <div style="background: #fff; padding: 25px;border-radius: 4px;">
        <div class="container">
            <div class="col-md-12">
                <table>
                    <tr>
                        <td>
                        <b>Ship To Address</b>
                        </td>
                    </tr>
                    
                    @php $state_s = $countries->getStateCodeFromCountry($order->s_state); @endphp

                    @isset($order->s_company)
                    <tr><td>{{ $order->s_company }}</td></tr>
                    @endisset
                    <tr><td>{{ $order->s_firstname }} {{ $order->s_lastname}}</td></tr>
                    <tr><td>{{ $order->s_address1 }}</td></tr>
                    <tr><td>{{ $order->s_city }}, {{ $state_s }} {{ $order->s_zip }}</td></tr>
                    <tr><td>{{ $order->s_phone }}</td></tr>
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
                        $p_image = $product->images->toArray();
                        if (!empty($p_image)) {
                            if (file_exists(base_path().'/public/images/thumbs/'.$p_image[0]['location']))
                                $image='/public/images/thumbs/'.$p_image[0]['location'];
                            else $image = '/public/images/no-image.jpg'; 
                        } else $image = '/public/images/no-image.jpg';
                        
                        $total = $product->pivot->price * $product->pivot->qty;
                        $totalPrice += $total;
                        echo "<tr><td><img style='height: 80px' src='$image' /></td>";
                        echo "<td class='align-middle'>".$product->pivot->product_name."</td>";
                        echo "<td class='align-middle text-center'>".$product->pivot->qty."</td>";
                        echo "<td class='align-middle text-right'>$".number_format($product->pivot->price,2)."</td>";
                        echo "<td class='align-middle text-right'>$".number_format($total,2)."</td>";
                        $i++;
                        echo '</tr>';
                    }
                    if ($order->discount)
                        $discount = $order->discount;
                    else $discount = 0;
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Sub Total:</td>
                            <td class='text-right'>{{ number_format($totalPrice-$discount,2) }}</td>
                        </tr>
                        @if ($discount)
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Discount:</td>
                            <td class='text-right'>{{ number_format($discount,2) }}</td>
                        </tr>
                        @endif
                        @if ($order->taxable>0)
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Tax (NYS):</td>
                            <td class='text-right'>${{number_format($order->taxable,2)}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="font-weight-bold text-right">Total Amount:</td>
                            <td class='text-right'>${{number_format($order->total,2)}}</td>
                        </tr>
                    </tfoot>
                </table>

                @if ($order->tracking)
                <div class="form-group row">
                    <div class="col-12">
                        <label for="comments-input" class="col-form-label">Tracking No.</label>
                        <div id="tracking-input" class="form-control">
                            @if (strpos($order->tracking,',') > 0)
                                @php
                                    $trackings = explode(",",$order->tracking);
                                @endphp

                                @foreach ($trackings as $tracking)
                                <a target="_blank" href="https://www.fedex.com/apps/fedextrack/?tracknumbers={{ $tracking }}">{{ $tracking }}</a></button>
                                @endforeach
                            @else 
                            <a target="_blank" href="https://www.fedex.com/apps/fedextrack/?tracknumbers={{ $order->tracking }}">{{ $order->tracking }}</a></button>
                            @endif
                        </div>
                    </div>    
                </div>
                @endif
            </div>

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
