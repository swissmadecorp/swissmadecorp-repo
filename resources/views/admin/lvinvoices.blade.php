@extends ("layouts.admin-new-default")

@section('title', 'Invoices')

@section ('header')

@endsection


@section ('content')
<div class="bg-gray-200 w-full rounded-lg shadow dark:bg-gray-600">
    <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white p-1.5 items-center">Invoices</h1>
</div>

@livewire('invoices')

@endsection
