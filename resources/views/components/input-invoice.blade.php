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
            <div @click="dropdownOpen = false" class="rounded-md bg-gray-200 dark:bg-gray-900 divide-y divide-gray-100 shadow-xs">
                <div class="py-1" role="none">
                    <?php 
                        if (! function_exists('loc')) {
                            function loc($id,$l,$isOrderPage=false) {

                                if ($isOrderPage==false)
                                    $window = "window.open('/admin/orders/$id/$l'";
                                else $window = "window.open('/admin/estimates/$l/$id'";

                                $javaWindow = "printWindow = $window, 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=300,left=500,width=800,height=600'); 
                                debugger;
                                var printAndClose = function() {
                                    if (printWindow.document.readyState == 'complete') {
                                        printWindow.print();
                                        clearInterval(sched);
                                    }
                                }
                                var sched = setInterval(printAndClose, 1000);
                                ";
                                
                                return $javaWindow;
                            }
                        }
                        $class = "block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white dark:text-gray-200 text-gray-900"
                    ?>
                    <a href="#" onclick="<?= loc($id,'print',$isOrderPage)?>" class="{{$class}}">Print Order</a>
                    
                    @if ($isOrderPage==false)
                    <a href="#" onclick="<?= loc($custId,strtolower($status).'/printstatement')?>" class="{{$class}}">Print Statement</a>
                    <a href="#" onclick="<?= loc($id,'print/packingslip')?>" class="{{$class}}">Print Packing Slip</a>
                    <a href="#" onclick="<?= loc($id,'print/appraisal')?>" class="{{$class}}">Print Appraisal</a>
                    <a href="#" onclick="<?= loc($id,'print/commercial')?>" class="{{$class}}">Print Commercial</a>
                    @endif
                </div>
                @if ($isOrderPage==false)
                <div class="py-1" role="none">
                    <a href="#" wire:click.prevent="sendEmail({{$id}})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white dark:text-gray-200">Email Invoice</a>
                </div>
                
                <div class="py-1" role="none">
                    <!-- <a href="#" wire:click.prevent="sendInvoice({{$id}})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white dark:text-gray-200">Send Invoice</a> -->
                    <button id="textinvoice-modal" wire:click.prevent="setCurrentInvoiceId({{$id}})" data-modal-target="textinvoice" data-modal-toggle="textinvoice" type="button" class="text-left block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white dark:text-gray-200 w-full">Text Invoice</button>
                </div>
                @endif
                <!-- <div class="py-1" role="none">
                    <a wire:ignore.self id="editinvoice" wire:click.prevent="loadInvoice({{$id}})" data-id="{{$id}}" class="cursor-pointer hover:text-blue-500 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 dark:hover:text-white dark:text-gray-200">Edit Product</a>
                </div> -->
                
                @if ($isOrderPage==false && $status == 'Unpaid')
                <div class="py-1" role="none">
                    <a href="#" wire:click.prevent="returnAllProducts({{$id}})" wire:confirm="You're about to return all the products for the invoice/memo # {{$id}}. Are you sure you want to do that?" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white dark:text-gray-200">Return all</a>
                </div>
                @endif
                <div class="py-1" role="none">
                    <a href="#" wire:click.prevent="deleteInvoice({{$id}})" wire:confirm="This action will completely delete this invoice. Are you sure you want to do this?" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white dark:text-gray-200">Delete Invoice/Order</a>
                </div>
            </div>
        </div>
    </div>
</div>
