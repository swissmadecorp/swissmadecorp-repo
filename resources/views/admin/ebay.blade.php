@extends('layouts.admin-default')

@section ('content')
    <a href="ebay/listings" class="btn btn-primary">List Products</a>
    <a href="ebay/endlisting" class="btn btn-primary">Active Listings</a>
    <a href="ebay/relistitem" class="btn btn-primary">Relist Item</a>
    <!-- <edit-note></edit-note> -->
@endsection

@section('jquery')
<div id="app">
    {{-- stuff in here --}}
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
@endsection
