@extends('layouts.admin-default')

@section ('header')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/> 
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/select/1.2.2/css/select.dataTables.min.css"/> 
@endsection

@section ('content')

<div id="reports">
    <ul id="report-items">
        <li><a href="reports/by/product" class="btn btn-primary">Report By Products</a></li>
        <li><a href="reports/by/company" class="btn btn-primary">Report By Company</a></li>
        <li><a href="reports/by/unpaid" class="btn btn-primary">Report By Unpaid</a></li>
        <li><a href="reports/by/memo" class="btn btn-primary">Report By Memo</a></li>
        <li><a href="reports/by/supplier" class="btn btn-primary">Report By Suppliers</a></li>
    </ul>
</div>

<!-- <a href="reports/by/paid" class="btn btn-primary">Report By Paid</a> -->


@endsection
