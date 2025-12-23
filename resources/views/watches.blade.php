@extends('layouts.default-new')

@section ('content')

@livewire('watches', ['isNewArrivalPage' => 'watch-products'])
    
@endsection