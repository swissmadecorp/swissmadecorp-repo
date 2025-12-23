@extends ("layouts.admin-new-default")

@section('title', 'Products')

@section ('main_header')
<link href="/multiselect/chosen.min.css" rel="stylesheet">
@stop

@section ('content')

@livewire('reminders') 

@stop

@section ('footer')
    <script src="/multiselect/chosen.jquery.js"></script>
@stop

@section ('jquery')
<script>
    
    $(document).ready( function() {
        $(document).on('mouseenter', 'span.hide', function () {
            $(this).css('opacity',1)
        }).on('mouseleave', 'span.hide', function () {
            $(this).css('opacity',0)
        })
    })

</script>

@endsection