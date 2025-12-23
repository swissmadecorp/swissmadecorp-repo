@extends ("layouts.default-new")

@section('title', 'Credit Card Processor')

@section("content")
    @livewire('credit-card-processor')
@endsection

@section ('footer')
    <script src="{{ asset('/js/card.js') }}"></script>
@endsection