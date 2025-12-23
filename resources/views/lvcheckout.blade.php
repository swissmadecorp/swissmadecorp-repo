@extends ("layouts.default-new")

@section('title', 'Cart')

@section("content")
    @livewire('checkout') 
@endsection

@section ('footer')
    <script src="{{ asset('/js/card.js') }}"></script>
@endsection