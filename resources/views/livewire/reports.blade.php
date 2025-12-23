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

    <div class="bg-gray-200 w-full rounded-lg shadow dark:bg-gray-600">
        <div class="w-full inline-flex rounded-lg shadow">
        <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white p-1.5 items-center">{{$pageName}}</h1>
        </div>
    </div>
    
    <livewire:invoice-item />

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
                    <th scope="col" class="cursor-pointer px-3 py-3" wire:click="doSort('order_id')">
                        <x-product-dataitem :sortBy="$sortBy" :sortDirection="$sortDirection" columnName="order_id" displayName="Invoice"/>
                    </th>
                    <th scope="col" class="text-center cursor-pointer px-3 py-3">Prod&nbsp;Id</th>
                    <th scope="col" class="px-3 py-3">Product Name</th>
                    <th scope="col" class="px-3 py-3">Serial</th>
                    <th scope="col" class="cursor-pointer px-3 py-3" wire:click="doSort('company')">
                        <x-product-dataitem :sortBy="$sortBy" :sortDirection="$sortDirection" columnName="company" displayName="Company" />
                    </th>
                    <th scope="col" class="cursor-pointer px-3 py-3" wire:click="doSort('max_date')">
                        <x-product-dataitem :sortBy="$sortBy" :sortDirection="$sortDirection" columnName="max_date" displayName="Date"/>
                    </th>
                </tr>
            </head>
            <tbody>
            
            @if (!empty($products))
                @foreach($products as $product)
                <?php $id = $product->order_id ?>
                <tr class="odd:bg-white hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                    <td class="px-3 py-3">
                        <a href="#" @click.prevent="$dispatch('load-invoice', { id: <?= $id ?> })" data-id="<?= $id ?>" class="editinvoice cursor-pointer dark:hover:text-white text-sky-600"><?= $id ?></a>
                    </td>
                    <td class="text-center px-3 py-3">{{ $product->product_id }}</td>
                    <td class="px-3 py-3">{{ $product->title }}</td>
                    <td class="px-3 py-3">{{ $product->p_serial }}</td>
                    <td class="px-3 py-3">{{ $product->company }}</td>
                    <td class="px-3 py-3 w-24">{{date('m-d-Y',strtotime($product->max_date))}}</td>
                </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>

    @if (!empty($products))
        <div class="px-6 py-3">
            {{ $products->links('livewire.pagination') }}
        </div>
   @endif
</div>
