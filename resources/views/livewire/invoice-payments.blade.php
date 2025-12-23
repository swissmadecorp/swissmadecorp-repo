<div>
@section('main_header')
<!-- <link href="/css/dropzone.css" rel="stylesheet"> -->
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@stop

@section ('footer')
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/js/jquery.mask.js" type="text/javascript"></script>
<script src="/editable-select/jquery-editable-select.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.umd.min.js"></script>
@stop
    <div x-data class="bg-gray-200 dark:bg-gray-900 flex h-[3rem] items-center p-2 rounded-lg">
        <div class="w-full inline-flex rounded-lg shadow">
            <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white">{{$pageName}}</h1>
        </div>

    </div>
    <!-- Page Header -->
    @if (session()->has('message'))
        <div id="alert-border-1" class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                @if (is_array(session('message')))
                    {{ session('message')['msg'] }}
                @else
                    {{ session('message') }}
                @endif
            </div>
            <button type="button" @click="dismiss()" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @elseif (session()->has('error'))
        <div id="alert-border-1" class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dark:text-red-400 dark:bg-gray-800 dark:border-red-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('error') }}
            </div>
            <button type="button" @click="dismiss()" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"  data-dismiss-target="#alert-border-2" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @endif

    <livewire:invoice-item />
    <!-- Main Payment page -->
    <div class="relative sm:rounded-lg" >
        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900">
            <div class="bg-gray-50 block border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400 dark:text-white focus:border-blue-500 focus:ring-blue-500 mt-1 ps-10 relative rounded-lg text-gray-900 w-2 w-96">
                <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input wire:model.live="search" type="text" id="table-search" class="focus:ring-0 bg-gray-50 border-0 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white h-10 p-0 rounded-lg text-gray-900 w-full" placeholder="Search for items">
            </div>
        </div>
            
        <!-- wire:poll.15s.visible -->
        <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-3 py-3">Company</th>
                    <th scope="col" class="text-right cursor-pointer px-3 py-3">Cost</th>
                    <th scope="col" class="text-right cursor-pointer px-3 py-3">Amount Sold.</th>
                    <th scope="col" class="text-right cursor-pointer px-3 py-3">Profit</th>
                    <th scope="col" class="text-right cursor-pointer px-3 py-3">Date</th>
                </tr>
            </head>
            <tbody>
            
            @foreach($orders as $order)
            <tr wire:key="{{$order->customer_id}}" class="odd:bg-white hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                <td class="px-3 py-3"><a wire:ignore.self data-id="{{$order->customer_id}}" class="viewpayment cursor-pointer hover:text-blue-500 px-4 py-2 text-sm text-gray-700 dark:text-sky-600 dark:hover:text-white">{{$order->company}}</a></td>
                <td class="text-right px-3 py-3">{{'$'. number_format($order->total_cost,2)}}</td>
                <td class="text-right px-3 py-3">{{'$'. number_format($order->amount,2)}}</td>
                <td class="text-right px-3 py-3">{{'$'. number_format($order->amount-$order->total_cost,2)}}</td>
                <td class="text-right px-3 py-3 w-24">{{date('m-d-Y',strtotime($order->max_date))}}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Payment Slider for an individual payment -->
    <div wire:ignore.self id="slideover-payment-container" class="fixed inset-0 w-full h-full invisible z-[51]" >
        <div wire:ignore.self id="slideover-payment-bg" class="absolute duration-500 ease-out transition-all inset-0 w-full h-full bg-gray-900 opacity-0"></div>
        <div tabindex="0" wire:ignore.self id="slideover-payment" class="absolute duration-500 ease-out transition-all h-full bg-white right-0 top-0 translate-x-full overflow-y-scroll dark:bg-gray-600" style="width: 790px">
            <div class="bg-gray-200 p-3 text-2xl text-gray-500 dark:bg-gray-700 dark:text-gray-200">Payments</div>
            <div id="slideover-payment-child" class="w-10 h-10 flex items-center shadow-sm rounded-full justify-center hover:bg-gray-300 cursor-pointer absolute top-0 right-0 m-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </div>

            <div wire:ignore class="p-3 dark:bg-gray-700" id="payment-content-conainer"></div>
        </div>
    </div>

    <div class="px-6 py-3">{{ $orders->links('livewire.pagination') }}</div>

@script
    <script> 
        $(function() {

            function Slider() {
                $('body').toggleClass('overflow-hidden')
                $('#slideover-payment-container').toggleClass('invisible')
                $('#slideover-payment-bg').toggleClass('opacity-0')
                $('#slideover-payment-bg').toggleClass('opacity-20')
                $('#slideover-payment').toggleClass('translate-x-full')
                if (!$('#slideover-payment-container').hasClass('invisible')) {
                    setTimeout(() => {
                        $('#title').focus();
                    }, "400");

                }
            }

            $('#slideover-payment-child').click(function() {
                Slider();
            })

            $(document).on('click', '.viewpayment', function() {
                id = $(this).attr('data-id');
                $wire.$call('getPayment', id);
                Slider();
            })

            $wire.on('viewPayment', msg => {
                debugger
                $('#payment-content-conainer').html(msg[0])
            })
        })
    </script>
@endscript
</div>
