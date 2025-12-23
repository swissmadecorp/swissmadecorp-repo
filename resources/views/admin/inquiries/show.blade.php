@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<p><b>Inquiry Date: {{ $inquiry->created_at->format('m/d/Y') }}</b></p>

<div class="p-1"  style="clear: both">
    <div class="form-group row">
        <label class="col-3 col-form-label">Contact Name</label>
        <div class="col-9 input-group">
           <span class="form-control">{{$inquiry->contact_name}}</span>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-3 col-form-label">Company Name</label>
        <div class="col-9 input-group">
            <span class="form-control">{{$inquiry->company_name}}</span>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-3 col-form-label">Email</label>
        <div class="col-9 input-group">
            <span class="form-control">{{$inquiry->email}}</span>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-3 col-form-label">Phone</label>
        <div class="col-9 input-group">
            <span class="form-control">{{$inquiry->phone}}</span>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-3 col-form-label">Notes</label>
        <div class="col-9 input-group">
            <span style="height: 150px;display:block" class="form-control">{{$inquiry->notes}}</span>
        </div>
    </div>


    <table class="table table-striped table-bordered hover repair-products">
    <thead>
        <tr>
            <th>Image</th>
            <th>Product Name</th>
            <th>Serial#</th>
            <th>Retail</th>
            <th>Cost</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td  style="width:80px">
                @if (@count($product->images))
                    <?php $image = $product->images->first() ?>
                    <a href="/{{$product->slug}}"><img style="width: 100px" title="{{ $image->title }}" alt="{{ $image->title }}" src="/images/thumbs/{{$image->location }}"></a>
                @else
                    <a href="/{{$product->slug}}"><img style="width: 100px" title="{{ $product->title }}" alt="{{ $product->title }}" src="/images/no-image.jpg"></a>
                @endif
            </td>
            <td>{{ $product->title }}</td>
            <td style="text-align: right">{{ $product->p_serial }}</td>
            <td style="text-align: right">${{ number_format($product->p_retail,2) }}</td>
            <td style="text-align: right">${{ number_format($product->p_price,2) }}</td>
        </tr>
    </tbody>

</table>

    <div class="float-right mr-3">
        <button type="submit" onclick="window.history.go(-1); return false;" class="btn btn-danger update">Go Back</button>
    </div>

</div>

@endsection

@section ('footer')
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.15/fh-3.1.2/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/select/1.2.2/js/dataTables.select.min.js"></script>
@endsection      