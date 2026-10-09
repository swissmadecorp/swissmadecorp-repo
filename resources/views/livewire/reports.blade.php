@section ('footer')
<script src="/js/jquery.mask.js" type="text/javascript"></script>
<script src="/js/jquery.autocomplete.min.js"></script>
@stop

<div class="space-y-5">
    <livewire:invoice-item />

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-5">
        <div class="mb-4 flex flex-wrap gap-2" role="tablist" aria-label="Report type">
            @foreach(['paid' => 'Paid invoices', 'unpaid' => 'Unpaid invoices', 'memo' => 'On memo', 'all' => 'All invoices'] as $tab => $label)
                <button type="button" wire:click="setView('{{ $tab }}')" class="rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $view === $tab ? 'border-sky-700 bg-sky-700 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-sky-300 hover:text-sky-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-4 md:items-end">
            <div>
                <label for="report-search" class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search company or customer</label>
                <input id="report-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Company, first name, or last name" class="block h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-900 focus:border-sky-500 focus:ring-sky-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
            </div>
            <div>
                <label for="report-from" class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">From date</label>
                <input id="report-from" type="date" wire:model.live="fromDate" class="block h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-900 focus:border-sky-500 focus:ring-sky-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label for="report-to" class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">To date</label>
                <input id="report-to" type="date" wire:model.live="toDate" class="block h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-900 focus:border-sky-500 focus:ring-sky-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
            <button type="button" wire:click="resetFilters" class="h-10 rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Clear filters</button>
        </div>

    </div>

    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ ucfirst($view) }} invoice report</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $orders->count() }} {{ \Illuminate\Support\Str::plural('invoice', $orders->count()) }} matching the selected filters</p>
        </div>
        <div class="rounded-xl bg-sky-50 px-5 py-3 text-sky-800 dark:bg-sky-900/30 dark:text-sky-200">
            <span class="block text-xs font-bold uppercase tracking-wide">{{ in_array($view, ['unpaid', 'memo'], true) ? 'Total owed' : ($view === 'all' ? 'Total amount' : 'Total amount received') }}</span>
            <strong class="text-xl">${{ number_format($totalReceived, 2) }}</strong>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <table class="w-full min-w-[680px] text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr><th class="px-4 py-3">Document</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Company / customer</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">{{ in_array($view, ['unpaid', 'memo'], true) ? 'Amount owed' : ($view === 'all' ? 'Amount' : 'Amount received') }}</th></tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                @php
                    $customer = $order->s_company ?: trim($order->s_firstname.' '.$order->s_lastname);
                    if (!$customer) { $relation = $order->customers->first(); $customer = $relation?->company ?: trim(($relation?->firstname ?? '').' '.($relation?->lastname ?? '')); }
                    $status = $order->method === 'On Memo' ? 'memo' : ($order->status ? 'paid' : 'unpaid');
                    $received = $reportAmounts[$order->id] ?? 0;
                @endphp
                <tr class="border-t border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                    <td class="px-4 py-4"><a href="#" @click.prevent="$dispatch('load-invoice', { id: {{ $order->id }} })" class="editinvoice font-semibold text-sky-700 hover:underline dark:text-sky-300">{{ $status === 'memo' ? 'Memo' : 'Invoice' }} #{{ $order->id }}</a></td>
                    <td class="whitespace-nowrap px-4 py-4">{{ optional($order->created_at)->format('M j, Y') }}</td>
                    <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">{{ $customer ?: 'Customer not listed' }}</td>
                    <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($status === 'memo' ? 'bg-violet-100 text-violet-700' : 'bg-orange-100 text-orange-700') }}">{{ ucfirst($status) }}</span></td>
                    <td class="px-4 py-4 text-right font-bold text-sky-700 dark:text-sky-300">${{ number_format($received, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No invoices match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links('livewire.pagination') }}</div>

</div>
