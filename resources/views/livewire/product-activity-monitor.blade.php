<div class="space-y-7 rounded-[34px] bg-[#f5f1ea] p-4 md:p-5" wire:poll.1s.visible="refreshMonitor">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-500">Product Activity</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-stone-900">Admin product monitor</h1>
            <p class="mt-2 max-w-3xl text-sm text-stone-600">Live editor presence stays on the left. Saved product activity is grouped into premium product-and-admin cards with their own save timelines on the right.</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-stone-600 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            Realtime monitor
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[28px] border border-stone-200 bg-[#f0ede6] p-5 shadow-[0_16px_36px_-28px_rgba(63,48,32,0.45)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500">Active Editors</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $stats['active'] }}</p>
            <p class="mt-2 text-sm text-stone-600">Admins currently inside the product editor.</p>
        </div>
        <div class="rounded-[28px] border border-[#dbe5d8] bg-[#eef2ea] p-5 shadow-[0_16px_36px_-28px_rgba(63,48,32,0.42)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#66745a]">Creating</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $stats['creating'] }}</p>
            <p class="mt-2 text-sm text-stone-600">Users starting brand-new items right now.</p>
        </div>
        <div class="rounded-[28px] border border-[#eadbc0] bg-[#f7efe1] p-5 shadow-[0_16px_36px_-28px_rgba(63,48,32,0.42)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-[#9a7b39]">Updating</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $stats['updating'] }}</p>
            <p class="mt-2 text-sm text-stone-600">Users editing existing products right now.</p>
        </div>
        <div class="rounded-[28px] border border-stone-200 bg-white p-5 shadow-[0_16px_36px_-28px_rgba(63,48,32,0.45)]">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500">Saved Today</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">{{ $stats['saved_today'] }}</p>
            <p class="mt-2 text-sm text-stone-600">Create and update events logged today.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.82fr,1.18fr]">
        <section class="rounded-[32px] border border-stone-200 bg-white p-6 shadow-[0_20px_50px_-36px_rgba(63,48,32,0.42)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-stone-900">Active Product Editors</h2>
                    <p class="mt-1 text-sm text-stone-500">Create sessions stay private until the product is saved. Update sessions show the fields that have been touched.</p>
                </div>
                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                    Live
                </span>
            </div>

            @if($activeSessions->isEmpty())
                <div class="mt-6 rounded-[28px] border border-dashed border-stone-300 bg-[#f7f3ec] px-6 py-12 text-center text-sm text-stone-500">
                    No admin is editing a product right now.
                </div>
            @else
                <div class="mt-6 grid gap-4">
                    @foreach($activeSessions as $session)
                        @php
                            $sessionInitials = collect(preg_split('/\s+/', trim($session['user_name'] ?? '')))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                ->implode('');
                        @endphp
                        <article class="rounded-[28px] border {{ $session['mode'] === 'create' ? 'border-[#dbe5d8] bg-[#f8fbf7]' : 'border-[#eadbc0] bg-[#fcf8f1]' }} p-5 shadow-[0_16px_36px_-30px_rgba(63,48,32,0.34)]">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex items-start gap-4">
                                    @if($session['mode'] === 'update')
                                        <img
                                            src="{{ $session['product_image'] }}"
                                            alt="{{ $session['product_title'] ?: 'Product image' }}"
                                            class="h-20 w-20 rounded-[22px] border border-stone-200 object-cover shadow-[0_14px_28px_-22px_rgba(60,47,33,0.42)]"
                                        >
                                    @else
                                        <div class="flex h-20 w-20 items-center justify-center rounded-[22px] border border-[#dbe5d8] bg-[#eef2ea] text-xs font-semibold uppercase tracking-[0.2em] text-[#66745a] shadow-[0_14px_28px_-22px_rgba(60,47,33,0.42)]">
                                            New
                                        </div>
                                    @endif

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-full border border-stone-200 bg-white text-sm font-semibold text-stone-700 shadow-sm">
                                                {{ $sessionInitials ?: 'AU' }}
                                            </div>
                                            <h3 class="text-lg font-semibold text-stone-900">{{ $session['user_name'] }}</h3>
                                            <span class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $session['mode'] === 'create' ? 'border-[#dbe5d8] bg-[#eef2ea] text-[#66745a]' : 'border-[#eadbc0] bg-[#f7efe1] text-[#9a7b39]' }}">
                                                {{ $session['mode_label'] }}
                                            </span>
                                        </div>

                                        @if($session['mode'] === 'update')
                                            <p class="mt-3 text-sm text-stone-900">
                                                Product #{{ $session['product_id'] }}: {{ $session['product_title'] ?: 'Untitled product' }}
                                            </p>
                                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-500">Last heartbeat {{ $session['last_seen_label'] }}</p>
                                        @else
                                            <p class="mt-3 text-sm text-stone-700">This user is building a new item. Field-by-field typing stays hidden until the product is saved.</p>
                                            <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-500">Last heartbeat {{ $session['last_seen_label'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if($session['mode'] === 'update')
                                    <div class="md:max-w-xs">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Changed Fields</p>
                                        @if(count($session['changed_fields']) > 0)
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($session['changed_fields'] as $field)
                                                    <span class="rounded-full border border-stone-200 bg-white px-3 py-1 text-xs font-medium text-stone-700 shadow-sm">{{ $field }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="mt-2 text-sm text-stone-500">No field changes detected yet.</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-[32px] border border-stone-200 bg-white p-6 shadow-[0_20px_50px_-36px_rgba(63,48,32,0.42)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-stone-900">Recent Saved Activity</h2>
                    <p class="mt-1 text-sm text-stone-500">Each product and admin pair gets its own card, with every save shown underneath in time order.</p>
                </div>
                <span class="rounded-full border border-stone-200 bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-600">
                    Product + User
                </span>
            </div>

            @if($recentEvents->isEmpty())
                <div class="mt-6 rounded-[28px] border border-dashed border-stone-300 bg-[#f7f3ec] px-6 py-12 text-center text-sm text-stone-500">
                    No product save activity has been logged yet.
                </div>
            @else
                <div class="mt-6 space-y-4">
                    @foreach($recentEvents as $group)
                        @php
                            $groupInitials = collect(preg_split('/\s+/', trim($group['user_name'] ?? '')))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                ->implode('');
                        @endphp
                        <article class="overflow-hidden rounded-[30px] border border-stone-200 bg-[#f8f4ec] shadow-[0_20px_46px_-34px_rgba(71,53,34,0.42)]">
                            <div class="p-5">
                            <div class="flex flex-col gap-5">
                                <div class="flex items-start gap-4">
                                    <img
                                        src="{{ $group['product_image'] }}"
                                        alt="{{ $group['product_title'] ?: 'Product image' }}"
                                        class="h-20 w-20 rounded-[22px] border border-stone-200 object-cover shadow-[0_14px_28px_-22px_rgba(63,48,32,0.42)]"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-xl font-semibold tracking-tight text-stone-900">{{ $group['product_title'] ?: 'Untitled product' }}</p>
                                            <span class="rounded-full border border-stone-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-500 shadow-sm">
                                                Product #{{ $group['product_id'] }}
                                            </span>
                                        </div>

                                        <div class="mt-3 flex flex-wrap items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-full border border-stone-200 bg-white text-sm font-semibold text-stone-700 shadow-sm">
                                                {{ $groupInitials ?: 'AU' }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-stone-600">
                                                <p class="font-medium text-stone-900">{{ $group['user_name'] }}</p>
                                                <p>{{ $group['total_saves_label'] }}</p>
                                                <p>Last updated {{ $group['last_updated_time'] }}</p>
                                                <p class="text-stone-500">{{ $group['last_updated_label'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(collect($group['field_summary'])->isNotEmpty())
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Change Summary</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($group['field_summary'] as $summary)
                                                <span class="rounded-full border border-stone-200 bg-white px-3 py-1 text-xs font-medium text-stone-700 shadow-sm">
                                                    {{ $summary['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Save Timeline</p>
                                    <div class="mt-4 space-y-3 border-l border-stone-200 pl-4">
                                        @foreach($group['timeline'] as $timelineEvent)
                                            <div class="relative flex gap-4 rounded-[24px] border border-stone-200 bg-white px-4 py-3 shadow-[0_12px_26px_-22px_rgba(63,48,32,0.38)]">
                                                <span class="absolute -left-[1.18rem] top-5 h-3.5 w-3.5 rounded-full border-2 border-white" style="background-color: #7f896a;"></span>
                                                <div class="flex w-28 shrink-0 flex-col justify-center">
                                                    <span class="text-sm font-semibold text-stone-800">{{ $timelineEvent['time_label'] }}</span>
                                                    <span class="mt-1 text-[11px] uppercase tracking-[0.16em] text-stone-500">{{ $timelineEvent['action'] === 'created' ? 'Entry' : 'Revision' }}</span>
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $timelineEvent['action'] === 'created' ? 'border-[#dbe5d8] bg-[#eef2ea] text-[#66745a]' : 'border-[#eadbc0] bg-[#f7efe1] text-[#9a7b39]' }}">
                                                            {{ $timelineEvent['action_label'] }}
                                                        </span>
                                                        <span class="text-xs text-stone-500">{{ $timelineEvent['created_at_label'] }}</span>
                                                    </div>

                                                    <p class="mt-2 text-sm text-stone-800">{{ $timelineEvent['description'] }}</p>

                                                    @if($timelineEvent['action'] === 'updated' && count($timelineEvent['changed_fields']) > 0)
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach($timelineEvent['changed_fields'] as $field)
                                                                <span class="rounded-full border border-stone-200/80 bg-stone-100 px-3 py-1 text-xs font-medium text-stone-700">
                                                                    {{ $field }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
