<div class="space-y-6" x-data="{ tab: 'compose' }">
    <div wire:loading.delay class="fixed inset-0 z-[70] grid place-items-center bg-slate-950/35 backdrop-blur-sm">
        <div class="flex items-center gap-3 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-xl dark:bg-slate-800 dark:text-slate-100">
            <svg class="h-5 w-5 animate-spin text-sky-600" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
            </svg>
            Updating campaign…
        </div>
    </div>

    @if (session()->has('campaign_message'))
        <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
            <span>{{ session('campaign_message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="rounded p-1 hover:bg-emerald-100 dark:hover:bg-emerald-900" aria-label="Dismiss">&times;</button>
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 p-6 text-white shadow-lg">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-sky-300">Customer newsletter</p>
                <h2 class="text-2xl font-semibold sm:text-3xl">New watches, delivered beautifully.</h2>
                <p class="mt-2 text-sm leading-6 text-slate-300">Build the monthly inventory email, choose which brands to include, and preview exactly what subscribed customers will receive.</p>
            </div>
            <a href="{{ route('massmail.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-400/30">
                <span class="text-lg leading-none">+</span> New campaign
            </a>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <p class="text-xs uppercase tracking-wider text-slate-400">Subscribed audience</p>
                <p class="mt-1 text-2xl font-semibold">{{ number_format($subscriberCount) }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <p class="text-xs uppercase tracking-wider text-slate-400">Recent watches</p>
                <p class="mt-1 text-2xl font-semibold">{{ number_format($recentInventoryCount) }}</p>
                <p class="text-xs text-slate-400">Available, added in 3 weeks</p>
            </div>
            @if ($activeCampaign)
                <a href="{{ route('massmail.edit', $activeCampaign) }}" class="rounded-xl border border-emerald-400/40 bg-emerald-400/10 p-4 backdrop-blur transition hover:bg-emerald-400/15">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 ring-4 ring-emerald-400/20"></span>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">Active monthly email</p>
                    </div>
                    <p class="mt-2 truncate text-lg font-semibold text-white">{{ $activeCampaign->title }}</p>
                    <p class="text-xs text-emerald-200/80">Customers receive this campaign · Click to edit</p>
                </a>
            @else
                <a href="{{ route('massmail.create') }}" class="rounded-xl border border-amber-400/40 bg-amber-400/10 p-4 backdrop-blur transition hover:bg-amber-400/15">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-300">No active monthly email</p>
                    <p class="mt-2 text-lg font-semibold text-white">Delivery is not configured</p>
                    <p class="text-xs text-amber-200/80">Create or activate a campaign</p>
                </a>
            @endif
        </div>
    </section>

    <div class="grid gap-6 {{ $showEditor ? 'xl:grid-cols-[360px_minmax(0,1fr)]' : '' }}">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="mb-3">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Campaigns</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">The active monthly email is always listed first.</p>
                </div>
                <label for="campaign-search" class="sr-only">Search campaigns</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3a6 6 0 1 0 0 12A6 6 0 0 0 9 3ZM1 9a8 8 0 1 1 14.32 4.906l3.387 3.387a1 1 0 0 1-1.414 1.414l-3.387-3.387A8 8 0 0 1 1 9Z" clip-rule="evenodd"/></svg>
                    <input id="campaign-search" wire:model.live.debounce.250ms="search" type="search" placeholder="Search campaigns" class="block w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($campaigns as $campaign)
                    <div wire:key="campaign-{{ $campaign->id }}" class="group p-4 transition {{ $campaign->is_active ? 'border-l-4 border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/20' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50' }} {{ $editingId === $campaign->id ? 'ring-2 ring-inset ring-sky-400' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('massmail.edit', $campaign) }}" class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $campaign->title }}</h3>
                                    @if ($campaign->is_active)
                                        <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Active monthly email</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs {{ $campaign->is_active ? 'font-medium text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $campaign->is_active ? 'Customers receive this campaign' : 'Draft / inactive' }} · Updated {{ optional($campaign->updated_at)->diffForHumans() }}</p>
                            </a>
                            <button type="button" wire:click="delete({{ $campaign->id }})" wire:confirm="Delete this campaign?" class="rounded-lg p-2 text-slate-400 opacity-0 transition hover:bg-red-50 hover:text-red-600 group-hover:opacity-100 focus:opacity-100 dark:hover:bg-red-950/40" aria-label="Delete {{ $campaign->title }}">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1.75a.75.75 0 0 0-.75.75V3H5.5a.75.75 0 0 0 0 1.5h.31l.77 11.156A2.5 2.5 0 0 0 9.074 18h1.852a2.5 2.5 0 0 0 2.494-2.344L14.19 4.5h.31a.75.75 0 0 0 0-1.5H12v-.5a.75.75 0 0 0-.75-.75h-2.5ZM8.5 6.5a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Zm3 0a.75.75 0 0 1 .75.75v7a.75.75 0 0 1-1.5 0v-7a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-slate-100 text-xl dark:bg-slate-700">✉</div>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">No campaigns found</p>
                        <p class="mt-1 text-xs text-slate-500">Create one to define your customer email.</p>
                    </div>
                @endforelse
            </div>

            @if ($campaigns->hasPages())
                <div class="border-t border-slate-200 p-3 dark:border-slate-700">{{ $campaigns->links() }}</div>
            @endif
        </section>

        @if ($showEditor)
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">{{ $editingId ? 'Edit campaign' : 'New campaign' }}</p>
                            @if ($active)
                                <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Currently active</span>
                            @endif
                        </div>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900 dark:text-white">Email details</h2>
                    </div>
                    <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-900">
                        <button type="button" @click="tab = 'compose'" :class="tab === 'compose' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 dark:text-slate-400'" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition">Compose</button>
                        <button type="button" @click="window.massMailEditorAction('syncEditor').then(() => tab = 'preview')" :class="tab === 'preview' ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 dark:text-slate-400'" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition">Preview</button>
                    </div>
                </div>

                <div class="space-y-5 p-5" x-show="tab === 'compose'">
                    <div>
                        <label for="campaign-title" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Email subject</label>
                        <input id="campaign-title" wire:model="title" type="text" placeholder="New watches at Swiss Made Corp." class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                        @error('title') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_280px]">
                        <div class="min-w-0">
                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                <label for="mail-content" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email content</label>
                                <button type="button" wire:click="loadStandardTemplate" class="text-xs font-semibold text-sky-600 hover:text-sky-700 dark:text-sky-400">Reset to standard design</button>
                            </div>
                            <div wire:ignore class="overflow-hidden rounded-xl border border-slate-300 dark:border-slate-600">
                                <textarea id="mail-content">{{ $content }}</textarea>
                            </div>
                            @error('content') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <aside class="space-y-4">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-900/60">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Send this campaign monthly</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">When active, this is the email customers receive. Activating it replaces the previous active campaign.</p>
                                    </div>
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input wire:model="active" type="checkbox" class="peer sr-only">
                                        <span class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:transition peer-checked:bg-emerald-500 peer-checked:after:translate-x-5 dark:bg-slate-600"></span>
                                        <span class="sr-only">Set as active campaign</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Watch brands</p>
                                <p class="mb-2 mt-1 text-xs text-slate-500 dark:text-slate-400">No selection includes every brand.</p>
                                <div class="max-h-56 space-y-1 overflow-y-auto rounded-xl border border-slate-200 p-2 dark:border-slate-600">
                                    @foreach ($categories as $category)
                                        <label wire:key="mail-category-{{ $category->id }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700">
                                            <input wire:model="categoryIds" value="{{ $category->id }}" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                            <span class="truncate">{{ $category->category_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('categoryIds.*') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="button" x-on:click="window.massMailEditorAction('refreshInventory')" class="flex w-full items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2.5 text-sm font-bold text-sky-700 transition hover:bg-sky-100 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-300">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.42.75.75 0 0 0-1.222.87 7 7 0 0 0 11.714-3.08.75.75 0 0 0-1.29-.21ZM4.688 8.576a5.5 5.5 0 0 1 9.201-2.42.75.75 0 0 0 1.222-.87A7 7 0 0 0 3.397 8.366a.75.75 0 0 0 1.29.21Z" clip-rule="evenodd"/><path d="M14.25 2.75a.75.75 0 0 1 .75-.75h2.25a.75.75 0 0 1 .75.75V5a.75.75 0 0 1-1.5 0V3.5H15a.75.75 0 0 1-.75-.75ZM5.75 17.25A.75.75 0 0 1 5 18H2.75a.75.75 0 0 1-.75-.75V15a.75.75 0 0 1 1.5 0v1.5H5a.75.75 0 0 1 .75.75Z"/></svg>
                                Refresh recent watches
                            </button>
                        </aside>
                    </div>
                </div>

                <div x-cloak x-show="tab === 'preview'" class="bg-slate-100 p-5 dark:bg-slate-900">
                    <div class="mx-auto max-w-4xl overflow-hidden rounded-xl bg-white shadow-lg">
                        <div class="flex items-center gap-1.5 border-b border-slate-200 bg-slate-50 px-4 py-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-3 truncate text-xs text-slate-500">{{ $title ?: 'Email preview' }}</span>
                        </div>
                        <iframe title="Campaign preview" srcdoc="{{ $content }}" class="h-[680px] w-full bg-white"></iframe>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-900/50">
                    <button type="button" wire:click="cancelEditor" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700">Close</button>
                    <button type="button" x-on:click="window.massMailEditorAction('save')" class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-500/30">Save campaign</button>
                </div>
            </section>
        @else
            @if ($activeCampaign)
                <section class="grid min-h-80 place-items-center rounded-2xl border border-emerald-200 bg-emerald-50 p-8 text-center dark:border-emerald-800 dark:bg-emerald-950/20">
                    <div>
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-600 text-2xl text-white">✓</div>
                        <p class="mt-4 text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Active monthly email</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white">{{ $activeCampaign->title }}</h2>
                        <p class="mx-auto mt-1 max-w-md text-sm text-slate-600 dark:text-slate-300">This is the campaign customers will receive when the monthly schedule runs.</p>
                        <a href="{{ route('massmail.edit', $activeCampaign) }}" class="mt-4 inline-flex rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-500">Edit active campaign</a>
                    </div>
                </section>
            @else
                <section class="grid min-h-80 place-items-center rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center dark:border-amber-800 dark:bg-amber-950/20">
                    <div>
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-2xl font-bold text-amber-700 dark:bg-amber-900 dark:text-amber-200">!</div>
                        <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">No active monthly campaign</h2>
                        <p class="mx-auto mt-1 max-w-md text-sm text-slate-600 dark:text-slate-300">Choose a campaign and turn on “Send this campaign monthly,” or create a new campaign.</p>
                        <a href="{{ route('massmail.create') }}" class="mt-4 inline-flex rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-amber-500">Create campaign</a>
                    </div>
                </section>
            @endif
        @endif
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-700">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Newsletter recipients</h2>
                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700 dark:bg-sky-950 dark:text-sky-300">{{ number_format($subscriberCount) }} subscribed</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review the customer email list and permanently remove invalid addresses.</p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                @if (count($selectedSubscriberIds))
                    <button type="button" wire:click="deleteSelectedSubscribers" wire:confirm="Permanently remove {{ count($selectedSubscriberIds) }} selected email {{ Str::plural('address', count($selectedSubscriberIds)) }}?" class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-bold text-red-700 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                        Delete selected <span class="rounded-full bg-red-600 px-2 py-0.5 text-xs text-white">{{ count($selectedSubscriberIds) }}</span>
                    </button>
                @endif
                <div class="relative w-full sm:w-80">
                    <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3a6 6 0 1 0 0 12A6 6 0 0 0 9 3ZM1 9a8 8 0 1 1 14.32 4.906l3.387 3.387a1 1 0 0 1-1.414 1.414l-3.387-3.387A8 8 0 0 1 1 9Z" clip-rule="evenodd"/></svg>
                    <input wire:model.live.debounce.250ms="subscriberSearch" type="search" placeholder="Search email addresses" class="block w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>
            </div>
        </div>

        @if (session()->has('subscriber_message'))
            <div class="border-b border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('subscriber_message') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                    <tr>
                        @php
                            $subscriberPageIds = $subscribers->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                            $allSubscriberPageIdsSelected = count($subscriberPageIds) > 0
                                && collect($subscriberPageIds)->every(fn ($id) => in_array($id, array_map('intval', $selectedSubscriberIds), true));
                        @endphp
                        <th class="w-12 px-5 py-3">
                            <input type="checkbox" wire:click="toggleSubscriberPage({{ Illuminate\Support\Js::from($subscriberPageIds) }})" @checked($allSubscriberPageIdsSelected) class="h-4 w-4 cursor-pointer rounded border-slate-300 text-sky-600 focus:ring-sky-500" aria-label="Select all email addresses on this page">
                        </th>
                        <th class="px-5 py-3 font-semibold">
                            <button type="button" wire:click="sortSubscribers('email')" class="inline-flex items-center gap-1.5 transition hover:text-sky-600" aria-label="Sort by email address">
                                Email address
                                <span class="text-sm {{ $subscriberSort === 'email' ? 'text-sky-600' : 'text-slate-300 dark:text-slate-600' }}">
                                    @if ($subscriberSort === 'email') {{ $subscriberSortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-5 py-3 font-semibold">
                            <button type="button" wire:click="sortSubscribers('subscribed')" class="inline-flex items-center gap-1.5 transition hover:text-sky-600" aria-label="Sort by subscription status">
                                Subscription
                                <span class="text-sm {{ $subscriberSort === 'subscribed' ? 'text-sky-600' : 'text-slate-300 dark:text-slate-600' }}">
                                    @if ($subscriberSort === 'subscribed') {{ $subscriberSortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-5 py-3 font-semibold">
                            <button type="button" wire:click="sortSubscribers('validation')" class="inline-flex items-center gap-1.5 transition hover:text-sky-600" aria-label="Sort by email validation">
                                Validation
                                <span class="text-sm {{ $subscriberSort === 'validation' ? 'text-sky-600' : 'text-slate-300 dark:text-slate-600' }}">
                                    @if ($subscriberSort === 'validation') {{ $subscriberSortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-5 py-3 font-semibold">
                            <button type="button" wire:click="sortSubscribers('created_at')" class="inline-flex items-center gap-1.5 transition hover:text-sky-600" aria-label="Sort by date added">
                                Added
                                <span class="text-sm {{ $subscriberSort === 'created_at' ? 'text-sky-600' : 'text-slate-300 dark:text-slate-600' }}">
                                    @if ($subscriberSort === 'created_at') {{ $subscriberSortDirection === 'asc' ? '↑' : '↓' }} @else ↕ @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-5 py-3 text-right font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($subscribers as $subscriber)
                        @php($validEmail = filter_var($subscriber->email, FILTER_VALIDATE_EMAIL) !== false)
                        <tr wire:key="subscriber-{{ $subscriber->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="w-12 px-5 py-3.5">
                                <input type="checkbox" wire:model.live="selectedSubscriberIds" value="{{ $subscriber->id }}" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-sky-600 focus:ring-sky-500" aria-label="Select {{ $subscriber->email }}">
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white">{{ $subscriber->email }}</td>
                            <td class="px-5 py-3.5">
                                @if ($subscriber->subscribed)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Subscribed</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">Unsubscribed</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($validEmail)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Format accepted</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-950 dark:text-red-300"><span class="h-2 w-2 rounded-full bg-red-500"></span>Invalid format</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ optional($subscriber->created_at)->format('M j, Y') ?: 'Unknown' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <button type="button" wire:click="deleteSubscriber({{ $subscriber->id }})" wire:confirm="Permanently remove {{ $subscriber->email }} from the newsletter list?" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/40">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No email addresses match your search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subscribers->hasPages())
            <div class="border-t border-slate-200 p-4 dark:border-slate-700">{{ $subscribers->links(data: ['scrollTo' => false]) }}</div>
        @endif
    </section>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
    @endassets

    @script
    <script>
        const editorId = 'mail-content';

        const initMailEditor = () => {
            const textarea = document.getElementById(editorId);
            if (!textarea || typeof tinymce === 'undefined') return;

            tinymce.get(editorId)?.remove();
            tinymce.init({
                selector: `#${editorId}`,
                height: 560,
                menubar: 'file edit view insert format tools table help',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount help',
                toolbar: 'undo redo | blocks fontsize | bold italic underline | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image table | code preview fullscreen',
                relative_urls: false,
                remove_script_host: false,
                entity_encoding: 'raw',
                valid_elements: '*[*]',
                branding: false,
                promotion: false,
                setup(editor) {
                    editor.on('init', () => editor.setContent($wire.get('content') || ''));
                    editor.on('change input undo redo', () => $wire.set('content', editor.getContent(), false));
                },
            });
        };

        window.massMailEditorAction = async (method) => {
            const editor = tinymce?.get(editorId);
            const editorContent = editor?.getContent() ?? $wire.get('content') ?? '';

            if (method === 'syncEditor') {
                await $wire.set('content', editorContent);
                return;
            }

            await $wire.call(method, editorContent);
        };

        $wire.on('mail-editor-content', (event) => {
            const content = event?.content ?? event?.[0]?.content ?? $wire.get('content') ?? '';
            const editor = tinymce?.get(editorId);
            editor ? editor.setContent(content) : setTimeout(initMailEditor, 50);
        });

        $wire.on('mail-editor-destroy', () => tinymce?.get(editorId)?.remove());

        initMailEditor();
    </script>
    @endscript
</div>
