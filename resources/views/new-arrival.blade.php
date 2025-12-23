@extends('layouts.default-new')

@section('title', isset($title) ? $title : 'Brand new, pre-owned, luxury, causal, and dress watches for men and women')

@section ('content')

<div class="pt-2">
    <div class="flex justify-center">
        <div class="container">
            <h4 class="text-3xl uppercase">New Arrival</h4>
            <hr>
            @livewire('watches', ['isNewArrivalPage' => 'new-arrival'])
        </div>
    </div>
</div>
    
@endsection

@section ("canonicallink")
    @if (isset($product) && isset($product->categories->category_name))
        <link rel="canonical" href="{{ config('app.url').$product->categories->id }}" />
    @endif
@endsection