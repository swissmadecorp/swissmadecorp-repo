<div class="mt-4 space-y-8 rounded-[34px] bg-[#f7f3ec] p-5 md:p-7" wire:poll.1s.visible="refreshMonitor">
    @php
        $displayFieldIcon = function (string $field): string {
            $iconMap = [
                'Retail' => '#',
                'Cost' => '$',
                'Title' => 'T',
                'Image' => 'I',
                'Serial #' => '#',
            ];

            return $iconMap[$field] ?? '+';
        };

        $displayFieldTone = function (string $field): string {
            return match ($field) {
                'Retail', 'Cost' => 'border-[#e8dcc4] bg-[#f6efe3] text-[#8b6b2f]',
                'Title' => 'border-[#e5e1d6] bg-[#f3f0ea] text-[#5a5349]',
                'Image', 'Serial #' => 'border-[#dddff1] bg-[#f0f1fb] text-[#58618d]',
                default => 'border-[#e5e1d6] bg-[#f3f0ea] text-[#5a5349]',
            };
        };

        $recentEventsByDate = $recentEvents->groupBy('display_date_key');
    @endphp

    <div class="flex flex-col gap-4 border-b border-[#e9e1d5] pb-7 pt-2 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="font-times max-w-3xl text-lg text-[#6f6557]">
                Track real-time edits and saves across your product catalog.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-3 rounded-full border border-[#e3dacd] bg-[#fbfaf7] px-5 py-3 text-sm text-[#6a6e45] shadow-[0_14px_32px_-28px_rgba(58,44,28,0.55)]">
                <span class="h-2.5 w-2.5 rounded-full bg-[#8a936d]"></span>
                <span class="font-medium">Active Now</span>
            </div>

            <div class="rounded-full border border-[#e3dacd] bg-white px-5 py-3 text-sm text-[#5f564a] shadow-[0_14px_32px_-28px_rgba(58,44,28,0.5)]">
                {{ $stats['active'] }} {{ \Illuminate\Support\Str::plural('editor', $stats['active']) }}
            </div>
        </div>
    </div>

    @if($activeSessions->isNotEmpty())
        <section class="space-y-4">
            <div>
                <h2 class="font-times text-2xl tracking-[-0.02em] text-[#2d251d]">Active Now</h2>
                <p class="mt-1 text-sm text-[#7a7062]">Current create and update sessions happening in the product editor.</p>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach($activeSessions as $session)
                    @php
                        $sessionInitials = collect(preg_split('/\s+/', trim($session['user_name'] ?? '')))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                            ->implode('');
                    @endphp

                    <article class="rounded-[28px] border {{ $session['mode'] === 'create' ? 'border-[#dce5d9] bg-[#fbfcf9]' : 'border-[#eadfcf] bg-[#fdfaf4]' }} p-4 shadow-[0_20px_40px_-34px_rgba(58,44,28,0.5)]">
                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                            <div class="flex items-start gap-4">
                                @if($session['mode'] === 'update')
                                    <img
                                        src="{{ $session['product_image'] }}"
                                        alt="{{ $session['product_title'] ?: 'Product image' }}"
                                        class="h-16 w-16 rounded-[18px] border border-[#e6ddd0] object-cover"
                                    >
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-[18px] border border-[#dce5d9] bg-[#eef3ea] text-[10px] font-semibold uppercase tracking-[0.2em] text-[#73805f]">
                                        New
                                    </div>
                                @endif

                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full border border-[#e6ddd0] bg-white text-sm font-semibold text-[#463d31]">
                                            {{ $sessionInitials ?: 'AU' }}
                                        </div>
                                        <h3 class="text-lg font-medium text-[#2f271f]">{{ $session['user_name'] }}</h3>
                                        <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $session['mode'] === 'create' ? 'border-[#dce5d9] bg-[#eef3ea] text-[#73805f]' : 'border-[#eadfcf] bg-[#f8efe2] text-[#9a7d41]' }}">
                                            {{ $session['mode_label'] }}
                                        </span>
                                    </div>

                                    @if($session['mode'] === 'update')
                                        <p class="mt-2 text-sm text-[#332b22]">Product #{{ $session['product_id'] }}: {{ $session['product_title'] ?: 'Untitled product' }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.16em] text-[#8a7f71]">Last heartbeat {{ $session['last_seen_label'] }}</p>
                                    @else
                                        <p class="mt-2 text-sm text-[#5f564a]">This user is building a new item. Field-by-field typing stays hidden until the product is saved.</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.16em] text-[#8a7f71]">Last heartbeat {{ $session['last_seen_label'] }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($session['mode'] === 'update')
                                <div class="md:max-w-sm">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#8a7f71]">Changed Fields</p>
                                    @if(count($session['changed_fields']) > 0)
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($session['changed_fields'] as $field)
                                                <span class="rounded-full border border-[#e5ddd1] bg-white px-3 py-1 text-xs font-medium text-[#5b5348]">
                                                    {{ $field }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-2 text-sm text-[#7f7466]">No field changes detected yet.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="font-times text-3xl tracking-[-0.02em] text-[#2b231b]">Recent Activity</h2>
                <p class="mt-2 text-sm text-[#6f6557]">Edits are grouped by product and admin to keep activity clear and accountable.</p>
            </div>

            <div class="inline-flex items-center gap-3 rounded-2xl border border-[#e3dacd] bg-[#fbfaf7] px-5 py-3 text-sm text-[#5f564a] shadow-[0_14px_32px_-28px_rgba(58,44,28,0.45)]">
                <svg class="h-5 w-5 text-[#8b816f]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3.75" y="4.75" width="16.5" height="15.5" rx="2.5"></rect>
                    <path d="M8 3.75V7"></path>
                    <path d="M16 3.75V7"></path>
                    <path d="M3.75 9.5H20.25"></path>
                </svg>
                <span>{{ $recentEventsByDate->count() }} {{ \Illuminate\Support\Str::plural('date', $recentEventsByDate->count()) }}</span>
            </div>
        </div>

        @if($recentEvents->isEmpty())
            <div class="rounded-[30px] border border-dashed border-[#ded4c8] bg-[#fbfaf7] px-6 py-14 text-center text-sm text-[#7b7163]">
                No product save activity has been logged yet.
            </div>
        @else
            <div class="space-y-6">
                @foreach($recentEventsByDate as $dateKey => $dateGroups)
                    @php
                        $dateLabel = $dateGroups->first()['display_date_label'] ?? $dateKey;
                        $isExpanded = in_array($dateKey, $expandedDates, true);
                    @endphp

                    <section class="rounded-[30px] border border-[#e2d8cc] bg-[#fbf8f2] shadow-[0_20px_46px_-40px_rgba(58,44,28,0.55)]">
                        <button
                            type="button"
                            wire:click="toggleDateSection('{{ $dateKey }}')"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                        >
                            <div>
                                <p class="font-times text-[1.45rem] leading-none text-[#2b231b]">{{ $dateLabel }}</p>
                                <p class="mt-1 text-sm text-[#7a7062]">
                                    {{ $dateGroups->count() }} {{ \Illuminate\Support\Str::plural('activity card', $dateGroups->count()) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3 text-[#7b705f]">
                                <span class="rounded-full border border-[#e3dacd] bg-white px-3 py-1 text-xs uppercase tracking-[0.18em]">
                                    {{ $isExpanded ? 'Expanded' : 'Collapsed' }}
                                </span>
                                <svg class="h-5 w-5 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m5 7.5 5 5 5-5"></path>
                                </svg>
                            </div>
                        </button>

                        @if($isExpanded)
                            <div class="space-y-6 border-t border-[#ebe1d5] px-3 pb-3 pt-4 md:px-4 md:pb-4">
                                @foreach($dateGroups as $group)
                                    @php
                                        $groupInitials = collect(preg_split('/\s+/', trim($group['user_name'] ?? '')))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                            ->implode('');
                                    @endphp

                                    <article class="rounded-[30px] border border-[#e7ddd1] bg-[#fcfbf8] p-3 shadow-[0_24px_54px_-40px_rgba(58,44,28,0.55)] md:p-4">
                                        <div class="overflow-hidden rounded-[26px] border border-[#eee5d8] bg-white">
                                            <div class="flex flex-col gap-4 px-4 py-4 lg:flex-row lg:items-center lg:divide-x lg:divide-[#ece2d6]">
                                                <div class="flex min-w-0 flex-[1.35] items-center gap-3 lg:pr-5">
                                                    <img
                                                        src="{{ $group['product_image'] }}"
                                                        alt="{{ $group['product_title'] ?: 'Product image' }}"
                                                        class="h-20 w-32 rounded-[16px] border border-[#e7ddd1] object-cover shadow-[0_12px_26px_-22px_rgba(58,44,28,0.45)]"
                                                    >

                                                    <div class="min-w-0">
                                                        <h3 class="font-times truncate text-[1.6rem] leading-none tracking-[-0.03em] text-[#261e17]">
                                                            {{ $group['product_title'] ?: 'Untitled product' }}
                                                        </h3>
                                                        <p class="font-times mt-1 text-[1rem] text-[#83774d]">#{{ $group['product_id'] }}</p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-[0.8] items-center gap-3 lg:px-5">
                                                    <div class="flex h-[3.75rem] w-[3.75rem] items-center justify-center overflow-hidden rounded-full border border-[#e7ddd1] bg-[#f5f0e8] text-base font-semibold text-[#443a30]">
                                                        {{ $groupInitials ?: 'AU' }}
                                                    </div>

                                                    <div>
                                                        <p class="font-times text-[1rem] text-[#2f271f]">{{ $group['user_name'] }}</p>
                                                        <p class="text-sm text-[#7d7366]">Administrator</p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-[0.6] items-center gap-3 lg:px-5">
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f3efe6] text-[#8a7a3a]">
                                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                                            <path d="M8 4.75h8l3 3v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 5 18.75v-11l3-3Z"></path>
                                                        </svg>
                                                    </div>

                                                    <div>
                                                        <p class="font-times text-[1rem] text-[#697248]">
                                                            {{ $group['total_saves'] }} {{ \Illuminate\Support\Str::plural('save', $group['total_saves']) }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-[0.55] flex-col lg:px-5">
                                                    <p class="text-sm text-[#7d7366]">Last updated</p>
                                                    <p class="font-times mt-1 text-[1rem] leading-none tracking-[-0.03em] text-[#2b231b]">
                                                        {{ $group['last_updated_time'] }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="border-t border-[#ece2d6] bg-[#fcfbf8]">
                                                <div class="flex flex-col lg:flex-row">
                                                    <div class="border-b border-[#ece2d6] px-4 py-3 lg:w-40 lg:border-b-0 lg:border-r">
                                                        <p class="text-[0.95rem] uppercase tracking-[0.08em] text-[#3f382f]">Summary</p>
                                                    </div>

                                                    <div class="flex-1 px-4 py-3">
                                                        <div class="flex flex-wrap gap-3">
                                                            @foreach($group['field_summary'] as $summary)
                                                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm {{ $displayFieldTone($summary['field']) }}">
                                                                    <span class="flex h-5 w-5 items-center justify-center rounded-full border border-current/20 bg-white/50 text-[11px] font-semibold">
                                                                        {{ $displayFieldIcon($summary['field']) }}
                                                                    </span>
                                                                    {{ $summary['label'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="relative">
                                                    <div class="pointer-events-none absolute bottom-0 left-[1.875rem] top-0 w-px bg-[#d8cfbf]"></div>

                                                    @foreach($group['timeline'] as $timelineEvent)
                                                        <div class="relative grid items-center gap-3 border-t border-[#ece2d6] py-3 pl-[4.5rem] pr-4" style="grid-template-columns: 6.5rem 8rem minmax(0, 1fr);">
                                                            <span class="absolute left-[1.5rem] top-1/2 h-3 w-3 -translate-y-1/2 rounded-full bg-[#889163] ring-4 ring-[#fcfbf8]"></span>

                                                            <div class="font-times truncate pr-2 text-[0.95rem] text-[#6f6557]">
                                                                {{ $timelineEvent['time_label'] }}
                                                            </div>

                                                            <div class="font-times truncate pr-2 text-[1.05rem] text-[#2f271f]">
                                                                {{ $timelineEvent['action'] === 'created' ? 'Created' : (count($timelineEvent['changed_fields']) > 0 ? implode(', ', $timelineEvent['changed_fields']) : 'Updated') }}
                                                            </div>

                                                            <div class="truncate text-sm text-[#6f6557]">
                                                                @if($timelineEvent['action'] === 'created')
                                                                    Product created and saved.
                                                                @elseif(count($timelineEvent['changed_fields']) > 0)
                                                                    {{ implode(' - ', collect($timelineEvent['changed_fields'])->map(fn ($field) => $field . ' changed')->all()) }}
                                                                @else
                                                                    Saved without tracked field differences.
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    </section>
</div>
