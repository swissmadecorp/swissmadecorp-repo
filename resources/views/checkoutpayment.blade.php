@extends('layouts.default')

@section('title', 'Cart')

@section ('header')
<link href="fancybox/jquery.fancybox.min.css" rel="stylesheet">
<style>
    .form-group {margin-bottom: .2rem}
    .table-borderless td,
        .table-borderless th {
            border: 0 !important;
            padding: 1px !important;
        }

</style>
@endsection

@section ('content')
        <form method="POST" action="{{route('cart.finalize.order')}}" id="payment" autocomplete="off">
        @csrf
        @inject('countries','App\Libs\Countries')
        
        <div class="row d-flex justify-content-center">
            <div class="col-md-12 col-xl-10 cart-page">
                <h3>Billing/Shipping Information</h3>
                <hr class="divider_bg mb-3">
                <div class="stepper-wrapper">
                    <div class="stepper-item completed">
                        <div class="step-counter">1</div>
                        <div class="step-name">Shopping Cart</div>
                    </div>
                    <div class="stepper-item completed">
                        <div class="step-counter">2</div>
                        <div class="step-name">Shipping Information</div>
                    </div>
                    <div class="stepper-item active">
                        <div class="step-counter">3</div>
                        <div class="step-name">Payment Information</div>
                    </div>
                    <div class="stepper-item">
                        <div class="step-counter">4</div>
                        <div class="step-name">Order Confirmation</div>
                    </div>
                </div>
                <table class="table table-borderless table-condensed">
                    <tr>
                        <td><h4>Billing Address</h4></td>
                        <td><h4>Shipping Address</h4></td>
                    </tr>
                    <tr>
                        <td>{{ $cookie_cart['b_company'] }}</td>
                        <td>{{ $cookie_cart['b_company'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $cookie_cart['b_firstname'] }} {{ $cookie_cart['b_lastname']}}</td>
                        <td>{{ $cookie_cart['b_firstname'] }} {{ $cookie_cart['b_lastname']}}</td>
                    </tr>
                    <tr>
                        <td>{{ $cookie_cart['b_address1'] }}</td>
                        <td>{{ $cookie_cart['b_address1'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $cookie_cart['b_city'] }}, {{ $countries->getStateByCode($cookie_cart['b_state']) }} {{ $cookie_cart['b_zip'] }}</td>
                        <td>{{ $cookie_cart['b_city'] }}, {{ $countries->getStateByCode($cookie_cart['b_state']) }} {{ $cookie_cart['b_zip'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $cookie_cart['b_phone'] }}</td>
                        <td>{{ $cookie_cart['b_phone'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ $countries->getCountry($cookie_cart['b_country']) }}</td>
                        <td>{{ $countries->getCountry($cookie_cart['b_country']) }}</td>
                    </tr>

                </table> 

                <h3>Payment Options</h3>
                <hr class="divider_bg mb-3">
                <?php 
                    $wirevalue = 1;
                    $ccvalue = 0;

                    $hasZero = !empty(array_filter($products, function($product) {
                        return $product['iswire'] == 0;
                    }));
                    
                    if ($hasZero) {
                        $wirevalue = 0; 
                        $ccvalue = 1; 
                    }
                    ?>
                <?php if (!$hasZero) {?>
                <div class="col-6">
                    <input id="cc" type="radio" name="payment_options" <?= isset($cookie_cart['payment']) && $cookie_cart['payment'] == 0 ? 'checked' : '' ?> value="{{$ccvalue==0 ? 0 : 1}}" class="css-checkbox">
                    <label for="cc" class="css-label"><strong>Credit Card</strong></label>
                </div>
                <?php } ?>
                <div class="col-6">
                    <input id="wire" type="radio" name="payment_options" value="{{$wirevalue ? 0 : 1}}" <?= isset($cookie_cart['payment']) && $cookie_cart['payment'] == 1 ? 'checked' : '' ?> class="css-checkbox">
                    @if (!$discount)
                    <label for="wire" class="css-label"><strong>Bank Wire</strong></label>
                    @endif
                </div>

                <br>
                <h3>Payment Information</h3>
                <hr class="divider_bg mb-3">

                <div class="col-md-7 float-left div-cart">
                    @include ('carttemplate')
                </div>
                
                <div class="col-md-5 float-left border pb-4 creditcard" style="background: #fbfbfb">
                    <div class="wire" <?= $cookie_cart['payment']==1 ? '' : 'style="display: none"' ?>>
                        <h4>Our Wire Transfer Information</h4>
                        <b>SWISS MADE CORP</b><br>
                        15 West 47th Street<br>
                        Suite 503<br>
                        New York, NY 10036<br><br>

                        <b>Bank of America</b><br>
                        550 5th Avenue<br>
                        New York, NY 10036<br>
                        Routing #: 021000322<br>
                        Account#: 483082594737<br>
                        US Wire Code: 026009593<br>
                        International Swift Code (IN US DOLLARS): BOFAUS3N
                    </div>
                    <div class="ccard" <?= $cookie_cart['payment']==1 ? 'style="display: none"' : '' ?>>
                        <div class="card-wrapper"></div>
                        <div class="card-credit-card"><img src="/images/creditcards.png" alt=""></div>
                        <div class="form-group">
                            <label for="nameoncard">Name on Card</label>
                            <input type="text" name="name" class="form-control" type="text" id="nameoncard" autofocus required>
                        </div>
                        <div class="form-group">
                            <label for="cardnumber">Card Number</label>
                            <input type="text" class="form-control" name="number" type="tel" id="cardnumber" placeholder="•••• •••• •••• ••••" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="expiry">Expiration date</label>
                                <input type="text" class="form-control" type="tel" placeholder="mm/yy" aria-label="Expiration date, month and year" maxlength="7" name="expiry" id="expiry" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="securitycode">Security code</label>
                                <input type="text" class="form-control" placeholder="cvc" type="number" id="securitycode" name="cvc" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="float-right mt-4">
                    <button type="submit" class="btn btn-secondary btn-sm">Finalize your order
                    <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div> 
        </div>
    </form>

@endsection

@section ('jquery')
<script>
    
    $(document).ready( function() {
        $('#b_country-input,#b_country-input').change( function() {
            _this = $(this);
            $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
            .done (function (data) {
                if ($(_this).attr('id') == 'b_country-input')
                    $('#b_state-input').html(data);
                else $('#b_state-input').html(data);
            })
        })
    
        if ($('.ccard').is(':visible')) {
            $(".ccard :input").attr("disabled", false);
        } else {
            $(".ccard :input").attr("disabled", true);
        }
        
        $('.css-checkbox').change( function(e) {
            if ($(this).val() == 1) {
                $('input','.ccard').each(function () {
                    $(this).prop('required',false)
                })
                $('.ccard').hide();
                $.ajax ( {
                    url: "{{ route('wire.payment') }}",
                    data: {payment: 1},
                    success: function(response) {
                        $('.div-cart').html(response);
                    }
                })
                $('.wire').show();
                //$('.cart').removeClass('col-md-7').addClass('col-md-12')
            } else {
                $('.wire').hide();
                $('input','.ccard').each(function () {
                    $(this).prop('required',true)
                })
                $.ajax ( {
                    url: "{{ route('wire.payment') }}",
                    data: {payment: 0},
                    success: function(response) {
                        $('.div-cart').html(response);
                    }
                })
                $('.ccard').show();
                //$('.cart').removeClass('col-md-12').addClass('col-md-7')
            }
        })

        new Card({
            form: document.getElementById('payment'),
            container: '.card-wrapper',
            width: 250,
            messages: {
                validDate: 'expire\ndate',
                monthYear: 'mm/yy'
            }

        });
    

    }) 

</script>
@endsection

@section ('footer')
    <script src="{{ asset('/js/card.js') }}"></script>
@endsection

