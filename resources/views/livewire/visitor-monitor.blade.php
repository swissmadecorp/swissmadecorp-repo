<div class="space-y-6 pt-3" wire:poll.3s="refreshMonitor" x-data="{ currentTab: 'active', expandedVisitor: null }">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <button
            type="button"
            @click="currentTab = 'active'"
            :class="currentTab === 'active' ? 'border-blue-300 ring-2 ring-blue-200' : 'border-blue-100'"
            class="rounded-3xl bg-blue-50 p-5 text-left shadow-sm transition"
        >
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Active Now</p>
            <p class="mt-3 text-3xl font-semibold text-blue-950">{{ $stats['active_visitors'] }}</p>
            <p class="mt-2 text-sm text-blue-700">Visitors currently browsing your site.</p>
        </button>
        <button
            type="button"
            @click="currentTab = 'known'"
            :class="currentTab === 'known' ? 'border-emerald-300 ring-2 ring-emerald-200' : 'border-emerald-100'"
            class="rounded-3xl bg-emerald-50 p-5 text-left shadow-sm transition"
        >
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Known Customers</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-950">{{ $stats['known_visitors'] }}</p>
            <p class="mt-2 text-sm text-emerald-700">Visitors identified through live chat.</p>
        </button>
        <button
            type="button"
            @click="currentTab = 'returning'"
            :class="currentTab === 'returning' ? 'border-amber-300 ring-2 ring-amber-200' : 'border-amber-100'"
            class="rounded-3xl bg-amber-50 p-5 text-left shadow-sm transition"
        >
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Returning Visitors</p>
            <p class="mt-3 text-3xl font-semibold text-amber-950">{{ $stats['returning_visitors'] }}</p>
            <p class="mt-2 text-sm text-amber-700">Browsers that have come back more than once.</p>
        </button>
        <button
            type="button"
            @click="currentTab = 'total'"
            :class="currentTab === 'total' ? 'border-gray-300 ring-2 ring-gray-200' : 'border-gray-200'"
            class="rounded-3xl bg-white p-5 text-left shadow-sm transition"
        >
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Total Visits</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950">{{ $stats['total_visits'] }}</p>
            <p class="mt-2 text-sm text-gray-600">Saved visit history even after people leave.</p>
        </button>
    </div>

    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Visitor Activity</h2>
            <p class="mt-1 text-sm text-gray-500">Choose one of the summary cards above to change what you are viewing.</p>
        </div>

        <div class="mt-6">
            <label for="visitor-monitor-search" class="text-sm font-medium text-gray-700">Search By IP, current page, landing page, or referrer</label>
            <div class="mt-2">
                <input
                    id="visitor-monitor-search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Example: 66.249.79.192 /product-details google.com"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-blue-300 focus:ring-2 focus:ring-blue-100"
                >
            </div>
            <p class="mt-2 text-xs text-gray-500">The search checks IP address, current page, landing page, and referrer across the visitor monitor.</p>
        </div>

        <div x-show="currentTab === 'active'" x-cloak>
            @if($activeVisitors->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                    No one is browsing the website right now.
                </div>
            @else
                <div class="mt-8 grid gap-5 xl:grid-cols-2">
                    @foreach($activeVisitors as $visitor)
                        <div
                            wire:key="active-visitor-{{ $visitor['monitor_key'] }}"
                            @click="expandedVisitor = expandedVisitor === '{{ $visitor['monitor_key'] }}' ? null : '{{ $visitor['monitor_key'] }}'"
                            :class="expandedVisitor === '{{ $visitor['monitor_key'] }}' ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-200'"
                            class="min-w-0 cursor-pointer rounded-3xl border bg-white p-5 shadow-sm transition"
                        >
                            @php
                                $currentUrl = $visitor['current_url'] ?: null;
                                $currentLabel = $visitor['current_path'] ?: ($visitor['current_url'] ?? '');
                                $landingLabel = \Illuminate\Support\Str::before($visitor['landing_path'] ?: '', '?');
                            @endphp

                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex h-16 w-16 items-center justify-center rounded-3xl border text-lg font-semibold {{ $visitor['is_returning'] ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-blue-200 bg-blue-50 text-blue-800' }}">
                                        <span>{{ $visitor['initials'] }}</span>
                                        <span class="absolute -right-2 -top-2 inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-[10px] font-bold uppercase tracking-wide {{ $visitor['is_returning'] ? 'bg-amber-500 text-white' : 'bg-blue-500 text-white' }}">
                                            {{ $visitor['is_returning'] ? 'Back ' . max(1, $visitor['visit_count'] - 1) : 'New' }}
                                        </span>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $visitor['visitor_label'] }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ $visitor['display_name'] ?: 'Anonymous visitor' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $visitor['is_known_customer'] ? 'Known from live chat' : 'Not yet identified' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $visitor['status_label'] }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $visitor['time_on_site'] }}</div>
                                    <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                                        <span x-text="expandedVisitor === '{{ $visitor['monitor_key'] }}' ? 'Hide details' : 'Show details'"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 min-w-0 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm">
                                <div class="font-medium text-gray-500">Current Page</div>
                                <div class="mt-1 min-w-0">
                                    @if($currentUrl)
                                        <a
                                            href="{{ $currentUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            @click.stop
                                            title="{{ $currentLabel ?: 'Unknown page' }}"
                                            class="block truncate font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900"
                                        >
                                            {{ $currentLabel ?: 'Unknown page' }}
                                        </a>
                                    @else
                                        <span class="block truncate text-gray-900" title="{{ $currentLabel ?: 'Unknown page' }}">{{ $currentLabel ?: 'Unknown page' }}</span>
                                    @endif
                                </div>
                            </div>

                            <div x-show="expandedVisitor === '{{ $visitor['monitor_key'] }}'" x-cloak>
                                <dl class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3 text-sm">
                                    <div class="min-w-0 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <dt class="font-medium text-gray-500">Previous Page</dt>
                                        <dd class="mt-1 min-w-0">
                                            @if($visitor['previous_url'])
                                                <a
                                                    href="{{ $visitor['previous_url'] }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    @click.stop
                                                    title="{{ $visitor['previous_path'] ?: 'Unknown page' }}"
                                                    class="block truncate font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900"
                                                >
                                                    {{ $visitor['previous_path'] ?: 'Unknown page' }}
                                                </a>
                                            @elseif($visitor['previous_path'])
                                                <span class="block truncate text-gray-900" title="{{ $visitor['previous_path'] }}">{{ $visitor['previous_path'] }}</span>
                                            @else
                                                <span class="text-gray-500">No page change yet</span>
                                            @endif
                                        </dd>
                                        @if($visitor['previous_page_changed_label'])
                                            <dd class="mt-1 text-xs text-gray-500">Left at {{ $visitor['previous_page_changed_label'] }}</dd>
                                        @endif
                                    </div>
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <dt class="font-medium text-gray-500">Location</dt>
                                        <dd class="mt-1 text-gray-900">{{ $visitor['location_label'] }}</dd>
                                    </div>
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <dt class="font-medium text-gray-500">IP Address</dt>
                                        <dd class="mt-1 break-all text-gray-900">{{ $visitor['ip_address'] ?: 'Unknown' }}</dd>
                                    </div>
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <dt class="font-medium text-gray-500">Landing Page</dt>
                                        <dd class="mt-1 break-words text-gray-900">
                                            {{ $landingLabel !== '' && $landingLabel !== $currentLabel ? $landingLabel : 'Same as current page' }}
                                        </dd>
                                    </div>
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <dt class="font-medium text-gray-500">Referrer</dt>
                                        <dd class="mt-1 break-words text-gray-900">{{ $visitor['referrer_url'] ?: 'Direct visit' }}</dd>
                                    </div>
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <dt class="font-medium text-gray-500">Visit Details</dt>
                                        <dd class="mt-1 text-gray-900">Visit #{{ $visitor['visit_count'] }}</dd>
                                        <dd class="mt-1 text-xs text-gray-500">{{ $visitor['visit_date_label'] ?: 'Unknown' }}</dd>
                                    </div>
                                    @if(($visitor['active_page_count'] ?? 1) > 1)
                                        <div class="min-w-0 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 md:col-span-2 xl:col-span-3">
                                            <dt class="font-medium text-gray-500">Open Pages</dt>
                                            <dd class="mt-2 grid gap-2 md:grid-cols-2">
                                                @foreach(($visitor['active_pages'] ?? []) as $page)
                                                    <div class="min-w-0 rounded-xl bg-white px-3 py-2 text-gray-900">
                                                        @if($page['url'])
                                                            <a
                                                                href="{{ $page['url'] }}"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                @click.stop
                                                                title="{{ $page['path'] }}"
                                                                class="block truncate font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900"
                                                            >
                                                                {{ $page['path'] }}
                                                            </a>
                                                        @else
                                                            <span class="block truncate" title="{{ $page['path'] }}">{{ $page['path'] }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div x-show="currentTab === 'known'" x-cloak>
            <div class="mt-6 overflow-hidden rounded-3xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                                <th class="px-4 py-3">Visitor</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Current / Landing</th>
                                <th class="px-4 py-3">Referrer</th>
                                <th class="px-4 py-3">Time On Site</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($knownCustomers as $visit)
                                <tr class="align-top" wire:key="left-visitor-{{ $visit['id'] }}">
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900">{{ $visit['display_name'] ?: 'Anonymous visitor' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Visit #{{ $visit['visit_count'] }}</div>
                                        @if($visit['is_returning'])
                                            <div class="mt-2 text-xs font-medium text-amber-700">Returning visitor</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $visit['location_label'] }}</td>
                                    <td class="px-4 py-4 text-gray-700">
                                        <div class="break-words font-medium">
                                            @php
                                                $leftCurrentUrl = $visit['current_url'] ? \Illuminate\Support\Str::before($visit['current_url'], '?') : null;
                                                $leftCurrentLabel = \Illuminate\Support\Str::before($visit['current_path'] ?: ($visit['current_url'] ?? ''), '?');
                                                $leftLandingLabel = \Illuminate\Support\Str::before($visit['landing_path'] ?: '', '?');
                                            @endphp
                                            @if($visit['current_url'])
                                                <a href="{{ $leftCurrentUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                    {{ $leftCurrentLabel }}
                                                </a>
                                            @else
                                                <span class="text-gray-900">{{ $leftCurrentLabel ?: 'Unknown page' }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Visit: {{ $visit['visit_date_label'] ?: 'Unknown' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            IP: {{ $visit['ip_address'] ?: 'Unknown IP' }}
                                        </div>
                                        @if($leftLandingLabel !== '' && $leftLandingLabel !== $leftCurrentLabel)
                                            <div class="mt-1 break-words text-xs text-gray-500">Landing: {{ $leftLandingLabel }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-xs break-words text-gray-700">{{ $visit['referrer_url'] ?: 'Direct visit' }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $visit['time_on_site'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $visit['status_label'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No known customers have been tracked yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($knownCustomers->hasPages())
                <div class="mt-6">
                    {{ $knownCustomers->links() }}
                </div>
            @endif
        </div>

        <div x-show="currentTab === 'returning'" x-cloak>
            <div class="mt-6 overflow-hidden rounded-3xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                                <th class="px-4 py-3">Visitor</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Current / Landing</th>
                                <th class="px-4 py-3">Referrer</th>
                                <th class="px-4 py-3">Time On Site</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($returningVisitors as $visit)
                                <tr class="align-top" wire:key="returning-visitor-{{ $visit['id'] }}">
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900">{{ $visit['display_name'] ?: 'Anonymous visitor' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Visit #{{ $visit['visit_count'] }}</div>
                                        <div class="mt-2 text-xs font-medium text-amber-700">Returning visitor</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $visit['location_label'] }}</td>
                                    <td class="px-4 py-4 text-gray-700">
                                        <div class="break-words font-medium">
                                            @php
                                                $returningCurrentUrl = $visit['current_url'] ? \Illuminate\Support\Str::before($visit['current_url'], '?') : null;
                                                $returningCurrentLabel = \Illuminate\Support\Str::before($visit['current_path'] ?: ($visit['current_url'] ?? ''), '?');
                                                $returningLandingLabel = \Illuminate\Support\Str::before($visit['landing_path'] ?: '', '?');
                                            @endphp
                                            @if($visit['current_url'])
                                                <a href="{{ $returningCurrentUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                    {{ $returningCurrentLabel }}
                                                </a>
                                            @else
                                                <span class="text-gray-900">{{ $returningCurrentLabel ?: 'Unknown page' }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Visit: {{ $visit['visit_date_label'] ?: 'Unknown' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            IP: {{ $visit['ip_address'] ?: 'Unknown IP' }}
                                        </div>
                                        @if($returningLandingLabel !== '' && $returningLandingLabel !== $returningCurrentLabel)
                                            <div class="mt-1 break-words text-xs text-gray-500">Landing: {{ $returningLandingLabel }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-xs break-words text-gray-700">{{ $visit['referrer_url'] ?: 'Direct visit' }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $visit['time_on_site'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $visit['status_label'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No returning visitors have been tracked yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($returningVisitors->hasPages())
                <div class="mt-6">
                    {{ $returningVisitors->links() }}
                </div>
            @endif
        </div>

        <div x-show="currentTab === 'total'" x-cloak>
            <div class="mt-6 overflow-hidden rounded-3xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                                <th class="px-4 py-3">Visitor</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Current / Landing</th>
                                <th class="px-4 py-3">Referrer</th>
                                <th class="px-4 py-3">Time On Site</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($totalVisits as $visit)
                                <tr class="align-top" wire:key="total-visitor-{{ $visit['id'] }}">
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-900">{{ $visit['display_name'] ?: 'Anonymous visitor' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Visit #{{ $visit['visit_count'] }}</div>
                                        @if($visit['is_returning'])
                                            <div class="mt-2 text-xs font-medium text-amber-700">Returning visitor</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $visit['location_label'] }}</td>
                                    <td class="px-4 py-4 text-gray-700">
                                        <div class="break-words font-medium">
                                            @php
                                                $totalCurrentUrl = $visit['current_url'] ? \Illuminate\Support\Str::before($visit['current_url'], '?') : null;
                                                $totalCurrentLabel = \Illuminate\Support\Str::before($visit['current_path'] ?: ($visit['current_url'] ?? ''), '?');
                                                $totalLandingLabel = \Illuminate\Support\Str::before($visit['landing_path'] ?: '', '?');
                                            @endphp
                                            @if($visit['current_url'])
                                                <a href="{{ $totalCurrentUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                    {{ $totalCurrentLabel }}
                                                </a>
                                            @else
                                                <span class="text-gray-900">{{ $totalCurrentLabel ?: 'Unknown page' }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Visit: {{ $visit['visit_date_label'] ?: 'Unknown' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            IP: {{ $visit['ip_address'] ?: 'Unknown IP' }}
                                        </div>
                                        @if($totalLandingLabel !== '' && $totalLandingLabel !== $totalCurrentLabel)
                                            <div class="mt-1 break-words text-xs text-gray-500">Landing: {{ $totalLandingLabel }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-xs break-words text-gray-700">{{ $visit['referrer_url'] ?: 'Direct visit' }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $visit['time_on_site'] }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $visit['status_label'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No visits have been tracked yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($totalVisits->hasPages())
                <div class="mt-6">
                    {{ $totalVisits->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
