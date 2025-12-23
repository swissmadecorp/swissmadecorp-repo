@extends('layouts.admin-default')

@section("content")
    <h3>Sorry {{auth()->user()->name}}! This page does not exist.</h3>
@endsection