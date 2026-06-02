<div class="space-y-6 py-4">
    <div class="mx-auto max-w-6xl rounded-[2rem] border border-stone-200 bg-[#fcfbf7] p-5 shadow-[0_24px_60px_rgba(15,23,42,0.08)] dark:border-gray-600 dark:bg-gray-800">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-stone-200 pb-5 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-stone-700 shadow-sm ring-1 ring-stone-200 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6 2a1 1 0 0 1 1 1v1h6V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Z" />
                        <path d="M18 11H2v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5ZM6 13a1 1 0 1 1 0 2H5a1 1 0 1 1 0-2h1Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-stone-900 dark:text-white">Appointments</h2>
                    <p class="text-sm text-stone-500 dark:text-gray-400">Search, filter, and track every scheduled visit</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="showBookingOriginNoticeModal"
                    class="rounded-xl bg-[#2b6cb0] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f5a95]"
                >
                    + New Appointment
                </button>
                <button
                    type="button"
                    class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-stone-500 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M3 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1Zm3 5a1 1 0 0 1 1-1h9a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1Zm4 5a1 1 0 0 1 1-1h5a1 1 0 1 1 0 2h-5a1 1 0 0 1-1-1Z" />
                    </svg>
                </button>
            </div>
        </div>

        @if ($showBookingSourceNotice)
            <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100">
                New appointments are currently created from the customer-facing product page calendar in [Calendar.php](/C:/wamp64/www/swissmadecorp/app/Livewire/Calendar.php) and [calendar.blade.php](/C:/wamp64/www/swissmadecorp/resources/views/livewire/calendar.blade.php).
            </div>
        @endif

        @if ($urgentBanner['count'] > 0)
            <div class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-red-700 via-red-600 to-red-500 px-5 py-4 text-white shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-4a1 1 0 0 1 1 1v3.382l1.447 1.447a1 1 0 1 1-1.414 1.414l-1.74-1.74A1 1 0 0 1 9 10V7a1 1 0 0 1 1-1Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ $urgentBanner['count'] }} appointments in the next 72 hours</p>
                        <p class="text-xs text-red-100">{{ $urgentBanner['range_label'] }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="setFilter('approaching')"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold transition hover:bg-white/20"
                >
                    View All
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        @endif

        <div class="mt-5 grid gap-5 xl:grid-cols-[1.7fr_0.85fr]">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Upcoming Visitors</h3>

                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search customer, watch, or ID"
                            class="h-10 rounded-xl border border-stone-200 bg-white px-4 text-sm text-stone-700 placeholder:text-stone-400 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                        >
                        <input
                            type="date"
                            wire:model.live="dateFrom"
                            class="h-10 rounded-xl border border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                        <input
                            type="date"
                            wire:model.live="dateTo"
                            class="h-10 rounded-xl border border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @php
                        $filters = [
                            'upcoming' => 'Upcoming',
                            'approaching' => 'Approaching',
                            'today' => 'Today',
                            'week' => 'This Week',
                            'month' => 'This Month',
                            'all' => 'All',
                        ];
                    @endphp

                    @foreach ($filters as $value => $label)
                        <button
                            type="button"
                            wire:click="setFilter('{{ $value }}')"
                            class="{{ $filter === $value ? 'bg-stone-900 text-white dark:bg-white dark:text-gray-900' : 'bg-white text-stone-600 hover:bg-stone-100 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }} rounded-full px-4 py-2 text-sm font-medium shadow-sm ring-1 ring-stone-200 transition dark:ring-gray-600"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <p class="text-xs font-medium uppercase tracking-[0.18em] text-stone-400 dark:text-gray-500">
                    Approaching means future appointments scheduled within the next 72 hours.
                </p>

                <div class="space-y-4">
                    @forelse ($appointments as $appointment)
                        <div class="grid gap-4 rounded-[1.65rem] border border-stone-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-600 dark:bg-gray-700 md:grid-cols-[110px_88px_1fr_auto]">
                            <div class="rounded-[1.25rem] border-l-4 {{ $appointment['status_border_class'] }} bg-stone-50 px-3 py-4 text-stone-900 dark:bg-gray-800 dark:text-white">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-stone-500 dark:text-gray-400">{{ $appointment['full_date_label'] }}</p>
                                <p class="mt-3 text-3xl font-semibold leading-none">{{ $appointment['time_label'] }}</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.2em] text-stone-500 dark:text-gray-400">{{ $appointment['time_suffix'] }}</p>
                            </div>

                            <div class="flex items-center justify-center rounded-[1.25rem] bg-white ring-1 ring-stone-200 dark:bg-gray-800 dark:ring-gray-600">
                                <img src="{{ $appointment['image'] }}" alt="{{ $appointment['product_name'] }}" class="h-20 w-20 rounded-2xl object-contain p-2">
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-semibold text-stone-900 dark:text-white">{{ $appointment['customer_name'] }}</p>
                                        <p class="text-sm text-stone-600 dark:text-gray-300">{{ $appointment['product_name'] }}</p>
                                        <p class="mt-1 text-xs font-medium uppercase tracking-[0.18em] text-stone-400 dark:text-gray-400">ID: #{{ $appointment['product_id'] }}</p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-stone-700 dark:text-gray-100">{{ $appointment['relative_label'] }}</p>
                                        @if ($appointment['priority'])
                                            <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $appointment['priority_classes'] }}">
                                                {{ $appointment['priority'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-4 text-sm text-stone-500 dark:text-gray-400">
                                    <span>{{ $appointment['phone'] }}</span>
                                    <span>{{ $appointment['email'] }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $appointment['status_classes'] }}">
                                    {{ $appointment['status'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-stone-300 bg-stone-50 px-6 py-12 text-center text-stone-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            No appointments matched these filters.
                        </div>
                    @endforelse
                </div>

                <div class="pt-2">
                    {{ $appointments->links('livewire.pagination') }}
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-[1.65rem] border border-stone-200 bg-white p-5 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Today's Agenda</h3>
                            <p class="text-sm text-stone-500 dark:text-gray-400">{{ now('America/New_York')->format('D, M j') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-4">
                        @forelse ($todayAgenda as $appointment)
                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-3">
                                    <div class="pt-1 text-stone-400">&bull;</div>
                                    <div>
                                        <p class="text-sm font-semibold text-stone-900 dark:text-white">{{ $appointment['time_label'] }} {{ $appointment['time_suffix'] }}</p>
                                        <p class="mt-1 text-sm text-stone-700 dark:text-gray-200">{{ $appointment['customer_name'] }}</p>
                                        <p class="text-xs text-stone-500 dark:text-gray-400">{{ $appointment['product_name'] }}</p>
                                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400 dark:text-gray-500">ID: #{{ $appointment['product_id'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-stone-500 dark:text-gray-400">No appointments scheduled today.</p>
                        @endforelse
                    </div>

                    <button
                        type="button"
                        wire:click="setFilter('today')"
                        class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300"
                    >
                        View Full Agenda
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>

                <div class="rounded-[1.65rem] border border-stone-200 bg-white p-5 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                    <h3 class="text-lg font-semibold text-stone-900 dark:text-white">Quick Filters</h3>

                    <div class="mt-4 space-y-2">
                        <button type="button" wire:click="setFilter('upcoming')" class="flex w-full items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600">
                            <span>Upcoming</span>
                            <span class="font-semibold">{{ $stats['upcoming'] }}</span>
                        </button>
                        <button type="button" wire:click="setFilter('today')" class="flex w-full items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600">
                            <span>Today</span>
                            <span class="font-semibold">{{ $stats['today'] }}</span>
                        </button>
                        <button type="button" wire:click="setFilter('week')" class="flex w-full items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600">
                            <span>This Week</span>
                            <span class="font-semibold">{{ $stats['this_week'] }}</span>
                        </button>
                        <button type="button" wire:click="setFilter('month')" class="flex w-full items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600">
                            <span>This Month</span>
                            <span class="font-semibold">{{ $stats['this_month'] }}</span>
                        </button>
                        <button type="button" wire:click="setFilter('all')" class="flex w-full items-center justify-between rounded-2xl bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:bg-stone-100 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-600">
                            <span>All Appointments</span>
                            <span class="font-semibold">{{ $stats['all'] }}</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">Today</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $stats['today'] }}</p>
                    </div>
                    <div class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">Approaching</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $stats['approaching'] }}</p>
                    </div>
                    <div class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">This Week</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $stats['this_week'] }}</p>
                    </div>
                    <div class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">Far Out</p>
                        <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $stats['far_out'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
