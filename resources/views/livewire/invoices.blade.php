<div x-data="{ isSliderVisible: false,
        focusSearchBox() {
            if (!this.isSliderVisible) {
                $refs.invoicesearchbox.focus();
                $refs.invoicesearchbox.select();
            }
        }
            }"
x-init="focusSearchBox()"
@keydown.window="
if ($event.key === '=') {

            if ($refs.invoicesearchbox !== document.activeElement && !isSliderVisible) {
                $event.preventDefault();
                focusSearchBox();
            }
        }">
    {{-- The whole world belongs to you. --}}
        <!-- Page Header -->

    <div x-data class="absolute dark:bg-gray-600 p-1.5 right-8 rounded-lg top-21">
        <button data-modal-target="print-label" data-modal-toggle="print-label" id="actions" class="mt-1 mr-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">Print Label from AIB </button>
    </div>

    <div wire:ignore.self id="print-label" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
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

                    <div class="mb-4">
                        <label for="tracking" class="block mb-2.5 text-sm font-medium text-heading">Tracking number</label>
                        <input type="text" wire:model="trackingNumber" id="tracking" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Paste or type your tracking #" required />
                    </div>

                    <button data-modal-hide="print-label"  wire:click.prevent="printLabel()" type="button" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Print
                    </button>
                    <div x-on:open-new-tab.window="window.open($event.detail.url, '_blank')"></div>
                </form>
            </div>
        </div>
    </div>

    <div wire:loading.delay.longest class="fixed z-50">
        <div class="text-center fixed left-0 top-0 bg-black opacity-50 w-screen h-screen justify-center center z-50">
            <div role="status" class="flex h-screen inline center justify-center">
                <svg aria-hidden="true" class="inline w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    @livewire('messages', ['loggedInUser' => auth()->id()])
    <livewire:payments :$order/>
    <livewire:product-item />

    @if (session()->has('message'))
        <div id="alert-border-1" class="flex center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('message') }}
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex center justify-center h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @elseif (session()->has('error'))
        <div id="alert-border-1" class="flex center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dark:text-red-400 dark:bg-gray-800 dark:border-red-800 transition-all duration-500 animate-bounce" role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <div class="ms-3 text-sm font-medium">
                {{ session('error') }}
            </div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"  data-dismiss-target="#alert-border-2" aria-label="Close">
            <span class="sr-only">Dismiss</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            </button>
        </div>
    @endif

    <livewire:invoice-item />

    <div class="relative sm:rounded-lg">
        <div class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900 md:p-4">
                @role('superadmin|administrator')
                    <button @click="isSliderVisible = !isSliderVisible" wire:ignore.self wire:click="createNew()" class="editinvoice bg-sky-500 hover:bg-[#0284c7] text-white font-bold text-sm px-3 py-1.5 rounded">Create New</button>
                @endrole

                <div  wire:ignore class="relative mt-1">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" x-ref="invoicesearchbox" wire:model.live.debounce.150ms="search" id="invoice-search" class="block h-10 ps-10 text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search for items">
                </div>
        </div>

        <ul class="items-center w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg sm:flex dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <?php $label = ""; $isChecked = $status; ?>

                @foreach(range(0, 3) as $y)
                <?php
                    $cr = '$set("status", '.$y.')';
                    switch ($y) {
                        case 0:
                            $label = "Unpaid Invoices/Memos";
                            // $isChecked=true;
                            break;
                        case 1:
                            $label = "Paid Invoices";
                            break;
                        case 2:
                            $label = "Returned";
                            break;
                        case 3:
                            $label = "Display all";
                            break;
                    }
                ?>
                <li class="w-full border-b border-gray-200 sm:border-b-0 sm:border-r dark:border-gray-600">
                    <div class="flex items-center ps-3">
                        <input id="horizontal-list-radio-{{$y}}" wire:click="setStatus({{$y}})" {{ $y==$isChecked ? 'checked' : "" }} type="radio" name="list-radio" value="status.{{$y}}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                        <label for="horizontal-list-radio-{{$y}}" class="py-3 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{$label}}</label>
                    </div>
                </li>
                @endforeach

        </ul>

                    <!-- Popup Menu (Hidden Initially) -->
        <div id="popup-menu" class="hidden z-50 absolute bg-gray-200 dark:bg-gray-800 shadow-lg rounded-lg border border border-gray-300">
            <ul class="divide-gray-300 grid grid-cols-2">
                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white print">Print Invoice</li>

                <li id="textinvoice-modal" data-modal-target="textinvoice" data-modal-toggle="textinvoice" class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white textinvoice">Text Invoice</li>
                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white packingslip">Print Packing Slip</li>
                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white email">Email Invoice</li>
                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white commercial">Print Commercial</li>
                <li class="menu-item border-t border-gray-300 cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white returnall">Return all</li>

                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white appraisal">Print Appraisal</li>
                <li class="menu-item border-t border-gray-300 cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white deleteinvoice">Delete Invoice/Order</li>

                <li class="menu-item cursor-pointer block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white printstatement">Print Statement</li>

            </ul>
        </div>

        <div class="overflow-x-auto">
            <table x-data = "{status: @entangle('status')}" class="w-full text-sm text-left rtl:text-right dark:text-white-400">
                <thead
                    :class="status == 0 ? 'bg-red-300' : 'bg-gray-50'"
                    class="text-xs text-gray-700 uppercase dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" style="width: 40px" class="px-3 py-3"></th>
                        <th scope="col" class="px-3 py-3">ID</th>
                        <th scope="col" class="px-3 py-3">Invoice</th>
                        <th scope="col" class="px-3 py-3">Company</th>
                        <th scope="col" class="px-3 py-3">Status</th>
                        <th scope="col" class="px-3 py-3">Date</th>
                        <th scope="col" class="px-3 py-3">Amount</th>
                    </tr>
                </head>
                <tbody>

                <?php $counter = 0; $action = 'unpaid'; ?>
                @foreach($orders as $order)
                <?php
                    $counter ++ ;$incomplete = '';
                    $id = "prod-".$order->id;

                    if ($order->customers->first())
                        $custId = $order->customers->first()->id;
                    else $custId = 0;

                    if ($order->code)
                        $status = $order->cc_status;
                    else $status = orderStatus()->get($order->status);

                    if ($action == 'paid') {
                        $total = $order->payments->sum('amount');
                    } else {
                        $total = $order->total - $order->payments->sum('amount');
                    }

                    $companyInfo = (!$order->b_firstname && !$order->b_lastname && $order->s_firstname && $order->s_lastname) ? '<b>'.$order->b_company . '</b>-'.$order->s_firstname . ' ' .$order->s_lastname .'*': $order->b_company;
                    $id = $order->id;
                    $po = $order->po;

                    if ($po)
                        $companyInfo .= ' ('. $po .') ';
                    if ($order['payment_options'] == 'Incomplete')
                        $incomplete = ' <b>(Incomplete)</b>';

                    $method = $order->method;$shipped='';

                    if ($order['emailed'])
                        $method .=' <i class="far fa-envelope" title="Invoice was emailed"></i>';
                    if ($order->tracking)
                        $shipped = " <a href='https://www.fedex.com/apps/fedextrack/?tracknumbers=$order->tracking' target='_blank'><i class='fab fa-fedex fa-lg'></i></a>";

                ?>
                <tr :class="status == 0 ? 'odd:bg-red-100 even:bg-red-50 hover:bg-red-200 even:bg-red-50' : 'odd:bg-gray-100 hover:bg-gray-200 even:bg-gray-50'"
                    wire:key="{{$id}}"
                    class="odd:dark:bg-gray-900 dark:text-gray-200 even:dark:bg-gray-800 border-b dark:border-gray-700">

                    <td class="px-3 py-2">

                        <button type="button" data-id="{{$id}}" data-status="{{$status}}" data-custid="{{$custId}}" data-isOrderPage="false" class="menu-btn inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                            <svg class="w-2.5 h-2.5 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                            Options
                        </button>

                    </td>
                    <td class="px-3 py-2">
                        <a href="#" @click="isSliderVisible = !isSliderVisible" wire:click.prevent="loadInvoice({{$id}})" data-id="{{$id}}" class="editinvoice cursor-pointer dark:hover:text-white text-sky-600">{{$order->id}}</a>

                    </td>
                    <td class="px-3 py-2">{!! $method.$shipped !!}</td>
                    <td class="px-3 py-2">{!! $companyInfo !!}</td>
                    <td class="px-3 py-2">{{$status}}</td>
                    <td class="px-3 py-2 text-center">{{$order->created_at->format('m-d-y')}}</td>
                    <td class="px-3 py-2 text-right">${{number_format($total,2)}}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <th colspan="5" scope="row" class="px-3 py-3 text-base">Total</th>
                        <td colspan="2" class="font-semibold px-3 py-3 text-right text-lg text-red-400">${{number_format($totalcost,2)}}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div wire:ignore.self x-data @click.outside="$wire.closeWhatsapp()" id="textinvoice" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Text Invoice to:
                    </h3>
                    <button type="button" wire:click="closeWhatsapp" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="textinvoice">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <form class="p-4 md:p-5">
                    <div class="grid gap-4 mb-4 grid-cols-4">
                        <div class="flex center">
                            <input checked id="checkbox-person-1" wire:model="textPerson" type="radio" value="9176990831" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Albert</label>
                        </div>

                        <div class="flex center">
                            <input id="checkbox-person-2" wire:model="textPerson" type="radio" value="9176569494" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Eddie</label>
                        </div>

                        <div class="flex center">
                            <input id="checkbox-person-3" wire:model="textPerson" type="radio" value="7186147678" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-3" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Ephraim</label>
                        </div>

                    </div>

                    <input type="text" wire:model.live.debounce.150ms="whatsapptoken" class="block mb-3 w-full h-10 text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Token goes here">
                    @if ($whatsAppNewToken)
                        <span class="break-all">{{$whatsAppNewToken}}</span>
                    @endif
                    <button data-modal-hide="textinvoice"  wire:click.prevent="sendText(0)" type="button" class="text-white inline-flex center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Send Text</button>
                    <button wire:click.prevent="sendText(1)" type="button" class="text-white inline-flex center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Initiate Texting</button>
                </form>
            </div>
        </div>
    </div>

    <div class="py-3">
   {{ $orders->links('livewire.pagination') }}
   </div>


