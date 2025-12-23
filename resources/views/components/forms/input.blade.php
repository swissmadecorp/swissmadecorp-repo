<div>
    <button id="dropdownActionButton{{ $counter }}" data-dropdown-toggle="dropdownAction{{ $counter }}" 
        class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 actionButton" type="button">
        <span class="sr-only">Show</span>
        Action
        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
        </svg>
    </button>
    <!-- Dropdown menu -->
    <div id="dropdownAction{{ $counter }}" class="z-10 hidden bg-gray-200 divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600 actionMenu">
        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownActionButton{{ $counter }}">
            <li>
                <a href="#" onclick="window.open('/admin/products/{{$id}}/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Print Label</a>
            </li>
            <li>
                <a href="/admin/products/{{$id}}/duplicate" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Duplicate</a>
            </li>
        </ul>
        <div class="py-1">
            <a href="/admin/orders/create?id={{$id}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Create Invoice</a>
        </div>

    </div> 
</div>