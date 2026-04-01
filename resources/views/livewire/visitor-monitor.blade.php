<div class="space-y-6 pt-3" wire:poll.10s="refreshMonitor" x-data="{ currentTab: 'live' }">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Active Now</p>
            <p class="mt-3 text-3xl font-semibold text-blue-950">{{ $stats['active_visitors'] }}</p>
            <p class="mt-2 text-sm text-blue-700">Visitors currently browsing your site.</p>
        </div>
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Known Customers</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-950">{{ $stats['known_visitors'] }}</p>
            <p class="mt-2 text-sm text-emerald-700">Visitors identified through live chat.</p>
        </div>
        <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Returning Visitors</p>
            <p class="mt-3 text-3xl font-semibold text-amber-950">{{ $stats['returning_visitors'] }}</p>
            <p class="mt-2 text-sm text-amber-700">Browsers that have come back more than once.</p>
        </div>
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Total Visits</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950">{{ $stats['total_visits'] }}</p>
            <p class="mt-2 text-sm text-gray-600">Saved visit history even after people leave.</p>
        </div>
    </div>

    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Visitor Activity</h2>
                <p class="mt-1 text-sm text-gray-500">Switch between live visitors and people who already left the website.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="currentTab = 'live'"
                    :class="currentTab === 'live' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                >
                    Live Right Now
                </button>
                <button
                    type="button"
                    @click="currentTab = 'left'"
                    :class="currentTab === 'left' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                >
                    Visited And Left
                </button>
            </div>
        </div>

        <div x-show="currentTab === 'live'" x-cloak>
            @if($activeVisitors->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                    No one is browsing the website right now.
                </div>
            @else
                <div class="mt-8 flex flex-wrap gap-6">
                    @foreach($activeVisitors as $visitor)
                        <div
                            class="relative"
                            wire:key="active-visitor-{{ $visitor['session_token'] }}"
                            x-data="{
                                open: false,
                                closeTimer: null,
                                positionLeft: 112,
                                positionTop: 8,
                                updatePosition() {
                                    if (! this.$refs.panel || ! this.$refs.trigger) {
                                        return;
                                    }

                                    const viewportPadding = 16;
                                    const gap = 16;
                                    const wrapperRect = this.$el.getBoundingClientRect();
                                    const triggerRect = this.$refs.trigger.getBoundingClientRect();
                                    const panelRect = this.$refs.panel.getBoundingClientRect();
                                    const rightPosition = triggerRect.width + gap;
                                    const leftPosition = -panelRect.width - gap;
                                    const bottomPosition = 8;
                                    const topPosition = -panelRect.height + triggerRect.height - 8;
                                    const fitsRight = triggerRect.right + gap + panelRect.width + viewportPadding <= window.innerWidth;
                                    const fitsLeft = triggerRect.left - gap - panelRect.width >= viewportPadding;
                                    const fitsBottom = triggerRect.top + bottomPosition + panelRect.height + viewportPadding <= window.innerHeight;
                                    const fitsTop = triggerRect.bottom + topPosition - viewportPadding >= 0;

                                    let nextLeft = fitsRight || !fitsLeft ? rightPosition : leftPosition;
                                    let nextTop = fitsBottom || !fitsTop ? bottomPosition : topPosition;

                                    const minLeft = viewportPadding - wrapperRect.left;
                                    const maxLeft = window.innerWidth - viewportPadding - wrapperRect.left - panelRect.width;
                                    const minTop = viewportPadding - wrapperRect.top;
                                    const maxTop = window.innerHeight - viewportPadding - wrapperRect.top - panelRect.height;

                                    this.positionLeft = Math.min(Math.max(nextLeft, minLeft), Math.max(minLeft, maxLeft));
                                    this.positionTop = Math.min(Math.max(nextTop, minTop), Math.max(minTop, maxTop));
                                },
                                show() {
                                    if (this.closeTimer) {
                                        clearTimeout(this.closeTimer);
                                        this.closeTimer = null;
                                    }
                                    this.open = true;
                                    this.$nextTick(() => this.updatePosition());
                                },
                                hide() {
                                    this.closeTimer = setTimeout(() => {
                                        this.open = false;
                                    }, 120);
                                }
                            }"
                            @mouseenter="show()"
                            @mouseleave="hide()"
                            @resize.window="open && updatePosition()"
                            @scroll.window="open && updatePosition()"
                        >
                            <div class="flex w-24 flex-col items-center gap-3" x-ref="trigger">
                                <div
                                    class="relative flex h-20 w-20 items-center justify-center rounded-3xl border text-lg font-semibold shadow-sm transition {{ $visitor['is_returning'] ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-blue-200 bg-blue-50 text-blue-800' }}"
                                    :class="open ? '-translate-y-1' : ''"
                                >
                                    <span>{{ $visitor['initials'] }}</span>
                                    <span class="absolute -right-2 -top-2 inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-[10px] font-bold uppercase tracking-wide {{ $visitor['is_returning'] ? 'bg-amber-500 text-white' : 'bg-blue-500 text-white' }}">
                                        {{ $visitor['is_returning'] ? 'Back' : 'New' }}
                                    </span>
                                </div>
                                <div class="text-center">
                                    <p class="max-w-24 truncate text-sm font-semibold text-gray-900">
                                        {{ $visitor['display_name'] ?: 'Visitor' }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $visitor['is_returning'] ? 'Returning visitor' : 'First visit' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                x-cloak
                                x-show="open"
                                x-transition.opacity.duration.150ms
                                class="absolute z-[70] w-[42rem]"
                                x-ref="panel"
                                :style="`left: ${positionLeft}px; top: ${positionTop}px;`"
                                @mouseenter="show()"
                                @mouseleave="hide()"
                            >
                                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-2xl ring-1 ring-black/5">
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
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Visit Date</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['visit_date_label'] ?: 'Unknown' }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Visit Count</dt>
                                            <dd class="mt-1 text-gray-900">Visit #{{ $visitor['visit_count'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Status</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['status_label'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Location</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['location_label'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">IP Address</dt>
                                            <dd class="mt-1 break-all text-gray-900">{{ $visitor['ip_address'] ?: 'Unknown' }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Current Page</dt>
                                            <dd class="mt-1 break-words">
                                                @if($visitor['current_url'])
                                                    <a href="{{ $visitor['current_url'] }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                        {{ $visitor['current_path'] ?: $visitor['current_url'] }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-900">{{ $visitor['current_path'] ?: 'Unknown page' }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Landing Page</dt>
                                            <dd class="mt-1 break-words text-gray-900">{{ $visitor['landing_path'] ?: 'Unknown page' }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                            <dt class="font-medium text-gray-500">Time On Website</dt>
                                            <dd class="mt-1 text-gray-900">{{ $visitor['time_on_site'] }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
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

        <div x-show="currentTab === 'left'" x-cloak>
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
                            @forelse($leftHistory as $visit)
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
                                            @if($visit['current_url'])
                                                <a href="{{ $visit['current_url'] }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline decoration-blue-300 underline-offset-2 hover:text-blue-900">
                                                    {{ $visit['current_path'] ?: $visit['current_url'] }}
                                                </a>
                                            @else
                                                <span class="text-gray-900">{{ $visit['current_path'] ?: 'Unknown page' }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Visit: {{ $visit['visit_date_label'] ?: 'Unknown' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            IP: {{ $visit['ip_address'] ?: 'Unknown IP' }}
                                        </div>
                                        <div class="mt-1 break-words text-xs text-gray-500">Landing: {{ $visit['landing_path'] ?: 'Unknown page' }}</div>
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
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No visitors have left the website yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($leftHistory->hasPages())
                <div class="mt-6">
                    {{ $leftHistory->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
