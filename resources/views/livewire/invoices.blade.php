<div class="rounded-2xl bg-white p-4 dark:bg-gray-800 sm:p-6" x-data="{ isSliderVisible: false,
        focusSearchBox() {
            if (!this.isSliderVisible) {
                $refs.invoicesearchbox.focus();
                $refs.invoicesearchbox.select();
            }
        }
            }"
x-init="$nextTick(() => focusSearchBox())"
@keydown.window="
if ($event.key === '=') {

            if ($refs.invoicesearchbox !== document.activeElement && !isSliderVisible) {
                $event.preventDefault();
                focusSearchBox();
            }
        }">

<style>
        @keyframes invoice-appointment-marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-50% - var(--invoice-marquee-gap) / 2)); }
        }

        .invoice-appointment-marquee {
            --invoice-marquee-gap: max(18rem, 60vw);
            animation: invoice-appointment-marquee 26s linear infinite;
            gap: var(--invoice-marquee-gap);
            will-change: transform;
        }

        .invoice-appointment-marquee-mask {
            mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
        }

        .invoice-appointment-marquee-mask:hover .invoice-appointment-marquee {
            animation-play-state: paused;
        }
    </style>

    <!-- Page Header -->

    <div x-data class="absolute right-8 top-21 rounded-lg p-1.5 dark:bg-gray-600">
        <button data-modal-target="print-label" data-modal-toggle="print-label" id="actions" class="rounded-lg border border-blue-200 bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-300 dark:hover:bg-gray-700" type="button">Print Label from AIB</button>
    </div>

    <div wire:key="invoice-search-toolbar" class="mb-4 flex flex-wrap items-center gap-4">
            @role('superadmin|administrator')
                <button wire:click="createNew()" type="button" class="editinvoice rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">+ Create New</button>
            @endrole
        <div class="relative ms-auto w-full sm:w-96">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input wire:key="invoice-search-input" type="text" aria-label="Search invoices" x-ref="invoicesearchbox" wire:model.live.debounce.300ms="search" id="invoice-search" class="block h-11 w-full rounded-lg border border-gray-300 bg-white ps-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" placeholder="Search invoices">
            <span wire:loading.delay wire:target="search" class="absolute right-3 top-3 text-xs text-blue-600 dark:text-blue-300" role="status">Searching…</span>
        </div>
    </div>

    <div wire:ignore.self id="print-label" tabindex="-1" aria-hidden="true" class="backdrop-blur bg-black/50 hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Print FedEx Label
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
                        <label for="tracking" class="block mb-2.5 text-sm font-medium text-heading">Enter tracking number in the box below to generate the label.</label>
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

    <div wire:loading.delay.longest wire:target.except="search" class="fixed z-50">
        <div class="text-center fixed left-0 top-0 bg-black/50 w-screen h-screen justify-center center z-50">
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

    @if ($appointmentBanner['count'] > 0)
        <div class="invoice-appointment-marquee-mask mb-4 overflow-hidden rounded-2xl border border-stone-200 bg-gradient-to-r from-stone-100 via-white to-sky-50 shadow-sm dark:border-gray-600 dark:from-gray-700 dark:via-gray-700 dark:to-slate-700">
            <div class="py-3">
                <div class="invoice-appointment-marquee flex w-max items-center gap-12 px-4">
                    @foreach (range(1, 2) as $loop)
                        <div class="flex shrink-0 items-center gap-3" @if ($loop === 2) aria-hidden="true" @endif>
                            @foreach ($appointmentBanner['items'] as $appointment)
                                <div class="flex min-w-[360px] items-start justify-between gap-3 rounded-2xl border border-stone-200 bg-white/90 px-4 py-3 text-stone-800 shadow-sm dark:border-gray-500 dark:bg-gray-800/90 dark:text-gray-100">
                                    <a href="/admin/appointments?filter=approaching" class="flex flex-1 items-center justify-between gap-4 transition hover:-translate-y-0.5">
                                        <div>
                                            <p class="text-sm font-semibold">{{ $appointment['customer_name'] }}</p>
                                            <p class="text-xs text-stone-500 dark:text-gray-400">{{ $appointment['product_name'] }} - #{{ $appointment['product_id'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-semibold">{{ $appointment['full_date_label'] }}</p>
                                            <p class="text-xs text-stone-500 dark:text-gray-400">{{ $appointment['time_label'] }} {{ $appointment['time_suffix'] }}</p>
                                        </div>
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="dismissAppointmentBanner({{ $appointment['id'] }})"
                                        class="shrink-0 rounded-full border border-stone-200 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-500 transition hover:border-stone-300 hover:text-stone-700 dark:border-gray-600 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        Dismiss
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <livewire:invoice-item />

    <div wire:key="invoice-list" class="relative">
        <ul class="mb-5 flex flex-wrap gap-1 border-b border-gray-200 text-sm font-medium dark:border-gray-700" aria-label="Invoice status">
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
                <li>
                    <button type="button" wire:click="setStatus({{$y}})" aria-pressed="{{ $y == $isChecked ? 'true' : 'false' }}" class="border-b-2 px-4 py-3 font-semibold transition {{ $y == $isChecked ? 'border-blue-600 text-blue-600 dark:text-blue-300' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">{{$label}}</button>
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

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <table class="w-full min-w-[760px] text-left text-sm text-gray-700 dark:text-gray-200">
                <thead class="text-xs font-semibold uppercase tracking-wide {{ (int) $status === 0 ? 'bg-red-300 text-red-950 dark:bg-red-900 dark:text-red-100' : 'bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                    <tr>
                        <th scope="col" style="width: 40px" class="px-3 py-3">Options</th>
                        <th scope="col" class="px-3 py-3">ID</th>
                        <th scope="col" class="px-3 py-3">Invoice</th>
                        <th scope="col" class="px-3 py-3">Company</th>
                        <th scope="col" class="px-3 py-3">Status</th>
                        <th scope="col" class="px-3 py-3">Date</th>
                        <th scope="col" class="px-3 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>

                <?php $counter = 0; $unpaidCounter = 0; $action = 'unpaid'; ?>
                @foreach($orders as $order)
                <?php
                    $counter ++ ;$incomplete = '';
                    $id = "prod-".$order->id;


                    $custId = $order->customers->first()?->id;

                    if ($order->code)
                        $action = $order->cc_status;
                    else
                        $action = orderStatus()->get($order->status);

                    if (strtolower(trim((string) $action)) == 'paid') {
                        $total = $order->payments->sum('amount');
                    } else {
                        $total = $order->total - $order->payments->sum('amount');
                    }

                    $groupId = $order->customers->first()?->pivot?->cgroup ?? 0;
                    $customerGroup = [2 => 'Website', 3 => 'Chrono24', 4 => 'Ebay'];

                    if ($groupId < 2) {
                        $companyInfo = $order->b_company;
                    } else
                        $companyInfo = '<b>'.$customerGroup[$groupId] . '</b>-'.$order->s_firstname . ' ' .$order->s_lastname .'*';

                    // $companyInfo = (!$order->b_firstname && !$order->b_lastname && $order->s_firstname && $order->s_lastname) ? '<b>'.$order->b_company . '</b>-'.$order->s_firstname . ' ' .$order->s_lastname .'*': $order->b_company;
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
                @php
                    $rowStatus = strtolower(trim((string) $action));
                    $isUnpaid = (int) $order->status === 0 || $rowStatus === 'unpaid';
                    if ($isUnpaid) { $unpaidCounter++; }
                    $rowClass = $isUnpaid
                        ? (($unpaidCounter % 2 ? 'bg-red-100 dark:bg-red-950/60' : 'bg-red-50 dark:bg-red-950/30') . ' hover:bg-red-200 dark:hover:bg-red-900/60')
                        : ($rowStatus === 'insured'
                            ? 'odd:bg-green-100 even:bg-green-50 hover:bg-green-200 dark:odd:bg-green-950/60 dark:even:bg-green-950/30 dark:hover:bg-green-900/60'
                            : 'bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800');
                    $badgeClass = $isUnpaid ? 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-200'
                        : (in_array($rowStatus, ['paid', 'insured']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200');
                @endphp
                <tr wire:key="{{$id}}" class="border-b border-gray-100 transition-colors dark:border-gray-700 {{ $rowClass }}">

                    <td class="px-4 py-4">

                        <button type="button" data-id="{{$id}}" data-status="{{$action}}" data-custid="{{$custId}}" data-isOrderPage="false" class="menu-btn invoice-options-btn inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                            <svg class="w-2.5 h-2.5 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                            Options
                        </button>

                    </td>
                    <td class="px-4 py-4">
                        <a href="#" wire:click.prevent="loadInvoice({{$id}})" data-id="{{$id}}" class="editinvoice cursor-pointer dark:hover:text-white text-sky-600">{{$order->id}}</a>

                    </td>
                    <td class="px-4 py-4">{!! $method.$shipped !!}</td>
                    <td class="px-4 py-4 font-medium">{!! $companyInfo !!}</td>
                    <td class="px-4 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">{{$action}}</span></td>
                    <td class="whitespace-nowrap px-4 py-4">{{$order->created_at->format('m-d-y')}}</td>
                    <td class="whitespace-nowrap px-4 py-4 text-right font-semibold tabular-nums">${{number_format($total,2)}}</td>
                </tr>
                @endforeach
                @if ($orders->isEmpty())
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">No invoices match these filters.</td></tr>
                @endif
                </tbody>
                @if (in_array((int) $status, [0, 3], true))
                <tfoot class="bg-white font-semibold dark:bg-gray-900" aria-live="polite">
                    <tr class="border-t border-gray-200 text-red-800 dark:border-gray-700 dark:text-red-200">
                        <th colspan="6" scope="row" class="px-4 py-3">Unpaid invoices — outstanding balance</th>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-lg tabular-nums">${{ number_format($invoiceTotals['unpaid'], 2) }}</td>
                    </tr>
                </tfoot>
                @endif
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
                    <button type="button" wire:click="closeWhatsapp" class="items-center text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="textinvoice">
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
                            <input checked id="checkbox-person-1" wire:model="textPerson" type="radio" value="19176990831" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Albert</label>
                        </div>

                        <div class="flex center">
                            <input id="checkbox-person-2" wire:model="textPerson" type="radio" value="19176569494" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Eddie</label>
                        </div>

                        <div class="flex center">
                            <input id="checkbox-person-3" wire:model="textPerson" type="radio" value="17186147678" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-3" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Ephraim</label>
                        </div>

                        <div class="flex center">
                            <input id="checkbox-person-4" wire:model="textPerson" type="radio" value="19174359735" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                            <label for="checkbox-person-4" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Gaby</label>
                        </div>
                    </div>

                    <input type="text" wire:model.live.debounce.150ms="whatsapptoken" class="block mb-3 w-full h-10 text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Token goes here">
                    @if ($whatsAppNewToken)
                        <span class="break-all">{{$whatsAppNewToken}}</span>
                    @endif
                    <button data-modal-hide="textinvoice"  wire:click.prevent="sendText(0)" type="button" class="text-white inline-flex center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Send Invoioce</button>
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
        // Preserve the active search and caret through Livewire's DOM update.
        const invoiceSearchComponentId = $wire.$id;
        let invoiceSearchSelection = null;

        Livewire.hook('morph', ({ component }) => {
            if (component.id !== invoiceSearchComponentId) return;
            const input = component.el.querySelector('#invoice-search');
            invoiceSearchSelection = input && document.activeElement === input
                ? { start: input.selectionStart, end: input.selectionEnd, direction: input.selectionDirection }
                : null;
        });

        Livewire.hook('morph.updating', ({ el, component, skip }) => {
            if (component.id === invoiceSearchComponentId && el.id === 'invoice-search'
                && document.activeElement === el) {
                // The client owns this value while typing; an older response
                // must not rewrite it or disturb its selection.
                skip();
            }
        });

        Livewire.hook('morphed', ({ component }) => {
            if (component.id !== invoiceSearchComponentId || !invoiceSearchSelection) return;
            const selection = invoiceSearchSelection;
            invoiceSearchSelection = null;
            const input = component.el.querySelector('#invoice-search');
            const active = document.activeElement;
            if (!input || (active !== input && active !== document.body && active !== document.documentElement)) return;
            input.focus({ preventScroll: true });
            input.setSelectionRange(selection.start, selection.end, selection.direction);
        });
        // End search focus preservation.

        $(function() {
            $(document).on("click", "#alert-border-1 button", function() {
            $(this).parent().slideUp("slow");;
        })

        $('.invoice-options-btn').popupMenu({
            menuSelector: "#popup-menu",
            buttonSelector: '.invoice-options-btn',
            selectors: {
                menuItem: ".menu-item"
            },

            // onMenuOpen now receives the full data object!
            onMenuOpen: (data, menu) => {
                // The 'data' object contains all attributes from the button,
                // e.g., data.id, data.invoiceid, data.sku, etc.
                // Example Button HTML: <button data-orderid="123" data-customername="Jane" data-lineindex="5" ...>
                const id = data.id;
                const custId = data.custid;
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
