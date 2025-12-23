<div x-data="{ dropdownOpen: false }" @keydown.window.escape="dropdownOpen = false" @click.away="dropdownOpen = false" x-ref="ts">
    <button
        @click="dropdownOpen = !dropdownOpen" type="button"
        class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
        aria-haspopup="true" x-bind:aria-expanded="dropdownOpen" aria-expanded="true">
        Action
        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
        </svg>
    </button>

    <div class="relative">
        <div
            x-data="{
            'style': {'tb': 'bottom', 'lr': 'right'},
            checkSize () {
                let size = $el.scrollHeight || $refs.ts.getBoundingClientRect().top || 88,
                sizeBottom = $el.getBoundingClientRect().bottom || $refs.ts.getBoundingClientRect().bottom,
                sizeLeft = $el.getBoundingClientRect().left || $refs.ts.getBoundingClientRect().left;
                let arr = {'tb': 'bottom', 'lr': 'right'};
                if ((window.innerHeight - sizeBottom) < (size + $el.offsetHeight)) arr.tb = 'top';
                if (sizeLeft < 200) arr.lr = 'right';
                return arr; 
            }
        }"
            x-init="style = checkSize();"
            @resize.window="style = checkSize();"
            x-show="dropdownOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="origin-top-right absolute mt-2 w-44 rounded-md shadow-lg z-50 border dark:border-gray-700 right-0"
            :class="{ 'bottom-10': style.tb === 'top', 'right-0': style.lr === 'left' }"
            style="display: none;"
        >
            <div @click="dropdownOpen = false" class="text-left rounded-md bg-gray-200 dark:bg-gray-900 divide-y divide-gray-100 shadow-xs">
                <div class="py-1" role="none">
                    <a href="#" onclick="window.open('/admin/products/{{$id}}/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white cursor-pointer text-gray-900 dark:text-gray-200">Print Label</a>
                    <a @click="isSliderVisible = !isSliderVisible" data-id="{{$id}}" data-duplicate="1" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-gray-900 cursor-pointer dark:text-gray-200 editproduct">Duplicate</a>
                </div>
                <div wire:ignore.self class="py-1" role="none">
                    <!-- <a wire:click="createInvoice({{$id}})" class="cursor-pointer block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Create Invoice</a> -->
                    <a wire:ignore.self wire:click.prevent="makeInvoice({{$id}})" data-id="{{$id}}" class="cursor-pointer block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white editinvoice">Make Invoice</a>
                </div>
                
                <div  class="py-1" role="none">
                    <a wire:click.prevent="postToEbay({{$id}})" class="cursor-pointer block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Submit to Ebay</a>
                </div>
                <div class="py-1" role="none">
                    <a wire:click="returnToVendor({{$id}})" class="cursor-pointer block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Return to Vendor</a>
                </div>
                
                <div class="py-1" role="none">
                    <a wire:confirm="Are you sure you want to delete this item?" wire:click="deleteProduct({{$id}})" class="cursor-pointer block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Delete Item</a>
                </div>
            </div>
        </div>
    </div>
</div>


