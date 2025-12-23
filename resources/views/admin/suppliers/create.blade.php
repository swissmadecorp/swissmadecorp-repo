@extends('layouts.admin-default')

@section ('content')

{{  Form::open(array('action'=>'SuppliersController@store')) }}  
    <div class="form-group row">
        <label for="firstname-input" class="col-3 col-form-label">First Name</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('firstname')) ? old('firstname') : '' ?>" type="text" name="firstname" id="firstname-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="lastname-input" class="col-3 col-form-label">Last Name</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('lastname')) ? old('lastname') : '' ?>" type="text" name="lastname" id="lastname-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="company-input" class="col-3 col-form-label">Company</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('company')) ? old('company') : '' ?>" type="text" name="company" id="company-input" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="address-input" class="col-3 col-form-label">Address 1</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('address')) ? old('address') : '' ?>" type="text" name="address" id="address-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="address2-input" class="col-3 col-form-label">Address 2</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('address2')) ? old('address2') : '' ?>" type="text"  name="address2" id="address2-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="phone-input" class="col-3 col-form-label">Phone</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty(old('phone')) ? old('phone') : '' ?>" type="text"  name="phone" id="phone-input">
        </div>
    </div>    
    <div class="form-group row">
        <label for="country-input" class="col-3 col-form-label">Country</label>
        <div class="col-9">
            @inject('countries','App\Libs\Countries')
            <?php echo $countries->getAllCountries() ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="state-input" class="col-3 col-form-label">State</label>
        <div class="col-9">
            <?php echo $countries->getAllStates() ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="city-input" class="col-3 col-form-label">City</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('city')) ? old('city') : '' ?>" type="text" name="city" id="city-input" >
        </div>
    </div>
    <div class="form-group row">
        <label for="zip-input" class="col-3 col-form-label">Zip Code</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('zipcode')) ? old('zipcode') : '' ?>" type="text"  name="zipcode" id="zipcode-input" >
        </div>
    </div>
    <div class="form-group row">
        <label for="contact-input" class="col-3 col-form-label">Contact</label>
        <div class="col-9">
            <input class="form-control" placeholder="Enter contact name" value="<?php echo !empty(old('contact')) ? old('contact') : '' ?>" type="text" name="contact" id="contact-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="email-input" class="col-3 col-form-label">Email</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty(old('email')) ? old('email') : '' ?>" type="text" name="email" id="email-input">
        </div>
    </div>

    <button type="submit" class="btn btn-primary uploadPhoto">Save</button>

    @include('admin.errors')
{{  Form::close() }}  
@endsection

@section ('jquery')
<script>
    $(document).ready( function() {
        @include ('admin.countrystate')
    })
</script>
@endsection;