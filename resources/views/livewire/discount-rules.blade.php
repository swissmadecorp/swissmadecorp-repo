<div
    x-data="{ drawerOpen: @entangle('drawerOpen') }"
    class="relative"
>
    @php
        $activeFlashMessage = session('discount_rules_flash_message', $flashMessage);
        $activeFlashType = session('discount_rules_flash_type', $flashType);
    @endphp

    @if ($activeFlashMessage)
        <div class="mb-4 flex items-start justify-between rounded-2xl border px-4 py-3 {{ $activeFlashType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
            <div>
                <p class="text-sm font-semibold">{{ $activeFlashMessage }}</p>
            </div>
            <button type="button" wire:click="clearFlash" class="rounded-full px-2 text-sm text-current/70 transition hover:bg-white/70 hover:text-current">x</button>
        </div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-700 p-5 text-white shadow-sm">
            <p class="text-xs uppercase tracking-[0.24em] text-slate-300">Active Rules</p>
            <p class="mt-3 text-3xl font-semibold">{{ $this->stats['active'] }}</p>
            <p class="mt-2 text-sm text-slate-300">Currently affecting storefront pricing</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Scheduled</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $this->stats['scheduled'] }}</p>
            <p class="mt-2 text-sm text-slate-500">Future promotions waiting to start</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Brand Rules</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $this->stats['brand'] }}</p>
            <p class="mt-2 text-sm text-slate-500">Applies to all products in selected brands</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Product Rules</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $this->stats['product'] }}</p>
            <p class="mt-2 text-sm text-slate-500">One-off discounts for exact items</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-4 lg:flex-row lg:items-center">
                <button
                    type="button"
                    wire:click="createNew"
                    class="inline-flex items-center justify-center rounded-2xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"
                >
                    Create Discount
                </button>

                <div class="relative w-full max-w-md">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="m19 19-4-4m1-7a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Search discount rules, promo codes, or titles"
                        class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                    >
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="statusFilter" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="expired">Expired</option>
                    <option value="inactive">Inactive</option>
                </select>

                <select wire:model.live="scopeFilter" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100">
                    <option value="all">All Scopes</option>
                    <option value="brand">Brand</option>
                    <option value="product">Individual Product</option>
                    <option value="all-products">All Products</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-xs uppercase tracking-[0.18em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Rule</th>
                        <th class="px-5 py-4">Applies To</th>
                        <th class="px-5 py-4">Action</th>
                        <th class="px-5 py-4">Code</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Date Range</th>
                        <th class="px-5 py-4 text-right">Discount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rules as $rule)
                        @php($status = $this->statusFor($rule))
                        <tr
                            wire:key="discount-rule-{{ $rule->id }}"
                            wire:click="editRule({{ $rule->id }})"
                            class="cursor-pointer transition hover:bg-sky-50/70"
                        >
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 rounded-2xl bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">#{{ $rule->id }}</div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $rule->rule_name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $rule->title ?: 'No meta title' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <p class="font-medium text-slate-900">{{ $this->targetSummary($rule) }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $this->targetDetails($rule) }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold text-slate-700 text-center">{{ $this->formatActionLabel((int) $rule->action) }}</span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="font-medium text-slate-900">{{ $rule->discount_code ?: 'No code' }}</span>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $status['classes'] }}">{{ $status['label'] }}</span>
                            </td>
                            <td class="px-5 py-4 align-top text-sm text-slate-500">
                                <p>{{ optional($rule->start_date)->format('m/d/Y') ?: '--' }}</p>
                                <p class="mt-1">{{ optional($rule->end_date)->format('m/d/Y') ?: '--' }}</p>
                            </td>
                            <td class="px-5 py-4 text-right align-top font-semibold text-slate-900">{{ $this->discountSummary($rule) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="text-base font-semibold text-slate-800">No discount rules matched this view.</p>
                                <p class="mt-2 text-sm text-slate-500">Try a broader search or create a new discount rule.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-5 py-4">
            {{ $rules->links('livewire.pagination') }}
        </div>
    </div>

    <div
        x-cloak
        x-show="drawerOpen"
        class="fixed inset-0 z-40 bg-slate-950/35"
        x-transition.opacity
        @click="drawerOpen = false"
    ></div>

    <aside
        x-cloak
        x-show="drawerOpen"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-2xl flex-col border-l border-slate-200 bg-white shadow-2xl"
    >
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] text-slate-400">{{ $editingRuleId ? 'Edit Rule' : 'New Rule' }}</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $editingRuleId ? 'Edit Discount Rule' : 'Create Discount Rule' }}</h2>
                <p class="mt-2 text-sm text-slate-500">Manage brand-wide promotions and one-off item discounts from one side panel.</p>
            </div>
            <button type="button" wire:click="closeDrawer" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-800">Rule Name</label>
                    <input type="text" wire:model.defer="ruleName" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                    @error('ruleName') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-800">Discount Type</label>
                    <select wire:model.live="action" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                        @foreach ($this->actions as $index => $label)
                            <option value="{{ $index }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('action') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-800">Amount</label>
                    <input type="number" step="0.01" wire:model.defer="amount" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100" placeholder="0.00">
                    @error('amount') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-800">Meta Title</label>
                    <input type="text" wire:model.defer="metaTitle" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-800">Description</label>
                    <textarea wire:model.defer="description" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"></textarea>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-800">Discount Code</label>
                    <input type="text" wire:model.defer="discountCode" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100" placeholder="Optional promo code">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-800">Start Date</label>
                        <input type="date" wire:model.defer="startDate" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-800">End Date</label>
                        <input type="date" wire:model.defer="endDate" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100">
                    </div>
                </div>
            </div>

            @if ($action === 4)
                <div class="rounded-3xl border border-sky-200 bg-sky-50 px-5 py-4">
                    <p class="text-sm font-semibold text-sky-900">This rule applies to all products automatically.</p>
                    <p class="mt-1 text-sm text-sky-700">Site-wide product percent discount does not need a brand or product selection.</p>
                </div>
            @elseif (in_array($action, [2, 3, 5], true))
                <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Applies To</p>
                        <p class="mt-1 text-sm text-slate-500">
                            @if ($action === 5)
                                This brand percent discount applies to all products in the selected brand.
                            @elseif ($action === 3)
                                This fixed amount discount applies only to the selected product items.
                            @else
                                This product percent discount applies only to the selected product items.
                            @endif
                        </p>
                    </div>

                    @if ($action === 5)
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-800">Search Brands</label>
                                <input type="text" wire:model.live.debounce.200ms="brandSearch" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100" placeholder="Type Cartier, Rolex, Omega...">
                            </div>

                            @if ($this->selectedBrands->count())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->selectedBrands as $brand)
                                        <button type="button" wire:click="removeBrand({{ $brand->id }})" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">
                                            {{ $brand->category_name }}
                                            <span class="text-white/70">x</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="grid gap-2 rounded-2xl border border-slate-200 bg-white p-2">
                                @forelse ($this->availableBrands as $brand)
                                    <button type="button" wire:click="toggleBrandSelection({{ $brand->id }})" class="flex items-center justify-between rounded-2xl px-3 py-2 text-left text-sm transition {{ in_array($brand->id, $selectedBrandIds, true) ? 'bg-sky-50 text-sky-900' : 'hover:bg-slate-50 text-slate-700' }}">
                                        <span>{{ $brand->category_name }}</span>
                                        @if (in_array($brand->id, $selectedBrandIds, true))
                                            <span class="rounded-full bg-sky-600 px-2 py-0.5 text-xs font-semibold text-white">Selected</span>
                                        @endif
                                    </button>
                                @empty
                                    <p class="px-3 py-2 text-sm text-slate-500">No brands matched your search.</p>
                                @endforelse
                            </div>

                            @error('selectedBrandIds') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-800">Search Products</label>
                                <input type="text" wire:model.live.debounce.250ms="productSearch" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100" placeholder="Search by ID, title, model, or reference">
                                <p class="mt-2 text-xs text-slate-500">Type at least 2 characters to search products.</p>
                            </div>

                            @if ($this->selectedProducts->count())
                                <div class="space-y-2">
                                    @foreach ($this->selectedProducts as $product)
                                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                            <div>
                                                <p class="font-semibold text-slate-900">#{{ $product->id }} {{ $product->title }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $product->categories?->category_name }} {{ $product->p_model }} {{ $product->p_reference }}</p>
                                            </div>
                                            <button type="button" wire:click="removeProduct({{ $product->id }})" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">Remove</button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500">No products selected yet.</div>
                            @endif

                            @if (strlen(trim($productSearch)) >= 2)
                                <div class="grid gap-2 rounded-2xl border border-slate-200 bg-white p-2">
                                    @forelse ($this->productResults as $product)
                                        <button type="button" wire:click="addProduct({{ $product->id }})" class="flex items-center justify-between rounded-2xl px-3 py-3 text-left transition hover:bg-slate-50">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">#{{ $product->id }} {{ $product->title }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $product->categories?->category_name }} {{ $product->p_model }} {{ $product->p_reference }}</p>
                                            </div>
                                            <span class="rounded-full bg-sky-600 px-3 py-1 text-xs font-semibold text-white">Add</span>
                                        </button>
                                    @empty
                                        <p class="px-3 py-2 text-sm text-slate-500">No products matched your search.</p>
                                    @endforelse
                                </div>
                            @endif

                            @error('selectedProductIds') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex items-center justify-between rounded-3xl border border-slate-200 bg-white px-4 py-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Active</p>
                        <p class="mt-1 text-sm text-slate-500">Enable this rule on the storefront.</p>
                    </div>
                    <input type="checkbox" wire:model.defer="isActive" class="h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                </label>

                <label class="flex items-center justify-between rounded-3xl border border-slate-200 bg-white px-4 py-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Free Shipping</p>
                        <p class="mt-1 text-sm text-slate-500">Override shipping charges when eligible.</p>
                    </div>
                    <input type="checkbox" wire:model.defer="freeShipping" class="h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
            <div>
                @if ($editingRuleId)
                    <button
                        type="button"
                        wire:click="deleteRule({{ $editingRuleId }})"
                        wire:confirm="Delete this discount rule?"
                        class="inline-flex items-center rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                    >
                        Delete Rule
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <button type="button" wire:click="closeDrawer" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</button>
                <button type="button" wire:click="save" class="rounded-2xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    {{ $editingRuleId ? 'Save Changes' : 'Create Rule' }}
                </button>
            </div>
        </div>
    </aside>
</div>
