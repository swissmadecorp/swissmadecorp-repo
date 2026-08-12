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
            <div class="rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                <p class="text-xs uppercase tracking-wider text-slate-400">Delivery</p>
                <p class="mt-1 text-lg font-semibold">Monthly inventory</p>
                <p class="text-xs text-slate-400">Active campaign is used by the scheduler</p>
            </div>
        </div>
    </section>

    <div class="grid gap-6 {{ $showEditor ? 'xl:grid-cols-[360px_minmax(0,1fr)]' : '' }}">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <label for="campaign-search" class="sr-only">Search campaigns</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3a6 6 0 1 0 0 12A6 6 0 0 0 9 3ZM1 9a8 8 0 1 1 14.32 4.906l3.387 3.387a1 1 0 0 1-1.414 1.414l-3.387-3.387A8 8 0 0 1 1 9Z" clip-rule="evenodd"/></svg>
                    <input id="campaign-search" wire:model.live.debounce.250ms="search" type="search" placeholder="Search campaigns" class="block w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-3 text-sm text-slate-900 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($campaigns as $campaign)
                    <div wire:key="campaign-{{ $campaign->id }}" class="group p-4 transition hover:bg-slate-50 dark:hover:bg-slate-700/50 {{ $editingId === $campaign->id ? 'bg-sky-50 dark:bg-sky-950/30' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('massmail.edit', $campaign) }}" class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $campaign->title }}</h3>
                                    @if ($campaign->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Active</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Updated {{ optional($campaign->updated_at)->diffForHumans() }}</p>
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
                        <p class="text-xs font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">{{ $editingId ? 'Edit campaign' : 'New campaign' }}</p>
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
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Monthly campaign</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">The active campaign is selected when the scheduled command runs.</p>
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
            <section class="grid min-h-80 place-items-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center dark:border-slate-600 dark:bg-slate-800/50">
                <div>
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-sky-100 text-2xl text-sky-700 dark:bg-sky-950 dark:text-sky-300">✦</div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Select a campaign to edit</h2>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">Or create a new campaign to control the subject, design, watch brands, and monthly delivery.</p>
                </div>
            </section>
        @endif
    </div>

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
