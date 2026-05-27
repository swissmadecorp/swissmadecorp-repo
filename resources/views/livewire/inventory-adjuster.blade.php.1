<div x-data="{ focusSearchBox() { $refs.searchbox.focus(); $refs.searchbox.select(); } }"
x-init="focusSearchBox()"
@keydown.window="if ($event.key === '/') { $event.preventDefault(); focusSearchBox(); }">
@section('main_header')
<!-- <link href="/css/dropzone.css" rel="stylesheet"> -->
<link href="/editable-select/jquery-editable-select.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css" integrity="sha512-nUqPe0+ak577sKSMThGcKJauRI7ENhKC2FQAOOmdyCYSrUh0GnwLsZNYqwilpMmplN+3nO3zso8CWUgu33BDag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@stop

@section ('footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.umd.min.js"></script>
<script src="/js/jquery.autocomplete.min.js"></script>
<script src="/editable-select/jquery-editable-select.js"></script>
<script src="/js/jquery.mask.js" type="text/javascript"></script>
@stop

    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}

    <!-- Page Header -->
    <div x-data class="bg-gray-200 dark:bg-gray-600 flex items-center p-1.5 rounded-lg">
        <div class="w-full rounded-lg shadow">
            <h1 class="uppercase tracking-wide text-3xl text-gray-500 dark:text-white">{{$pageName}}</h1>
        </div>

        <div class="relative">
        <button wire:click.prevent="refreshInventory()" type="button" class="bg-blue-700 dark:bg-blue-600 dark:focus:ring-blue-800 dark:hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium h-[3rem] hover:bg-blue-800 px-5 rounded-lg text-sm text-white">Refresh Inventory</button>
        </div>

    </div>

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


    <livewire:product-item />


    <div class="relative sm:rounded-lg" >
        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900">
                <div class="bg-gray-50 block border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400 dark:text-white focus:border-blue-500 focus:ring-blue-500 mt-1 ps-10 relative rounded-lg text-gray-900 w-2 w-96">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" x-ref="searchbox" wire:model.live.debounce.5s="search" id="table-search" class="focus:ring-0 bg-gray-50 border-0 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white h-10 p-0 rounded-lg text-gray-900 w-full" placeholder="Search for items">
                </div>
            </div>

            <!-- wire:poll.15s.visible -->
            <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400" id="products-table">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-8 py-3">Image</th>
                        <th scope="col" style="width: 40px" class="px-3 py-3">Condition</th>
                        <th scope="col" class="cursor-pointer px-3 py-3">Id</th>
                        <th scope="col" class="cursor-pointer w-[200px] px-3 py-3">Title</th>
                        <th scope="col" class="px-3 py-3">Serial</th>
                        <th scope="col" class="px-3 py-3">Cost</th>
                        <th scope="col" class="px-3 py-3">Action</th>
                    </tr>
                </head>
                <tbody>

                <?php $counter = 0 ?>

                @foreach($products as $product)
                <?php
                    $counter ++;
                    if (isset($product->images[0])) {
                        $countermage = $product->images[0];
                        $path = '/images/thumbs/'.$product->images[0]->location;
                        $path = '<a href="/new-unworn-certified-pre-owned-watches/'.$product->slug.'"class="w-20 block mx-auto" target="_blank"><img class="w-24 md:w-48 justify-center" src="'.$path.'"></a>';
                    } else {
                        $countermage="/images/no-image.jpg";
                        $path = '<a href="/new-unworn-certified-pre-owned-watches/' . $product->slug . '" class="w-20 block mx-auto" target="_blank"><img class="w-24 md:w-48 justify-center" src="'.$countermage.'"></a>';
                    }

                    $group_id = $product->group_id;
                    $groupname='';

                    if ($group_id>0)
                        $groupname = $group_id==1 ? 'jewelry' : 'bezel';

                    // $editPath = "<a class='dark:hover:text-white text-sky-600' href='/admin/products/".$product->id."/{$groupname}edit'>".$product['id'].'</a>';


                    $details = "<span class='block'>".($product['p_box']==1 ? '<i class="fa fa-box"></i>' :'') . ' ' . ($product['p_papers']==1 ? '<i class="fas fa-pager"></i>' :'').'</span>';
                    $details .= ($product['p_strap']>0) ? Strap()->get($product['p_strap']) : '';
                    $status = $product['p_status'] != 0 && $product['p_status'] <> 9 ? "<div class='bg-red-100 font-medium text-center text-xs'>".Status()->get($product['p_status'])."</div>" : '';
                    $id = $product->id;

                ?>
                <tr x-data wire:key="{{$id}}" class="odd:bg-white hover:bg-gray-100 odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                    <td class="px-1 py-1 relative w-24">
                        @if ($product->listings)
                            @if ($product->listings->listitem)
                                <a target='_blank' href="https://www.ebay.com/itm/{{$product->listings->listitem}}"><img src='/assets/ebay-logo.png' class="top-0 left-3.5 absolute h-7"></a>
                            @endif
                        @endif
                        {!! $path !!}</td>
                    <td class="relative text-center">
                        <span class="block text-center text-gray-400 text-xs">{{Conditions()->get($product['p_condition'])}}</span>
                    </td>
                    <td class="px-3 py-2 w-24">
                        <a wire:ignore.self id="editproduct" data-id="{{$id}}" class="cursor-pointer hover:text-blue-500 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 dark:hover:text-white">{{ $id }}</a>

                    </td>
                    <td class="px-3 py-2 w-full">{{$product->title}}
                    <?php if (count($product->repair)) {
                        if ($product->repair[0]->status == 0) {
                            $repair = $product->repair->first(); ?>
                            <div class="bg-red-100 font-medium text-center text-xs">
                                <button wire:click.prevent="setProductId({{$id}})" onclick="displayModalBox()" class="text-red-700">(Repair)</button>
                                <!-- <button wire:click.prevent="setProductId({{$id}})" data-modal-toggle="setproduct" data-modal-target="setproduct" class="text-red-700">(Repair)</button> -->
                            </div>
                        <?php }
                    } elseif ($product->p_status > 0) { ?>
                        <div class="bg-red-100 font-medium text-center text-xs">
                            <span class="text-red-700">{{Status()->get($product->p_status)}}</span>
                        </div>
                    <?php } ?>
                    </td>
                    <td class="px-3 py-2 w-24">{{$product->p_serial}}</td>
                    <td class="px-3 py-2 w-24"><span class="hide text-right">${{number_format($product['p_price'],0)}}</span></td>
                    <td class="px-3 py-2 w-24">
                        <button type="button" wire:click.prevent="removeItem({{$id}})" class="focus:outline-none text-white bg-red-800 hover:bg-red-600 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Remove</button>

                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>


    <div class="px-6 py-3">
   {{ $products->links('livewire.pagination') }}
   </div>

   @script
    <script>
        $(function() {
            $wire.on('input-set-focus', () => {
                $('#table-search').focus();
            });

            $('#table-search').keydown(function(event) {
                let $val = $('#table-search').val();
                if (event.key === 'Enter') {
                    products = $('#products-table tbody tr')
                        products.each(function(index, element) {
                        let cell = $(this).find('td:nth-child(3)');

                        if (cell.length === 0) {
                            console.log(`No third cell found for row at index ${index}.`);
                            return;
                        }

                        let link = cell.find('a');

                        if (link.length === 0) {
                            console.log(`No link found in the third cell for row at index ${index}.`);
                            return;
                        }

                        let linkText = link.text();
                        if (linkText === $val) {
                            $wire.$call('removeItem', linkText)
                            return false
                        }

                    });
                }
            });
        })

    </script>
@endscript

</div>