<div x-data="{ isSliderVisible: false,
        selectedRow: null,
        focusSearchBox() {
            if (!this.isSliderVisible) {
                $refs.searchbox.focus();
                $refs.searchbox.select();
            }
        }
            }"
x-init="focusSearchBox()"
@keydown.window="
if ($event.key === '=') {

            if ($refs.searchbox !== document.activeElement && !isSliderVisible) {
                $event.preventDefault();
                focusSearchBox();
            }
        }">
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

    @livewire('messages', ['loggedInUser' => auth()->id()])
    <!-- Page Header -->
    <div x-data class="absolute dark:bg-gray-600 p-1.5 right-8 rounded-lg top-21">
                <div class="relative">
            <button id="actions" data-dropdown-toggle="dropdown" class="mt-1 mr-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">Actions<svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                </svg>
            </button>

            <!-- Dropdown menu -->

            <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                <ul class="absolute top-0 left-0 bg-gray-200 py-2 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200" aria-labelledby="dropdownglobalpricerButton">
                    <li>
                        <button id="global-price-modal" data-modal-target="globalprice" data-modal-toggle="globalprice" type="button" class="text-left block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full">Set Global Prices</button>
                    </li>
                    <li>
                        <button id="exportproducts-modal" data-modal-target="exportproducts" data-modal-toggle="exportproducts" type="button" class="text-left block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full">Export Products</button>
                    </li>
                    <li>
                        <button id="importfromexcel-modal" data-modal-target="importfromexcel" data-modal-toggle="importfromexcel" type="button" class="text-left block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white w-full">Import from Excel</button>
                    </li>
                </ul>

            </div>
        </div>
        <livewire:gs-global-prices />
    </div>

    <div wire:ignore.self id="importfromexcel" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Import XLS File
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="importfromexcel">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <form wire:submit.prevent="doImport" class="p-4 md:p-5">
                    <input type="file" wire:model.live="importFile" class="mb-4">

                    <button type="submit"
                            wire:target="doImport"
                            wire:loading.attr="disabled"
                            @if(is_null($importFile)) disabled @endif
                            class="editinvoice bg-blue-600 cursor-pointer disabled:bg-gray-400 disabled:cursor-not-allowed duration-150 ease-in-out focus:outline-none focus:ring-4 focus:ring-blue-300 font-semibold hover:bg-blue-700 px-3 py-2 rounded-lg shadow-md text-lg text-white transition">

                        <span wire:loading.remove wire:target="doImport">
                            @if(is_null($importFile)) Select File to Enable @else 🚀 Start Import @endif
                        </span>

                        <span wire:loading wire:target="doImport">
                            ⏳ Processing...
                        </span>
                    </button>

                    <div wire:loading.delay wire:target="importFile" class="mt-2 text-sm text-blue-600">
                        File is uploading, please wait...
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self id="exportproducts" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Export Products
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="exportproducts">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form class="p-4 md:p-5">
                    <div class="grid gap-4 mb-4 grid-cols-4">
                        <div class="flex items-center">
                            <input checked id="checkbox-company" wire:model="exportSelections.1" type="checkbox" value="company" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-company" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Company</label>
                        </div>

                        <div class="flex items-center">
                            <input id="checkbox-serial" wire:model="exportSelections.2" type="checkbox" value="serial" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-serial" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Serial #</label>
                        </div>

                        <div class="flex items-center">
                            <input id="checkbox-cost" wire:model="exportSelections.3" type="checkbox" value="cost" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-cost" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cost</label>
                        </div>

                        <div class="flex items-center">
                            <input id="checkbox-notes" wire:model="exportSelections.4" type="checkbox" value="notes" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-notes" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Notes</label>
                        </div>
                    </div>
                    <button data-modal-hide="exportproducts"  wire:click.prevent="doExport()" type="button" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Export
                    </button>
                </form>
            </div>
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
    <livewire:invoice-item />

    <div>

    </div>

    <div class="sm:rounded-lg" >
        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 p-2 bg-white dark:bg-gray-900">
                <div>
                    @role('superadmin|administrator')
                    <button id="dropdownActionButton" data-dropdown-toggle="dropdownAction" class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700" type="button">
                        <span class="sr-only">Show</span>
                        Create New
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    @endrole
                    <!-- Dropdown menu -->
                    <div id="dropdownAction" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-800 dark:divide-gray-600">
                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200 " aria-labelledby="dropdownActionButton">
                            <li>
                                <a @click="isSliderVisible = !isSliderVisible" class="newproduct text-gray-900 cursor-pointer block px-4 py-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Watch</a>
                            </li>
                            <li>
                                <a @click="isSliderVisible = !isSliderVisible" class="newproduct text-gray-900 cursor-pointer block px-4 py-2 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Jewelry</a>
                            </li>
                            <li>
                                <a href="#" class="text-gray-900 cursor-pointer block px-4 py-2 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">Bezel</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-gray-50 block border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-500 dark:focus:ring-blue-500 dark:placeholder-gray-400 dark:text-white focus:border-blue-500 focus:ring-blue-500 mt-1 ps-10 relative rounded-lg text-gray-900 w-2 w-96">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" x-ref="searchbox" wire:model.live.debounce.5s="search" id="table-search" class="focus:ring-0 bg-gray-50 border-0 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white h-10 p-0 rounded-lg text-gray-900 w-52" placeholder="Search for items">
                    <!-- wire:change="$event.target.value" -->
                    <select wire:model.live="status" class="absolute bg-gray-50 block border- border-0 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white focus:ring-0 p-2 right-0 text-gray-900 text-sm" style="top: 1px;top: 1px;border-left: 1px solid #cdcccc;">
                        <?php $stats = [6,8,10]?>
                        @foreach (Status() as $key => $status)
                            @if (!in_array($key,$stats))
                            <option <?php echo !empty($product->p_status) && $product->p_status==$key ? 'selected' : '' ?> value="{{ $key }}">{{ $status }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
        </div>

        <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r dark:border-gray-600">
                <div class="flex items-center ps-3">
                    <input id="radio-onhand1" wire:click="$set('onhand',1)" type="radio" <?= ($onhand==1) ? 'checked' : "" ?> name="list-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label for="radio-onhand1" class="py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Display On-Hand </label>
                </div>
            </li>
            <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r dark:border-gray-600">
                <div class="flex items-center ps-3">
                    <input id="radio-onhand2" wire:click="$set('onhand',0)" <?= ($onhand==0) ? 'checked' : "" ?> type="radio" name="list-radio" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label for="radio-onhand2" class="py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Display Not On-Hand</label>
                </div>
            </li>
        </ul>

        <livewire:create-repair>

                <!-- wire:poll.15s.visible -->
            <!-- Popup Menu (Hidden Initially) -->
        <div id="popup-menu" class="hidden z-50 absolute bg-gray-200 dark:bg-gray-800 shadow-lg rounded-lg border w-44 border border-gray-300">
            <ul class="divide-gray-300">
                <li class="menu-item print cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Print Tag</li>
                <!-- <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white print">Print Label</li> -->
                @role('superadmin|administrator')
                <li data-duplicate="1" class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white editproduct">Duplicate</li>
                <li wire:ignore.self class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white editinvoice">Make Invoice</li>
                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white ebay">Submit to Ebay</li>
                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white endebay">End on Ebay</li>
                <li wire:confirm = "Are you sure you want to return this item back to vendor?" class="menu-item border-t border-gray-300 cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white return">Return to Vendor</li>
                <li wire:confirm = "Are you sure you want to delete this item?" class="menu-item border-t border-gray-300 cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white deleteitem">Delete Item</li>
                @endrole
            </ul>
        </div>

        <div class="overflow-x-auto relative ">
            <table class="w-full text-sm text-left rtl:text-right dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" style="width: 40px" class="px-3 py-3">
                            <input wire:model.live="selectAll" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        </th>
                        <th scope="col" style="width: 40px" class="px-3 py-3">Action</th>
                        <th scope="col" class="px-8 py-3">Image</th>
                        <th scope="col" class="cursor-pointer px-3 py-3" wire:click="doSort('id')">
                            <x-product-dataitem :sortBy="$sortBy" :sortDirection="$sortDirection" columnName="id" displayName="Id" />

                        </th>
                        <th scope="col" class="cursor-pointer w-[200px] px-3 py-3" wire:click="doSort('title')">
                        <x-product-dataitem :sortBy="$sortBy" :sortDirection="$sortDirection" columnName="title" displayName="Title" />
                        </th>
                        <th scope="col" class="px-3 py-3">Serial</th>
                        <th scope="col" class="px-3 py-3">Cost</th>
                        <th scope="col" class="px-3 py-3" wire:click="doSort('p_newprice')" >
                        <x-product-dataitem :sortBy="$sortBy" :sortDirection="$sortDirection" columnName="p_newprice" displayName="Price" />
                        </th>
                        <th scope="col" class="px-3 py-3">Retail</th>
                        <th scope="col" class="px-3 py-3">Qty</th>
                        <th scope="col" class="px-3 py-3">Details</th>
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
                        $path = '<a href="/product-details/'.$product->slug.'"class="w-20 block mx-auto" target="_blank"><img class="w-24 md:w-48 justify-center" src="'.$path.'"></a>';
                    } else {
                        $countermage="/images/no-image.jpg";
                        $path = '<a href="/product-details/' . $product->slug . '" class="w-20 block mx-auto" target="_blank"><img class="w-24 md:w-48 justify-center" src="'.$countermage.'"></a>';
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
                    <td class="text-center">
                        <input wire:model.live="productSelections.{{ $product->id }}" model: type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    </td>
                    <td class="relative text-center">
                        <span class="block text-center text-gray-400 text-xs">{{Conditions()->get($product['p_condition'])}}</span>

                        <!--Under views/components/input.blade.php -->
                        <button type="button" data-id="{{$id}}" class="menu-btn inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                            <svg class="w-2.5 h-2.5 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                            Action
                        </button>

                    </td>
                    <td class="px-1 py-1 relative w-24">
                        @if ($product->listings)
                            @if ($product->listings->listitem)
                                <a target='_blank' href="https://www.ebay.com/itm/{{$product->listings->listitem}}"><img src='/assets/ebay-logo.png' class="top-0 left-3.5 absolute h-7"></a>
                            @elseif ($product->listings->errors)
                                <div title="{{$product->listings->errors}}"><img src='/assets/ebay-logo-x.png' class="top-0 left-3.5 absolute h-7"></a>

                            @endif
                        @endif
                        {!! $path !!}</td>
                    <td class="px-3 py-2 w-24">
                        <a  @click="isSliderVisible = true; selectedRow = {{$loop->index}}" data-id="{{$id}}" class="editproduct cursor-pointer hover:text-blue-500 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 dark:hover:text-white">{{ $id }}</a>

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
                    <td @click.away="$wire.productFieldName === '{{$product->id}}.dealerPrice' ? $wire.cancelEdit : null"  class="px-3 py-2 text-right w-24" wire:click.self="editMode({{$product->id}},'dealerPrice')">
                        @if ($productFieldName === $product->id.".dealerPrice")
                            <div x-data x-init="$refs.pricebox.focus()">
                                <input wire:model="productDealerPrice" x-ref="pricebox" wire:keydown.enter="updateDealerPrice" type="text" class="bg-gray-100 text-gray-900 text-sm rounded block w-full p-2" />
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" wire:click="updateDealerPrice" class="text-blue-700 border border-blue-700 hover:bg-white-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2 text-center inline-flex items-center dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500">
                                <svg class="h-3 w-3 text-blue-600"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />  <polyline points="17 21 17 13 7 13 7 21" />  <polyline points="7 3 7 8 15 8" /></svg>
                                </svg>
                                <span class="sr-only">Edit</span>
                                </button>
                                <button type="button" wire:click="cancelEdit" class="text-blue-700 border border-blue-700 hover:bg-white-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2 text-center inline-flex items-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500">
                                    <svg class="h-3 w-3 text-blue-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                <span class="sr-only">Cancel</span>
                                </button>
                            </div>
                            @error("productDealerPrice")
                                {{$message}}
                            @enderror
                        @else
                            ${{number_format($product->p_newprice,0)}}
                        @endif

                    </td>
                    <td class="px-3 py-2 text-right w-24">${{number_format($product->p_retail,0)}}</td>
                    <td @click.away="$wire.productFieldName === '{{$product->id}}.qty' ? $wire.cancelEdit : null" class="px-3 py-2 text-center w-24">
                        <!-- To make the td clickable, add self to the end like this wire:model.self -->
                        <!-- because normally clicks are usually for buttons and anchors -->
                        @if ($productFieldName === $product->id.".qty")
                            <div x-data x-init="$refs.qtybox.focus()">
                            <input type="text" wire:model="productQty" wire:keydown.enter="updateQty" x-ref="qtybox" class="bg-gray-100 test-gray-900 text-sm rounded block w-16 dark:bg-gray-600 dark:text-gray-100" />
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" wire:click="updateQty" class="text-blue-700 border border-blue-700 hover:bg-white-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2 text-center inline-flex items-center dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500">
                                <svg class="h-3 w-3 text-blue-600"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />  <polyline points="17 21 17 13 7 13 7 21" />  <polyline points="7 3 7 8 15 8" /></svg>
                                </svg>
                                <span class="sr-only">Edit</span>
                                </button>
                                <button type="button" wire:click="cancelEdit" class="text-blue-700 border border-blue-700 hover:bg-white-700 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2 text-center inline-flex items-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500">
                                    <svg class="h-3 w-3 text-blue-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                <span class="sr-only">Cancel</span>
                                </button>
                            </div>
                            @error("productQty")
                                {{$message}}
                            @enderror
                        @else
                            <button class="text-blue-700 border border-blue-700 hover:bg-white-700 hover:text-black focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm p-2 text-center inline-flex items-center dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:focus:ring-blue-800 dark:hover:bg-blue-500" type="button" wire:click="editMode({{$product->id}},'qty')">{{$product->p_qty}}</button>
                        @endif
                    </td>
                    <td class="px-3 py-2 w-24">{!!$details !!}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <th colspan="5" scope="row" class="px-3 py-3 text-base">Total</th>
                        <td colspan="3" class="px-3 py-3">${{number_format($totalCost,0)}}</td>
                        <td class="px-3 py-3 text-center">{{$totalQty}}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


    <div class="px-6 py-3">
   {{ $products->links('livewire.pagination') }}
   </div>

@script
   <script>
    $(document).ready( function() {
        $(document).on('mouseenter', 'span.hide', function () {
            $(this).css('opacity',1)
        }).on('mouseleave', 'span.hide', function () {
            $(this).css('opacity',0)
        })

        $('.menu-btn').popupMenu({
            menuSelector: "#popup-menu",
            selectors: {
                menuItem: ".menu-item"
            },

            // onMenuOpen now receives the full data object!
            onMenuOpen: (data, menu) => {
                const productId = data.id;

                menu.find(".menu-item").attr("data-id", productId);
                menu.find("li.editproduct, li.editinvoice").off('click.unifiedSlider').on('click.unifiedSlider', function(e) {
                    // 1. Stop the event from propagating to prevent conflicts with the menu's general closure logic.
                    e.stopImmediatePropagation();

                    // 2. Conditional Livewire Call: Only execute 'makeInvoice' if the clicked item is 'editinvoice'.
                    if ($(this).hasClass('editinvoice')) {
                        // This call loads the data before the UI slide-over handler runs.
                        $wire.$call('makeInvoice', productId);
                    }

                    // 3. Manually trigger the external UI management handler for either .editproduct or .editinvoice.
                    $(document).trigger({
                        type: 'click',
                        target: this // 'this' is the clicked li element
                    });

                    // 4. Close the menu last.
                    $.popupMenuClose();
                });

                menu.find("li.ebay").attr("wire:click.prevent", `postToEbay(${productId})`);
                menu.find("li.endebay").attr("wire:click.prevent", `endOnEbay(${productId})`);
                menu.find("li.return").attr("wire:click", `returnToVendor(${productId})`);

                // menu.find("li.editinvoice").attr("wire:click.prevent", `makeInvoice(${productId})`);
                menu.find("li.deleteitem").attr("wire:click", `deleteProduct(${productId})`);
                menu.find("li.print").attr("wire:click", `printTag(${productId})`);


            }
        });

        $wire.on('import-incomplete', event => {
            // Hide the popup menu when a product is updated
            alert('Import completed with errors. Please check the logs.');
        });

        $wire.on('printTag', event => {
            // Hide the popup menu when a product is updated
            window.open('/admin/printTag/'+event[0].ids, 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;
        });

        // Hide menu when clicking anywhere outside
        // $(document).on("click", function () {
        //     $("#popup-menu").addClass("hidden").removeData("active-button");
        // });

    })

</script>
@endscript

</div>