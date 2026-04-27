<div class="space-y-6 pt-3" wire:poll.1s.visible="refreshMonitor">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl bg-blue-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Active Editors</p>
            <p class="mt-3 text-3xl font-semibold text-blue-950">{{ $stats['active'] }}</p>
            <p class="mt-2 text-sm text-blue-700">Admins currently inside the product editor.</p>
        </div>
        <div class="rounded-3xl bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Creating</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-950">{{ $stats['creating'] }}</p>
            <p class="mt-2 text-sm text-emerald-700">Users starting brand-new items right now.</p>
        </div>
        <div class="rounded-3xl bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Updating</p>
            <p class="mt-3 text-3xl font-semibold text-amber-950">{{ $stats['updating'] }}</p>
            <p class="mt-2 text-sm text-amber-700">Users editing existing products right now.</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Saved Today</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950">{{ $stats['saved_today'] }}</p>
            <p class="mt-2 text-sm text-gray-600">Create and update events logged today.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.85fr,1.15fr]">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Active Product Editors</h2>
                    <p class="mt-1 text-sm text-gray-500">Create sessions stay private until the product is saved. Update sessions show the fields that have been touched.</p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                    Live
                </span>
            </div>

            @if($activeSessions->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                    No admin is editing a product right now.
                </div>
            @else
                <div class="mt-6 grid gap-4">
                    @foreach($activeSessions as $session)
                        <article class="rounded-3xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div class="flex items-start gap-4">
                                    @if($session['mode'] === 'update')
                                        <img
                                            src="{{ $session['product_image'] }}"
                                            alt="{{ $session['product_title'] ?: 'Product image' }}"
                                            class="h-20 w-20 rounded-2xl border border-gray-200 object-cover"
                                        >
                                    @else
                                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                                            New
                                        </div>
                                    @endif

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $session['user_name'] }}</h3>
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {{ $session['mode'] === 'create' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $session['mode_label'] }}
                                            </span>
                                        </div>

                                        @if($session['mode'] === 'update')
                                            <p class="mt-2 text-sm text-gray-900">
                                                Product #{{ $session['product_id'] }}: {{ $session['product_title'] ?: 'Untitled product' }}
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500">Last heartbeat {{ $session['last_seen_label'] }}</p>
                                        @else
                                            <p class="mt-2 text-sm text-gray-700">This user is building a new item. Field-by-field typing stays hidden until the product is saved.</p>
                                            <p class="mt-1 text-xs text-gray-500">Last heartbeat {{ $session['last_seen_label'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if($session['mode'] === 'update')
                                    <div class="md:max-w-xs">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Changed Fields</p>
                                        @if(count($session['changed_fields']) > 0)
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($session['changed_fields'] as $field)
                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-sm">{{ $field }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="mt-2 text-sm text-gray-500">No field changes detected yet.</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Recent Saved Activity</h2>
                    <p class="mt-1 text-sm text-gray-500">Each product and admin pair gets its own card, with every save shown underneath in time order.</p>
                </div>
                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-stone-600">
                    Product + User
                </span>
            </div>

            @if($recentEvents->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-sm text-gray-500">
                    No product save activity has been logged yet.
                </div>
            @else
                <div class="mt-6 space-y-4">
                    @foreach($recentEvents as $group)
                        <article class="rounded-3xl border border-gray-200 bg-stone-50/80 p-5">
                            <div class="flex flex-col gap-5">
                                <div class="flex items-start gap-4">
                                    <img
                                        src="{{ $group['product_image'] }}"
                                        alt="{{ $group['product_title'] ?: 'Product image' }}"
                                        class="h-20 w-20 rounded-2xl border border-gray-200 object-cover"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-lg font-semibold text-gray-900">{{ $group['product_title'] ?: 'Untitled product' }}</p>
                                            <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 shadow-sm">
                                                Product #{{ $group['product_id'] }}
                                            </span>
                                        </div>

                                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600">
                                            <p class="font-medium text-gray-900">{{ $group['user_name'] }}</p>
                                            <p>{{ $group['total_saves_label'] }}</p>
                                            <p>Last updated {{ $group['last_updated_time'] }}</p>
                                            <p class="text-gray-500">{{ $group['last_updated_label'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if(collect($group['field_summary'])->isNotEmpty())
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Change Summary</p>
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
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Save Timeline</p>
                                    <div class="mt-4 space-y-3">
                                        @foreach($group['timeline'] as $timelineEvent)
                                            <div class="flex gap-4 rounded-2xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm">
                                                <div class="flex w-24 shrink-0 items-center text-sm font-semibold text-stone-700">
                                                    {{ $timelineEvent['time_label'] }}
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $timelineEvent['action'] === 'created' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                            {{ $timelineEvent['action_label'] }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">{{ $timelineEvent['created_at_label'] }}</span>
                                                    </div>

                                                    <p class="mt-2 text-sm text-gray-800">{{ $timelineEvent['description'] }}</p>

                                                    @if($timelineEvent['action'] === 'updated' && count($timelineEvent['changed_fields']) > 0)
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach($timelineEvent['changed_fields'] as $field)
                                                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-700">
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
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
