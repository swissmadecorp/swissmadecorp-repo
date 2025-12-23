@extends('layouts.default')

@section('title', 'Cart')

@section ('header')
<link href="/fancybox/jquery.fancybox.min.css" rel="stylesheet">

<style>
    .form-group {margin-bottom: .2rem}
</style>
@endsection

@section ('content')

<form method="POST" action="payment" autocomplete="off">
    @csrf
    <input type="hidden" value="<?php echo $_SERVER['REMOTE_ADDR'] ?>" name="ip">
    
    @inject('countries','App\Libs\Countries')
    @if ($products)
        <div class="row d-flex justify-content-center">
        <div class="col-md-12 col-xl-10 cart-page">
        <h3>Billing Information</h3>
        <hr class="divider_bg mb-3">
        <div class="stepper-wrapper">
            <div class="stepper-item completed">
                <div class="step-counter">1</div>
                <div class="step-name">Shopping Cart</div>
            </div>
            <div class="stepper-item active">
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

            <div class="col-md-5 float-left">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="text" value="{{ $cookie_cart ? $cookie_cart['email'] : '' }}" class="form-control" name="email" id="email" autofocus required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="b_firstname">First Name</label>
                        <input type="text" value="{{ $cookie_cart ? $cookie_cart['b_firstname'] : '' }}" class="form-control" name="b_firstname" id="b_firstname" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="b_lastname">Last Name</label>
                        <input type="text" class="form-control" name="b_lastname" value="{{ $cookie_cart ? $cookie_cart['b_lastname'] : '' }}" id="b_lastname" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="b_company">Company</label>
                    <input type="text" class="form-control" value="{{ $cookie_cart ? $cookie_cart['b_company'] : '' }}" name="b_company"  id="b_company">
                </div>                    
                <div class="form-group">
                    <label for="b_address1">Address 1</label>
                    <input type="text" class="form-control" name="b_address1" value="{{ $cookie_cart ? $cookie_cart['b_address1'] : '' }}" required id="b_address1">
                </div>
                <div class="form-group">
                    <label for="b_address2">Address 2</label>
                    <input type="text" class="form-control" name="b_address2" value="{{ $cookie_cart ? $cookie_cart['b_address2'] : '' }}" id="b_address2">
                </div>
                <div class="form-group">
                    <label for="b_phone">Phone</label>
                    <input type="text" class="form-control" name="b_phone" value="{{ $cookie_cart ? $cookie_cart['b_phone'] : '' }}" required id="b_phone">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="b_city">City</label>
                        <input type="text" class="form-control" name="b_city" value="{{ $cookie_cart ? $cookie_cart['b_city'] : '' }}" id="b_city">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="b_state-input">State</label>
                        <?php echo $countries->getAllStates($cookie_cart ? $cookie_cart['b_state'] : '')  ?>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="b_zip">Zip</label>
                        <input type="text" class="form-control" name="b_zip" value="{{ $cookie_cart ? $cookie_cart['b_zip'] : '' }}" required id="b_zip">
                    </div>
                </div>

                <div class="form-group">
                    <label for="b_country-input">Country</label>
                    <?php echo $countries->getAllCountries(0) ?>
                </div>
            </div>

            <div class="col-md-7 float-left">
                <div class="mt-4">
                    @include ('carttemplate')
                </div>
                
                <div class="float-right clearfix mt-3">
                    <button type="submit" class="btn btn-secondary btn-sm">Continue to payment
                    <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        </div>
        </div>
        @else
        <h3>Your cart is empty</h3>
        @endif
    
    </form>

    <script src="{{-- mix('public/mix/js/bootstrap.js') -- }}"></script>
    <script src="{{-- mix('public/mix/js/app.js') --}}"></script>
@endsection

@section ('jquery')

<script>
    
    $(document).ready( function() {
        $('#b_country-input,#s_country-input').change( function() {
            _this = $(this);
            $.get("{{ route('get.state.from.country')}}",{id: $(_this).val()})
            .done (function (data) {
                if ($(_this).attr('id') == 'b_country-input')
                    $('#b_state-input').html(data);
                else $('#s_state-input').html(data);
            })
        })
    }) 

</script>
@endsection