<div class="watch-reminders" wire:poll.30s.visible
    x-data="{
        editorOpen: $wire.entangle('showEditor'),
        drawerOpen: false,
        init() {
            this.$watch('editorOpen', open => this.setDrawerOpen(open));
            this.$nextTick(() => requestAnimationFrame(() => this.setDrawerOpen(this.editorOpen)));
        },
        setDrawerOpen(open) {
            this.drawerOpen = open;
            document.body.classList.toggle('overflow-hidden', open);
            if (open) {
                const url = new URL(window.location.href);
                if (url.searchParams.has('reminder')) {
                    url.searchParams.delete('reminder');
                    window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
                }
                this.$nextTick(() => this.$refs.reminderPanel.focus());
            }
        },
        destroy() {
            document.body.classList.remove('overflow-hidden');
        }
    }"
    x-on:keydown.escape.window="if (editorOpen) $wire.closeEditor()">
    <style>
        .wr-slide-container{visibility:hidden;pointer-events:none;transition:visibility 0s .5s}
        .wr-slide-container[data-open="true"]{visibility:visible;pointer-events:auto;transition-delay:0s}
        .wr-slide-container .wr-slide-panel{transform:translateX(100%)}
        .wr-slide-container .wr-slide-bg{opacity:0}
        .wr-slide-container[data-open="true"] .wr-slide-panel{transform:translateX(0)}
        .wr-slide-container[data-open="true"] .wr-slide-bg{opacity:.2}
        .watch-reminders{--wr-bg:#fff;--wr-soft:#f8fafc;--wr-line:#e2e8f0;--wr-text:#172033;--wr-muted:#64748b;color:var(--wr-text)}
        .dark .watch-reminders{--wr-bg:#1f2937;--wr-soft:#111827;--wr-line:#374151;--wr-text:#f3f4f6;--wr-muted:#9ca3af}
        .wr-heading{display:flex;justify-content:space-between;align-items:center;gap:20px;margin:8px 0 24px}.wr-heading h1{font-size:26px;font-weight:700;margin:0 0 5px}.wr-muted{color:var(--wr-muted);font-size:13px;line-height:1.6}
        .wr-button{border:1px solid var(--wr-line);background:var(--wr-bg);color:var(--wr-text);border-radius:8px;padding:9px 13px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap}.wr-button:hover{background:var(--wr-soft)}.wr-button:disabled{opacity:.5;cursor:wait}.wr-primary{background:#183c35;color:#fff;border-color:#183c35}.wr-primary:hover{background:#245348}.wr-danger{color:#dc2626}.wr-link{color:#2563eb;text-decoration:none;overflow-wrap:anywhere}.wr-link:hover{text-decoration:underline}.wr-customer-edit{display:block;padding:0;border:0;background:transparent;color:var(--wr-text);font:inherit;font-weight:700;text-align:left;cursor:pointer}.wr-customer-edit:hover{color:#2563eb;text-decoration:underline}
        .wr-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}.wr-stat{background:var(--wr-bg);border:1px solid var(--wr-line);border-radius:12px;padding:18px;text-align:left;cursor:pointer}.wr-stat strong{display:block;font-size:28px;margin-top:6px}.wr-stat.active{border-color:#25846b;box-shadow:0 0 0 1px #25846b}.wr-stat span{font-size:13px;color:var(--wr-muted)}
        .wr-panel{background:var(--wr-bg);border:1px solid var(--wr-line);border-radius:12px;overflow:hidden}.wr-toolbar{display:flex;gap:12px;justify-content:space-between;padding:18px;border-bottom:1px solid var(--wr-line)}.wr-input{width:100%;border:1px solid var(--wr-line);border-radius:7px;background:var(--wr-bg);color:var(--wr-text);padding:10px 12px;font-size:14px}.wr-input::placeholder{color:#a0aec0;opacity:1}.dark .wr-input::placeholder{color:#aeb8c7;opacity:1}.wr-input:focus{outline:2px solid #479d84;outline-offset:1px}.wr-search{max-width:430px}.wr-filter{max-width:200px}
        .wr-table-scroll{overflow-x:auto}.wr-table{width:100%;text-align:left;font-size:13px;border-collapse:collapse}.wr-table th{background:var(--wr-soft);color:var(--wr-muted);font-size:11px;text-transform:uppercase;letter-spacing:.06em;padding:14px 18px;white-space:nowrap}.wr-table td{padding:20px 18px;border-top:1px solid var(--wr-line);vertical-align:top;min-width:160px}.wr-table td:first-child{min-width:210px}.wr-table strong{display:block;font-size:14px;margin-bottom:6px}.wr-contact{display:block;margin-top:5px}.wr-tag{display:inline-block;border-radius:5px;background:var(--wr-soft);border:1px solid var(--wr-line);padding:3px 7px;margin:2px 2px 2px 0;font-size:11px}.wr-status{display:inline-block;border-radius:20px;padding:5px 10px;font-size:11px;font-weight:700;white-space:nowrap}.wr-status-0{background:#eff6ff;color:#1d4ed8}.wr-status-1{background:#dcfce7;color:#166534}.wr-status-2{background:#f1f5f9;color:#475569}.wr-actions{display:flex;gap:7px;flex-wrap:wrap}.wr-actions .wr-button{padding:6px 9px;font-size:12px}.wr-notice{padding:13px 16px;background:#ecfdf5;color:#166534;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:18px}.wr-empty{text-align:center;padding:55px 20px}.wr-empty h2{font-size:18px;font-weight:600;margin-bottom:8px}
        [x-cloak]{display:none!important}.wr-slide-container{position:fixed;inset:0;width:100%;height:100%;z-index:51}.wr-slide-bg{position:absolute;inset:0;background:#111827;transition:opacity .5s ease}.wr-slide-panel{position:absolute;right:0;top:0;height:100%;width:390px;max-width:calc(100vw - 1rem);overflow-y:auto;background:var(--wr-bg);color:var(--wr-text);border-left:1px solid var(--wr-line);box-shadow:-20px 0 60px #0003;transition:transform .5s ease-out}.wr-modal-header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid var(--wr-line);position:sticky;top:0;z-index:2;background:var(--wr-bg)}.wr-modal-header h2{font-size:20px;font-weight:700}.wr-modal-body{padding:24px}.wr-section{margin-bottom:24px}.wr-section h3{font-size:14px;font-weight:700;margin-bottom:12px}.wr-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}.wr-field label{display:block;font-size:12px;font-weight:600;margin-bottom:6px}.wr-field.wr-wide{grid-column:1/-1}.wr-error{color:#dc2626;font-size:12px;margin-top:5px}.wr-checks{display:flex;flex-wrap:wrap;gap:10px 18px;margin-top:10px}.wr-checks label{display:flex;align-items:center;gap:7px;font-size:13px}.wr-checks input{accent-color:#183c35}.wr-modal-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 24px;border-top:1px solid var(--wr-line);background:var(--wr-soft)}.wr-legacy{padding:12px;background:#fffbeb;color:#92400e;border-radius:8px;margin-bottom:20px;font-size:13px}
        @media(min-width:768px){.wr-slide-panel{width:790px;max-width:calc(100vw - 2rem)}}
        @media(max-width:640px){.wr-heading{align-items:flex-start}.wr-heading h1{font-size:21px}.wr-stats{gap:8px}.wr-stat{padding:12px}.wr-stat strong{font-size:23px}.wr-toolbar{flex-direction:column}.wr-filter,.wr-search{max-width:none}.wr-grid{grid-template-columns:1fr}.wr-modal-body{padding:18px}.wr-modal-footer{align-items:flex-end}.wr-modal-footer .wr-muted{max-width:50%}}
    </style>

    <div class="wr-heading">
        <div><h1>Customer watch reminders</h1><p class="wr-muted">Keep track of the watches your customers are waiting for.</p></div>
        @if(auth()->user()?->hasRole('administrator'))
            <button type="button" class="wr-button wr-primary" wire:click="newReminder" wire:loading.attr="disabled" wire:target="newReminder,loadReminder">+ New reminder</button>
        @endif
    </div>

    @if(session()->has('message'))<div class="wr-notice" role="status">{{ session('message') }}</div>@endif

    <div class="wr-stats">
        @foreach([0 => 'Watching inventory', 1 => 'Matches to follow up', 2 => 'Customers contacted'] as $status => $label)
            <button type="button" class="wr-stat {{ (string) $filter === (string) $status ? 'active' : '' }}" wire:click="$set('filter', '{{ $status }}')" aria-pressed="{{ (string) $filter === (string) $status ? 'true' : 'false' }}">
                <span>{{ $label }}</span><strong>{{ $counts[$status] ?? 0 }}</strong>
            </button>
        @endforeach
    </div>

    <div class="wr-panel">
        <div class="wr-toolbar">
            <input aria-label="Search reminders" class="wr-input wr-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search customer, email, phone, or watch…">
            <select class="wr-input wr-filter" wire:model.live="filter" aria-label="Reminder status">
                <option value="">All reminders</option><option value="0">Watching inventory</option><option value="1">Matches to follow up</option><option value="2">Contacted</option>
            </select>
        </div>
        <div class="wr-table-scroll">
            <table class="wr-table">
                <thead><tr><th>Customer</th><th>Watch requested</th><th>Preferences</th><th>Status &amp; inventory</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($reminders as $record)
                    <tr wire:key="reminder-{{ $record->id }}">
                        <td>
                            @if(auth()->user()?->hasRole('administrator'))
                                <button type="button" class="wr-customer-edit" wire:click="loadReminder({{ $record->id }})" wire:loading.attr="disabled" wire:target="newReminder,loadReminder" aria-label="Edit reminder for {{ $record->customer_name ?: $record->assigned_to }}">{{ $record->customer_name ?: $record->assigned_to }}</button>
                            @else
                                <strong>{{ $record->customer_name ?: $record->assigned_to }}</strong>
                            @endif
                            @if($record->customer_email)<a class="wr-link wr-contact" href="mailto:{{ $record->customer_email }}">{{ $record->customer_email }}</a>@endif
                            @if($record->customer_phone)<a class="wr-link wr-contact" href="tel:{{ preg_replace('/[^0-9+]/', '', $record->customer_phone) }}">{{ $record->customer_phone }}</a>@endif
                            @if(!$record->customer_email && !$record->customer_phone)<p class="wr-muted">Contact details need review</p>@endif
                        </td>
                        <td>
                            <strong>{{ $record->category ? $record->category->category_name.' '.$record->watch_model : $record->criteria }}</strong>
                            @if($record->category_id)
                                <p class="wr-muted">@if($record->watch_reference)Ref. {{ $record->watch_reference }}@endif @if($record->watch_reference && $record->case_size) · @endif @if($record->case_size){{ $record->case_size }}mm @endif</p>
                                @if($record->dial_color)<p class="wr-muted">{{ $record->dial_color }} dial</p>@endif
                            @else
                                <span class="wr-tag">Legacy criteria · review details</span>
                            @endif
                            @if($record->notes)<p class="wr-muted" style="margin-top:8px;max-width:300px;white-space:pre-line">{{ $record->notes }}</p>@endif
                        </td>
                        <td>
                            @forelse($record->preferences('product_condition') as $condition)
                                <span class="wr-tag">{{ Conditions()->get($condition, 'Unknown condition') }}</span>
                            @empty<span class="wr-tag">Any condition</span>@endforelse
                            <p class="wr-muted" style="margin-top:7px">{{ in_array('Box', $record->preferences('boxpapers')) ? 'Box required' : 'Box: no preference' }}</p>
                            <p class="wr-muted">{{ in_array('Papers', $record->preferences('boxpapers')) ? 'Papers required' : 'Papers: no preference' }}</p>
                        </td>
                        <td>
                            <span class="wr-status wr-status-{{ $record->status }}">{{ [0 => 'Watching', 1 => 'Match found', 2 => 'Contacted'][$record->status] ?? 'Needs review' }}</span>
                            @if($record->matchedProduct)
                                <p style="margin-top:9px"><a class="wr-link" href="{{ url('/admin/products?search='.$record->matched_product_id) }}">View watch #{{ $record->matched_product_id }} ↗</a></p>
                                <p class="wr-muted">{{ \App\Services\WatchReminderMatcher::available($record->matchedProduct) ? 'Currently in stock' : 'No longer available' }}</p>
                            @elseif($record->status === 1)<p class="wr-muted">Review inventory before contacting</p>@endif
                            @if($record->contacted_at)<p class="wr-muted">Contacted {{ $record->contacted_at->format('M j, Y') }}</p>
                            @elseif($record->matched_at)<p class="wr-muted">Matched {{ $record->matched_at->format('M j, Y') }}</p>@endif
                        </td>
                        <td>
                            @if(auth()->user()?->hasRole('administrator'))
                            <div class="wr-actions">
                                @if($record->status === 1)<button type="button" class="wr-button wr-primary" wire:click="markContacted({{ $record->id }})" wire:confirm="Mark this customer as contacted?">Mark contacted</button>@endif
                                @if($record->status !== 0)<button type="button" class="wr-button" wire:click="resumeWatching({{ $record->id }})">Watch again</button>@endif
                                <button type="button" class="wr-button wr-danger" wire:click="deleteReminder({{ $record->id }})" wire:confirm="Remove this customer's reminder?">Remove</button>
                            </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="wr-empty"><h2>{{ $search || $filter !== '' ? 'No reminders found' : 'A watch worth waiting for' }}</h2><p class="wr-muted">{{ $search || $filter !== '' ? 'Try another search or status filter.' : 'Create a reminder with the customer’s details and the watch they want. We’ll flag it when matching inventory arrives.' }}</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:14px 18px">{{ $reminders->links() }}</div>
    </div>
    <p class="wr-muted" style="margin-top:12px">Matches appear here and when saving a matching inventory watch. Contact customers directly; no email or text is sent automatically.</p>

    <div id="slideover-reminder-container" class="wr-slide-container" wire:key="reminder-editor" x-bind:data-open="drawerOpen ? 'true' : 'false'" x-bind:aria-hidden="!drawerOpen" x-bind:inert="!drawerOpen">
        <div id="slideover-reminder-bg" class="wr-slide-bg" wire:click="closeEditor"></div>
        <aside id="slideover-reminder" x-ref="reminderPanel" tabindex="-1" class="wr-slide-panel" role="dialog" aria-modal="true" aria-labelledby="reminder-editor-title">
        @if($showEditor)
        <form wire:submit="saveReminder">
            <div class="wr-modal-header"><h2 id="reminder-editor-title">{{ $key ? 'Edit reminder' : 'New watch reminder' }}</h2><button type="button" class="wr-button" wire:click="closeEditor" aria-label="Close editor">✕</button></div>
            <div class="wr-modal-body">
                @if($legacyCriteria)<div class="wr-legacy"><strong>Existing request:</strong> {{ $legacyCriteria }}<br>Watch details have been filled from the original request where possible. Review them, complete any missing details, and check the customer’s contact information before saving.</div>@endif
                <section class="wr-section">
                    <h3>01 &nbsp; Customer details</h3>
                    <p class="wr-muted" style="margin-bottom:12px">Full name is required. Add an email address or phone number when available.</p>
                    <div class="wr-grid">
                        @foreach(['customer_name' => ['Full name *', 'text', 'Customer’s full name'], 'customer_email' => ['Email address', 'email', 'name@example.com'], 'customer_phone' => ['Phone number', 'tel', '+1 (555) 123-4567']] as $field => [$label, $type, $placeholder])
                            <div class="wr-field"><label for="wr-{{ $field }}">{{ $label }}</label><input class="wr-input" id="wr-{{ $field }}" type="{{ $type }}" wire:model="reminder.{{ $field }}" placeholder="{{ $placeholder }}" @if($field === 'customer_name') autofocus @endif>@error('reminder.'.$field)<p class="wr-error">{{ $message }}</p>@enderror</div>
                        @endforeach
                    </div>
                </section>
                <section class="wr-section">
                    <h3>02 &nbsp; Watch to look for</h3>
                    <p class="wr-muted" style="margin-bottom:12px">Brand and model are required. Reference and case size narrow the match when supplied. Case and extra spaces are ignored.</p>
                    <div class="wr-grid">
                        <div class="wr-field"><label for="wr-brand">Brand *</label><select class="wr-input" id="wr-brand" wire:model="reminder.category_id"><option value="">Select a brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->category_name }}</option>@endforeach</select>@error('reminder.category_id')<p class="wr-error">{{ $message }}</p>@enderror</div>
                        @foreach(['watch_model' => ['Model *', 'Oyster Perpetual No Date'], 'watch_reference' => ['Reference number (optional)', '114300'], 'case_size' => ['Case size (mm) (optional)', '39'], 'dial_color' => ['Dial color (optional)', 'Leave blank for any dial']] as $field => [$label, $placeholder])
                            <div class="wr-field"><label for="wr-{{ $field }}">{{ $label }}</label><input class="wr-input" id="wr-{{ $field }}" wire:model="reminder.{{ $field }}" placeholder="{{ $placeholder }}" @if($field === 'case_size') type="number" step="0.01" min="0.01" max="100" @else type="text" @endif>@error('reminder.'.$field)<p class="wr-error">{{ $message }}</p>@enderror</div>
                        @endforeach
                    </div>
                </section>
                <section class="wr-section">
                    <h3>03 &nbsp; Condition &amp; accessories</h3>
                    <p class="wr-muted">Select all acceptable conditions. Leave all unchecked for any condition.</p>
                    <div class="wr-checks">@foreach(Conditions() as $value => $label)@if($value > 0)<label><input type="checkbox" wire:model="reminder.product_condition" value="{{ $value }}">{{ $label }}</label>@endif @endforeach</div>
                    @error('reminder.product_condition')<p class="wr-error">{{ $message }}</p>@enderror
                    @error('reminder.product_condition.*')<p class="wr-error">{{ $message }}</p>@enderror
                    <div class="wr-checks" style="margin-top:20px"><label><input type="checkbox" wire:model="reminder.boxpapers" value="Box">Box required</label><label><input type="checkbox" wire:model="reminder.boxpapers" value="Papers">Papers required</label></div>
                    <p class="wr-muted" style="margin-top:6px">Unchecked means no preference. If both are selected, the watch must include both.</p>
                    @error('reminder.boxpapers')<p class="wr-error">{{ $message }}</p>@enderror
                    @error('reminder.boxpapers.*')<p class="wr-error">{{ $message }}</p>@enderror
                </section>
                <div class="wr-field"><label for="wr-notes">Notes</label><textarea class="wr-input" id="wr-notes" wire:model="reminder.notes" rows="3" placeholder="Budget, preferred contact time, or other details for the team…"></textarea><p class="wr-muted">Notes are for staff; they are not used for automatic matching.</p>@error('reminder.notes')<p class="wr-error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="wr-modal-footer"><p class="wr-muted">We also check inventory already in stock when you save.</p><div class="wr-actions"><button type="button" class="wr-button" wire:click="closeEditor">Cancel</button><button class="wr-button wr-primary" type="submit" wire:loading.attr="disabled" wire:target="saveReminder">Save reminder</button></div></div>
        </form>
        @endif
        </aside>
    </div>
</div>