@script
    <script>
        $(function() {
            $(document).on("click", "#alert-border-1 button", function() {
            $(this).parent().slideUp("slow");;
        })

        $('.menu-btn').popupMenu({
            menuSelector: "#popup-menu",
            selectors: {
                menuItem: ".menu-item"
            },

            // onMenuOpen now receives the full data object!
            onMenuOpen: (data, menu) => {
                // The 'data' object contains all attributes from the button,
                // e.g., data.id, data.invoiceid, data.sku, etc.

                // Example Button HTML: <button data-orderid="123" data-customername="Jane" data-lineindex="5" ...>
                const id = data.id;
                const custId = data.custId;
                const status = data.status;

                // Your logic is now entirely custom:
                const openWindow = (url) => `window.open('${url}', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`;

                menu.find(".menu-item").attr("data-id", id);
                menu.find("li.print").attr("onclick", `window.open('/admin/orders/${id}/print', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
                menu.find("li.printstatement").attr("onclick", `window.open('/admin/orders/${custId}/${status}/printstatement', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
                menu.find("li.packingslip").attr("onclick", `window.open('/admin/orders/${id}/print/packingslip', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
                menu.find("li.appraisal").attr("onclick", `window.open('/admin/orders/${id}/print/appraisal', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);
                menu.find("li.commercial").attr("onclick", `window.open('/admin/orders/${id}/print/commercial', 'new', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400'); return false;`);

                menu.find("li.email").attr("wire:click.prevent", `sendEmail(${id})`);
                menu.find("li.textinvoice").attr("wire:click.prevent", `setCurrentInvoiceId(${id})`);
                menu.find("li.returnall").attr({
                    "wire:click.prevent": `returnAllProducts(${id})`,
                    "wire:confirm": `You're about to return all the products for the invoice/memo # ${id}. Are you sure you want to do that?`
                });

                menu.find("li.deleteinvoice").attr({
                    "wire:click.prevent": `deleteInvoice(${id})`,
                    "wire:confirm": `This action will completely delete invoice #${id}. Are you sure you want to do this?`
                });
            }
        });

        // Hide menu when clicking anywhere outside
        // $(document).on("click", function () {
        //     $("#popup-menu").addClass("hidden").removeData("active-button");
        // });

        // Prevent menu from closing when clicking inside it
        $("#popup-menu").on("click", function (e) {
            e.stopPropagation();
        });
            $wire.on('itemMsg', msg => {
                Swal.fire({
                    title: "Invoice",
                    text: msg,
                }).then((result) => {
                    if (result.isConfirmed) {

                    }
                });
            })

        })
    </script>
@endscript

</div>