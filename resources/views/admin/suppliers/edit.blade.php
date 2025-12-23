@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')
    
 {{  Form::model($supplier, array('route' => array('suppliers.update', $supplier->id), 'method' => 'PATCH', 'id' => 'supplierform')) }} 
    <div class="form-group row">
        <label for="firstname-input" class="col-3 col-form-label">First Name</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty($supplier->firstname) ? $supplier->firstname : '' ?>" type="text" placeholder="Enter first name" name="firstname" id="firstname-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="lastname-input" class="col-3 col-form-label">Last Name</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty($supplier->lastname) ? $supplier->lastname : '' ?>" type="text" placeholder="Enter last name" name="lastname" id="lastname-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="company-input" class="col-3 col-form-label">Company</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty($supplier->company) ? $supplier->company : '' ?>" type="text" placeholder="Enter company" name="company" id="company-input" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="address-input" class="col-3 col-form-label">Address 1</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty($supplier->address1) ? $supplier->address1 : '' ?>" type="text" name="address" id="address-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="address2-input" class="col-3 col-form-label">Address 2</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty($supplier->address2) ? $supplier->address2 : '' ?>" type="text" placeholder="Enter address 2" name="address2" id="address2-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="phone-input" class="col-3 col-form-label">Phone</label>
        <div class="col-9 input-group">
            <input class="form-control" value="<?php echo !empty($supplier->phone) ? $supplier->phone : '' ?>" type="text" placeholder="Enter phone number" name="phone" id="phone-input" >
        </div>
    </div>    
    <div class="form-group row">
        <label for="country-input" class="col-3 col-form-label">Country</label>
        <div class="col-9">
            @inject('countries','App\Libs\Countries')
            <?php echo $countries->getAllCountries($supplier->country) ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="state-input" class="col-3 col-form-label">State</label>
        <div class="col-9">
            <?php echo $countries->getAllStates($supplier->state) ?>
        </div>
    </div>
    <div class="form-group row">
        <label for="city-input" class="col-3 col-form-label">City</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty($supplier->city) ? $supplier->city : '' ?>" type="text" placeholder="Enter city" name="city" id="city-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="zip-input" class="col-3 col-form-label">Zip Code</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty($supplier->zip) ? $supplier->zip : '' ?>" type="text" placeholder="Enter zip code" name="zipcode" id="zipcode-input">
        </div>
    </div>
    <div class="form-group row">
        <label for="contact-input" class="col-3 col-form-label">Contact</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty($supplier->contact) ? $supplier->contact : '' ?>" type="text" placeholder="Enter contact name" name="contact" id="contact-input" >
        </div>
    </div>    
    <div class="form-group row">
        <label for="email-input" class="col-3 col-form-label">Email</label>
        <div class="col-9">
            <input class="form-control" value="<?php echo !empty($supplier->email) ? $supplier->email : '' ?>" type="text" placeholder="Enter email address" name="email" id="email-input" >
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    

</div>

    @include('admin.errors')
{{  Form::close() }}  
@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection