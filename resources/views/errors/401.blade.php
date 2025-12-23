@extends ("layouts.default-new")

@section('content')
    <div class='flex justify-center'>
        <h2>
            <div wire:ignore.self="" class="flex justify-center">
                <div class="pt-10 text-2xl text-center">Error 401</div>
            </div>
            <div wire:ignore.self="" class="flex justify-center">
                <h4 class="md:p-[150px] pt-10 text-2xl text-center">
                    Oops. It seems you have landed in an authorized page!
                    <br>
                    If you think you have reached this page in error, please contact us.
                </h4>
            </h2>

    </div>

@endsection