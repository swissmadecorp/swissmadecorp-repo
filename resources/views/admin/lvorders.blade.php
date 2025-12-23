

@section('title', 'Orders')

@section ('header')

@section ('content')
<div class="bg-gray-200 w-full rounded-lg shadow dark:bg-gray-600">
    <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white p-1.5 items-center">Orders</h1>
</div>

@livewire('orders')

@endsection

@section ('jquery')

<script>
    $(document).ready( function() {
        $(document).on('mouseenter', 'span.hide', function () {
            $(this).css('opacity',1)
        }).on('mouseleave', 'span.hide', function () {
            $(this).css('opacity',0)
        })

        $(document).on("click", "#alert-border-1 button", function() {
            $(this).parent().slideUp("slow");;
        })
    })

</script>

@endsection