<div class="space-y-6 pt-3" wire:poll.10s="refreshMonitor" x-data="{ currentTab: 'active' }">
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
            @if(($pageGroups ?? collect())->isNotEmpty())
                <div class="mt-6 rounded-3xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Live Page Groups</h3>
                            <p class="text-sm text-gray-500">Pages are grouped automatically from the live URLs, so identical watch pages roll up under the same watch name.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        @foreach($pageGroups as $group)
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Page Group</p>
                                        <p class="mt-2 text-lg font-semibold text-gray-950">{{ $group['label'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-3xl font-semibold text-gray-950">{{ $group['active_count'] }}</p>
                                        <p class="text-sm text-gray-600">Active {{ $group['visitor_label'] }}</p>
                                    </div>
                                </div>
                                @if(!empty($group['visitors']))
                                    <div class="mt-4 space-y-2">
                                        @foreach($group['visitors'] as $groupVisitor)
                                            <div class="rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                                <span class="font-medium text-gray-900">{{ $groupVisitor['label'] }}</span>
                                                <span class="text-gray-500"> on </span>
                                                <span>{{ \Illuminate\Support\Str::before($groupVisitor['current_path'] ?: 'Unknown page', '?') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($activeVisitors->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                    No one is browsing the website right now.
                </div>
            @else
                <div class="mt-8 flex flex-wrap gap-6">
                    @foreach($activeVisitors as $visitor)
                        <div
                            class="relative"
                            wire:key="active-visitor-{{ $visitor['monitor_key'] }}"
                            x-data="{
                                open: false,
                                closeTimer: null,
                                show() {
                                    if (this.closeTimer) {
                                        clearTimeout(this.closeTimer);
                                        this.closeTimer = null;
                                    }
                                    this.open = true;
                                },
                                hide() {
                                    this.closeTimer = setTimeout(() => {
                                        this.open = false;
                                    }, 120);
                                }
                            }"
                            @mouseenter="show()"
                            @mouseleave="hide()"
                        >
                            <div class="flex w-24 flex-col items-center gap-3">
                                <div
                                    class="relative flex h-20 w-20 items-center justify-center rounded-3xl border text-lg font-semibold shadow-sm transition {{ $visitor['is_returning'] ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-blue-200 bg-blue-50 text-blue-800' }}"
                                    :class="open ? '-translate-y-1' : ''"
                                >
                                    <span>{{ $visitor['initials'] }}</span>
                                    <span class="absolute -right-2 -top-2 inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-[10px] font-bold uppercase tracking-wide {{ $visitor['is_returning'] ? 'bg-amber-500 text-white' : 'bg-blue-500 text-white' }}">
                                        {{ $visitor['is_returning'] ? 'Back ' . max(1, $visitor['visit_count'] - 1) : 'New' }}
                                    </span>
                                </div>
                                <div class="text-center">
                                    @php
                                        $cardCurrentPath = $visitor['current_path'] ?: 'Unknown page';
                                    @endphp
                                    <p class="max-w-24 truncate text-sm font-semibold text-gray-900">
                                        {{ $visitor['display_name'] ?: 'Visitor' }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $visitor['is_returning'] ? 'Returning visitor' : 'First visit' }}
                                    </p>
                                    <p class="mt-1 max-w-24 truncate text-[11px] font-medium text-gray-700" title="{{ $cardCurrentPath }}">
                                        {{ $cardCurrentPath }}
                                    </p>
                                    @if(($visitor['active_page_count'] ?? 1) > 1)
                                        <p class="mt-1 text-[11px] font-medium text-blue-600">
                                            {{ $visitor['active_page_count'] }} open pages
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition.opacity.duration.150ms
                                class="fixed inset-x-0 top-0 z-[70] p-0"
                                @mouseenter="show()"
                                @mouseleave="hide()"
                            >
                                <div class="border-b border-white/35 p-5 shadow-2xl ring-1 ring-black/10" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.10)); backdrop-filter: blur(26px) saturate(180%); -webkit-backdrop-filter: blur(26px) saturate(180%);">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900">
                                                {{ $visitor['display_name'] ?: 'Anonymous visitor' }}
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ $visitor['is_known_customer'] ? 'Known from live chat' : 'Not yet identified' }}
                                            </p>
                                        </div>
                                        @if($visitor['is_returning'])
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Returned customer
                                            </span>
                                        @endif
                                    </div>

                                    <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Visit Date</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['visit_date_label'] ?: 'Unknown' }}</dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Visit Count</dt>
                                            <dd class="mt-1 text-gray-900">Visit #{{ $visitor['visit_count'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Status</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['status_label'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Location</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['location_label'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">IP Address</dt>
                                            <dd class="mt-1 break-all text-gray-900">{{ $visitor['ip_address'] ?: 'Unknown' }}</dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Current Page</dt>
                                            <dd class="mt-1 break-words">
                                                @php
                                                    $currentUrl = $visitor['current_url'] ?: null;
                                                    $currentLabel = $visitor['current_path'] ?: ($visitor['current_url'] ?? '');
                                                @endphp
                                                @if($visitor['current_url'])
                                                    <a href="{{ $currentUrl }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                        {{ $currentLabel }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-900">{{ $currentLabel ?: 'Unknown page' }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Open Pages</dt>
                                            <dd class="mt-1 space-y-1">
                                                @foreach(($visitor['active_pages'] ?? []) as $page)
                                                    <div class="break-words text-gray-900">
                                                        @if($page['url'])
                                                            <a href="{{ $page['url'] }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                                {{ $page['path'] }}
                                                            </a>
                                                        @else
                                                            <span>{{ $page['path'] }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Live Trail</dt>
                                            <dd class="mt-1 space-y-1">
                                                @forelse(($visitor['live_page_trail'] ?? []) as $trailPage)
                                                    <div class="break-words text-gray-900">
                                                        <span class="text-xs text-gray-500">{{ $trailPage['seen_at_label'] ?: '--' }}</span>
                                                        <span class="mx-1 text-gray-400">•</span>
                                                        @if($trailPage['url'])
                                                            <a href="{{ $trailPage['url'] }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                                {{ $trailPage['path'] }}
                                                            </a>
                                                        @else
                                                            <span>{{ $trailPage['path'] }}</span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="text-gray-500">No trail yet</div>
                                                @endforelse
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Landing Page</dt>
                                            @php
                                                $landingLabel = \Illuminate\Support\Str::before($visitor['landing_path'] ?: '', '?');
                                            @endphp
                                            <dd class="mt-1 break-words text-gray-900">
                                                {{ $landingLabel !== '' && $landingLabel !== $currentLabel ? $landingLabel : 'Same as current page' }}
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Time On Website</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['time_on_site'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl border border-white/35 px-4 py-3" style="background-color: rgba(255, 255, 255, 0.22);">
                                            <dt class="font-medium text-gray-500">Referrer</dt>
                                            <dd class="mt-1 break-words text-gray-900">{{ $visitor['referrer_url'] ?: 'Direct visit' }}</dd>
                                        </div>
                                    </dl>
                                </div>
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
