@extends('layouts.default')

@section('title', 'Cart')

@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">
@endsection

@section ('content')
    
    <div class="row d-flex justify-content-center">
        <div class="col-md-12 col-xl-10 cart-page">
            <h3>Checkout</h3>
            <hr class="divider_bg mb-3">
            
            @if($products)
            <div class="stepper-wrapper">
                <div class="stepper-item active">
                    <div class="step-counter">1</div>
                    <div class="step-name">Shopping Cart</div>
                </div>
                <div class="stepper-item">
                    <div class="step-counter">2</div>
                    <div class="step-name">Shipping Information</div>
                </div>
                <div class="stepper-item">
                    <div class="step-counter">3</div>
                    <div class="step-name">Payment Information</div>
                </div>
                <div class="stepper-item">
                    <div class="step-counter">4</div>
                    <div class="step-name">Order Confirmation</div>
                </div>
            </div>

            <h3 style="color:red">{{ Session::get('message') }}</h3>

            <div class="cartContainer"> 

                @include ('carttemplate')
                <pre id="confirm"><code class="json"></code></pre>
                <div id="result"></div>
                <!-- <pre id="authorize"><div class="text-center"></div></pre> -->

                <div id="addressBookWidgetDiv" style="width:330px; height:240px; display:none"></div>
                <div id="walletWidgetDiv" style="width:330px; height:240px; display:none"></div>
                <div id='amazonpayment'>
                    <button id="place-order" class="btn btn-sm btn-secondary">Finalize Order</button>
                    <button class="start-over btn btn-sm btn-secondary">Logout from Amazon</button>
                    <div id="ajax-loader" style="display:none;"><img src="images/ajax-loader.gif" /></div>
                </div>
                
            </div>

            <div style="position: relative;">
                <div id="checkoutRegular"></div>
                <div class="pt-3 promo form-group" style="border: 1px solid #e0d7d7;padding: 15px;">
                    <label for="promo">Promo Code:</label>
                    
                    <input type="text" class="w-25" id="promo" /><button aria-label="Apply discount">Apply</button>
                </div>

                <div class="pt-2 container">
                    <div class="row">
                        <div class="col-sm-3">
                        <form method="POST" action="cart/checkout" accept-charset="UTF-8" autocomplete="off">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">Checkout
                            <i class="fas fa-angle-double-right"></i>
                            </button>
                        </form>
                        </div>
                        <div class="col-sm-6 text-center">
                            - OR -
                        </div>
                        <div class="col-sm-3">
                            <!-- <form action="paypal/checkout" method="post">
                                @csrf
                                <button class="paypalButton float-right"><img src="https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif" alt=""></button>
                            </form> -->
                            <div id="AmazonPayButton"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @else
    
        <h4 id="empty-cart">Your cart is empty.</h4>
    </div>
    @endif

    <script src="{{-- mix('mix/js/bootstrap.js') --}}"></script>
    <script src="{{-- mix('mix/js/app.js') --}}"></script>

    <script type='text/javascript'>

        var amazonClientId = 'amzn1.application-oa2-client.0301a0bbe0e848bbaa6edb4b8bbe5d91';
	    var amazonSellerId = 'A2D3CDILGBV6ZZ';
            window.onAmazonLoginReady = function () {
                try {
                    amazon.Login.setClientId(amazonClientId);
                    amazon.Login.setUseCookie(true);
                } catch (err) {
                    alert(err);
                }
            };


            @if (isset($_GET["access_token"]))
            document.getElementById("addressBookWidgetDiv").style.display = "inline-block";
            document.getElementById("walletWidgetDiv").style.display = "inline-block";
            document.getElementById("amazonpayment").style.display = "block";
            document.getElementById("checkoutRegular").style.display = "block";
            var orderReferenceId='';

            window.onAmazonPaymentsReady = function () {
                new OffAmazonPayments.Widgets.AddressBook({
                    sellerId: amazonSellerId,
                    onOrderReferenceCreate: function (orderReference) {

                        /* Make a call to the back-end that will SetOrderReferenceDetails
                         * and GetOrderReferenceDetails. This will set the order total
                         * to 19.95 and return order reference details.
                         */
                        
                        var access_token = '<?= $_GET["access_token"];?>';
                        orderReferenceId = orderReference.getAmazonOrderReferenceId()
                        $.post("{{route('amazon.details')}}", {
                            orderReferenceId: orderReferenceId,
                            accessToken: access_token
                        }).done(function (data) {
                            try {
                                var json = data[0]; //JSON.parse(data[0]);
                                var values = data[1];
                                destination = json.GetOrderReferenceDetailsResult.OrderReferenceDetails.Destination.PhysicalDestination;
                                $(document).ready(function() {
                                    $('.taxfield').text(values[1])
                                    $('.freightfield').text(values[2])
                                    $('.totalfield').text(values[0])
                                })
                            } catch (err) {
                            }
                            //$("#get_details_response").html(data);
                        });
                    },
                    onAddressSelect: function (orderReference) {
                        var access_token = '<?= $_GET["access_token"];?>';
                        if (orderReferenceId) {
                            $.post("{{route('amazon.details')}}", {
                                orderReferenceId: orderReferenceId,
                                accessToken: access_token
                            }).done(function (data) {
                                try {
                                    var json = data[0]; //JSON.parse(data[0]);
                                    var values = data[1];
                                    destination = json.GetOrderReferenceDetailsResult.OrderReferenceDetails.Destination.PhysicalDestination;
                                    $(document).ready(function() {
                                        $('.taxfield').text(values[1])
                                        $('.freightfield').text(values[2])
                                        $('.totalfield').text(values[0])
                                    })
                                } catch (err) {
                                }
                                //$("#get_details_response").html(data);
                            });
                        }
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onError: function (error) {
                        // your error handling code
                        alert("AddressBook Widget error: " + error.getErrorCode() + ' - ' + error.getErrorMessage());
                    }
                }).bind("addressBookWidgetDiv");

                new OffAmazonPayments.Widgets.Wallet({
                    sellerId: amazonSellerId,
                    onPaymentSelect: function (orderReference) {
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onError: function (error) {
                        // your error handling code
                        alert("Wallet Widget error: " + error.getErrorCode() + ' - ' + error.getErrorMessage());
                    }
                }).bind("walletWidgetDiv");


                $(document).ready(function() {
                    $('.start-over').on('click', function() {
                        amazon.Login.logout();
                        $.post("{{ route('amazon.clear.session')}}", {})
                            .done(function(data) {
                                //alert(data);
                            }
                        )
                        document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                        window.location = '/cart';
                    });

                    $('#place-order').on('click', function() {
                        var authorizeResponse;
                        var confirmResponse;
                        $.post("{{ route('amazon.process.payment')}}", {}).done(function(data) {
                            try {
                                var obj = jQuery.parseJSON(data);
                                
                                $.each(obj, function(key, value) {
                                    if (key == 'confirm') {
                                        confirmResponse = value;
                                        // var str = JSON.stringify(value, null, 2);
                                        // $("#confirm").html(str);
                                    } else if (key == 'authorize') {
                                        authorizeResponse = value;
                                        // var str = JSON.stringify(value, null, 2);
                                        // $("#authorize").html(str);
                                    }
                                });

                                // Normally, you would do these decline checks on the back-end instead of in the browser

                                if (confirmResponse) {
                                    if (confirmResponse.Error) {
                                        $("#result").html("<font color='red'><strong>Confirm API call failed (see reason above)</strong></font>");
                                    }
                                }

                                if (authorizeResponse) {
                                    if (authorizeResponse.AuthorizeResult.AuthorizationDetails.AuthorizationStatus.State === "Declined") {
                                        $("#result").html("<font color='red'><strong>The authorization was Declined with Reason Code "
                                            + authorizeResponse.AuthorizeResult.AuthorizationDetails.AuthorizationStatus.ReasonCode + "</strong></font>");
                                    }
                                } else if (authorizeResponse.AuthorizeResult.AuthorizationDetails.AuthorizationStatus.State == "Closed") {
                                    windows.location.href = '{{ route ("finilize.amazon.payment")}}';
                                }

                            } catch (err) {
                                $("#confirm").html(data);
                                console.log(data);
                                console.log(err);
                            }

                            window.location.href = '{{ route ("finilize.amazon.payment")}}?order_id='+obj.order_id;
                        });

                        //$(this).hide();
                        //$('#ajax-loader').show();
                    });
                });

            };
            @else
            window.onAmazonPaymentsReady = function () {
                var authRequest;
                OffAmazonPayments.Button("AmazonPayButton", amazonClientId, {
                    type: "PwA",       // PwA, Pay, A, LwA, Login
                    color: "DarkGray", // Gold, LightGray, DarkGray
                    size: "small",    // small, medium, large, x-large
                    language: "en-EN", // for Europe/UK regions only: en-GB, de-DE, fr-FR, it-IT, es-ES
                    authorization: function() {
                        loginOptions = { scope: "profile postal_code payments:widget payments:shipping_address", popup: true };
                        authRequest = amazon.Login.authorize(loginOptions,'/cart?amazon_id='+amazonClientId);
                    },
                    onError: function(error) {
                        // something bad happened
                    }
                });

            };
            @endif
        </script>
        <script async="async" type='text/javascript' src="https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js"></script>
@endsection
