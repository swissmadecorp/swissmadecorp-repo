@extends ("layouts.admin-new-default")

@section('title', 'The Show')

@section ('header')

@endsection


@section ('content')

@livewire('c-the-show') 

@endsection

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