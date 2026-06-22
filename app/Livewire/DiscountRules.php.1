<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\DiscountRule;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountRules extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public string $search = '';

    public string $statusFilter = 'all';
    public string $scopeFilter = 'all';
    public bool $drawerOpen = false;
    public ?int $editingRuleId = null;

    public string $ruleName = '';
    public string $metaTitle = '';
    public string $description = '';
    public string $amount = '';
    public int $action = 5;
    public bool $freeShipping = false;
    public bool $isActive = true;
    public string $discountCode = '';
    public string $scopeType = 'brand';
    public string $startDate = '';
    public string $endDate = '';

    public string $brandSearch = '';
    public string $productSearch = '';
    public array $selectedBrandIds = [];
    public array $selectedProductIds = [];

    public string $flashMessage = '';
    public string $flashType = 'success';

    protected function rules(): array
    {
        $rules = [
            'ruleName' => ['required', 'string', 'max:255'],
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'action' => ['required', 'integer'],
            'discountCode' => ['nullable', 'string', 'max:255'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'scopeType' => ['required', 'in:brand,product'],
            'selectedBrandIds' => ['array'],
            'selectedBrandIds.*' => ['integer', 'exists:categories,id'],
            'selectedProductIds' => ['array'],
            'selectedProductIds.*' => ['integer', 'exists:products,id'],
        ];

        if ($this->usesTargeting()) {
            if ($this->requiresBrandScope()) {
                $rules['selectedBrandIds'][] = 'min:1';
            } else {
                $rules['selectedProductIds'][] = 'min:1';
            }
        }

        return $rules;
    }

    protected array $messages = [
        'selectedBrandIds.min' => 'Choose at least one brand for this rule.',
        'selectedProductIds.min' => 'Choose at least one product for this rule.',
    ];

    public function mount(): void
    {
        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedScopeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        if ($this->requiresBrandScope()) {
            $this->scopeType = 'brand';
            $this->selectedProductIds = [];
            $this->productSearch = '';
        }

        if ($this->requiresProductScope()) {
            $this->scopeType = 'product';
            $this->selectedBrandIds = [];
            $this->brandSearch = '';
        }

        if (! $this->usesTargeting()) {
            $this->brandSearch = '';
            $this->productSearch = '';
            $this->selectedBrandIds = [];
            $this->selectedProductIds = [];
        }

        if ($this->action === 4) {
            $this->scopeType = 'brand';
            $this->selectedBrandIds = [];
            $this->selectedProductIds = [];
        }
    }

    public function createNew(): void
    {
        $this->resetForm();
        $this->drawerOpen = true;
    }

    public function editRule(int $ruleId): void
    {
        $rule = DiscountRule::findOrFail($ruleId);

        $this->editingRuleId = $rule->id;
        $this->ruleName = $rule->rule_name ?? '';
        $this->metaTitle = $rule->title ?? '';
        $this->description = $rule->description ?? '';
        $this->amount = $rule->amount !== null ? (string) $rule->amount : '';
        $this->action = (int) $rule->action;
        $this->freeShipping = (bool) $rule->free_shipping;
        $this->isActive = (bool) $rule->is_active;
        $this->discountCode = $rule->discount_code ?? '';
        $this->scopeType = $rule->usesBrandScope() ? 'brand' : 'product';
        if ($this->requiresBrandScope()) {
            $this->scopeType = 'brand';
        }
        if ($this->requiresProductScope()) {
            $this->scopeType = 'product';
        }
        $this->startDate = optional($rule->start_date)->format('Y-m-d') ?? now()->toDateString();
        $this->endDate = optional($rule->end_date)->format('Y-m-d') ?? now()->toDateString();
        $this->selectedBrandIds = $rule->selectedBrandIds();
        $this->selectedProductIds = $rule->selectedProductIds();
        $this->brandSearch = '';
        $this->productSearch = '';
        $this->drawerOpen = true;
    }

    public function closeDrawer(): void
    {
        $this->drawerOpen = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        $payload = [
            'rule_name' => trim($this->ruleName),
            'title' => $this->emptyToNull($this->metaTitle),
            'description' => $this->emptyToNull($this->description),
            'amount' => $this->amount === '' ? 0 : $this->amount,
            'action' => $this->action,
            'free_shipping' => $this->freeShipping ? 1 : 0,
            'is_active' => $this->isActive ? 1 : 0,
            'discount_code' => $this->emptyToNull($this->discountCode),
            'scope_type' => $this->usesTargeting() ? ($this->requiresBrandScope() ? 'brand' : 'product') : 'product',
            'brand_ids' => $this->requiresBrandScope() ? $this->selectedBrandIds : null,
            'product' => $this->usesTargeting() && ! $this->requiresBrandScope() ? $this->selectedProductIds : null,
            'start_date' => $this->startDate ?: now()->toDateString(),
            'end_date' => $this->endDate ?: now()->toDateString(),
        ];

        if ($this->editingRuleId) {
            DiscountRule::findOrFail($this->editingRuleId)->update($payload);
            session()->flash('discount_rules_flash_message', 'Discount rule updated successfully.');
            session()->flash('discount_rules_flash_type', 'success');
        } else {
            DiscountRule::create($payload);
            session()->flash('discount_rules_flash_message', 'Discount rule created successfully.');
            session()->flash('discount_rules_flash_type', 'success');
        }

        $this->closeDrawer();
        $this->resetPage();
        return $this->redirectRoute('discountrules.index');
    }

    public function deleteRule(int $ruleId)
    {
        $rule = DiscountRule::findOrFail($ruleId);
        $rule->delete();

        if ($this->editingRuleId === $ruleId) {
            $this->closeDrawer();
        }

        session()->flash('discount_rules_flash_message', 'Discount rule deleted successfully.');
        session()->flash('discount_rules_flash_type', 'success');
        $this->resetPage();
        return $this->redirectRoute('discountrules.index');
    }

    public function toggleBrandSelection(int $brandId): void
    {
        if (in_array($brandId, $this->selectedBrandIds, true)) {
            $this->selectedBrandIds = array_values(array_diff($this->selectedBrandIds, [$brandId]));
            return;
        }

        $this->selectedBrandIds[] = $brandId;
    }

    public function removeBrand(int $brandId): void
    {
        $this->selectedBrandIds = array_values(array_diff($this->selectedBrandIds, [$brandId]));
    }

    public function addProduct(int $productId): void
    {
        if (! in_array($productId, $this->selectedProductIds, true)) {
            $this->selectedProductIds[] = $productId;
        }
    }

    public function removeProduct(int $productId): void
    {
        $this->selectedProductIds = array_values(array_diff($this->selectedProductIds, [$productId]));
    }

    public function clearFlash(): void
    {
        $this->flashMessage = '';
    }

    public function getActionsProperty()
    {
        return \DiscountRules();
    }

    public function getAvailableBrandsProperty()
    {
        return Category::query()
            ->select('id', 'category_name')
            ->when(strlen(trim($this->brandSearch)) > 0, function ($query) {
                $query->where('category_name', 'like', '%' . trim($this->brandSearch) . '%');
            })
            ->orderBy('category_name')
            ->limit(20)
            ->get();
    }

    public function getSelectedBrandsProperty()
    {
        if (count($this->selectedBrandIds) === 0) {
            return collect();
        }

        return Category::query()
            ->select('id', 'category_name')
            ->whereIn('id', $this->selectedBrandIds)
            ->orderBy('category_name')
            ->get();
    }

    public function getSelectedProductsProperty()
    {
        if (count($this->selectedProductIds) === 0) {
            return collect();
        }

        return Product::query()
            ->select('products.id', 'products.title', 'products.category_id', 'products.p_reference', 'products.p_model')
            ->with('categories:id,category_name')
            ->whereIn('products.id', $this->selectedProductIds)
            ->orderBy('products.id', 'desc')
            ->get();
    }

    public function getProductResultsProperty()
    {
        $term = trim($this->productSearch);

        if (strlen($term) < 2) {
            return collect();
        }

        return Product::query()
            ->select('products.id', 'products.title', 'products.category_id', 'products.p_reference', 'products.p_model')
            ->with('categories:id,category_name')
            ->where('products.p_qty', '>', 0)
            ->where(function ($query) use ($term) {
                $query->where('products.id', 'like', '%' . $term . '%')
                    ->orWhere('products.title', 'like', '%' . $term . '%')
                    ->orWhere('products.p_model', 'like', '%' . $term . '%')
                    ->orWhere('products.p_reference', 'like', '%' . $term . '%');
            })
            ->whereNotIn('products.id', $this->selectedProductIds)
            ->orderBy('products.id', 'desc')
            ->limit(12)
            ->get();
    }

    public function getStatsProperty(): array
    {
        $rules = DiscountRule::all();

        return [
            'active' => $rules->filter(fn (DiscountRule $rule) => $this->statusFor($rule)['label'] === 'Active')->count(),
            'scheduled' => $rules->filter(fn (DiscountRule $rule) => $this->statusFor($rule)['label'] === 'Scheduled')->count(),
            'brand' => $rules->filter(fn (DiscountRule $rule) => $rule->usesBrandScope() && $rule->usesTargeting())->count(),
            'product' => $rules->filter(fn (DiscountRule $rule) => $rule->usesProductScope() && $rule->usesTargeting())->count(),
        ];
    }

    public function statusFor(DiscountRule $rule): array
    {
        $today = now()->toDateString();
        $start = optional($rule->start_date)->format('Y-m-d');
        $end = optional($rule->end_date)->format('Y-m-d');

        if (! $rule->is_active) {
            return ['label' => 'Inactive', 'classes' => 'bg-slate-100 text-slate-700 ring-slate-200'];
        }

        if ($start && $start > $today) {
            return ['label' => 'Scheduled', 'classes' => 'bg-amber-100 text-amber-800 ring-amber-200'];
        }

        if ($end && $end < $today) {
            return ['label' => 'Expired', 'classes' => 'bg-rose-100 text-rose-700 ring-rose-200'];
        }

        return ['label' => 'Active', 'classes' => 'bg-emerald-100 text-emerald-700 ring-emerald-200'];
    }

    public function targetSummary(DiscountRule $rule): string
    {
        if ((int) $rule->action === 4) {
            return 'All Products';
        }

        if (! $rule->usesTargeting()) {
            return 'Whole Cart';
        }

        if ($rule->appliesToAllProducts()) {
            return 'All Products';
        }

        if ($rule->usesBrandScope()) {
            $count = count($rule->selectedBrandIds());
            return $count === 1 ? '1 Brand' : $count . ' Brands';
        }

        $count = count($rule->selectedProductIds());
        return $count === 1 ? '1 Product' : $count . ' Products';
    }

    public function targetDetails(DiscountRule $rule): string
    {
        if ((int) $rule->action === 4) {
            return 'Site-wide';
        }

        if (! $rule->usesTargeting()) {
            return 'No item targeting';
        }

        if ($rule->usesBrandScope()) {
            $names = Category::query()
                ->whereIn('id', $rule->selectedBrandIds())
                ->orderBy('category_name')
                ->pluck('category_name')
                ->take(3)
                ->implode(', ');

            return $names ?: 'All brands';
        }

        return count($rule->selectedProductIds()) . ' selected';
    }

    public function formatActionLabel(int $action): string
    {
        return (string) ($this->actions->get($action) ?? 'Discount Rule');
    }

    public function discountSummary(DiscountRule $rule): string
    {
        if (in_array((int) $rule->action, [1, 2, 4, 5], true)) {
            return rtrim(rtrim(number_format((float) $rule->amount, 2), '0'), '.') . '%';
        }

        return '$' . number_format((float) $rule->amount, 2);
    }

    public function render()
    {
        $today = now()->toDateString();

        $rules = DiscountRule::query()
            ->when(strlen(trim($this->search)) > 0, function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('rule_name', 'like', '%' . trim($this->search) . '%')
                        ->orWhere('discount_code', 'like', '%' . trim($this->search) . '%')
                        ->orWhere('title', 'like', '%' . trim($this->search) . '%')
                        ->orWhere('description', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->when($this->statusFilter === 'active', function ($query) use ($today) {
                $query->where('is_active', 1)
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->when($this->statusFilter === 'scheduled', function ($query) use ($today) {
                $query->where('is_active', 1)
                    ->where('start_date', '>', $today);
            })
            ->when($this->statusFilter === 'expired', function ($query) use ($today) {
                $query->where('end_date', '<', $today);
            })
            ->when($this->statusFilter === 'inactive', function ($query) {
                $query->where('is_active', 0);
            })
            ->when($this->scopeFilter === 'brand', function ($query) {
                $query->where('scope_type', 'brand')
                    ->where('action', 5);
            })
            ->when($this->scopeFilter === 'product', function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->whereNull('scope_type')
                        ->orWhere('scope_type', 'product');
                })->whereIn('action', [2, 3, 5]);
            })
            ->when($this->scopeFilter === 'all-products', function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery->where('action', 4)
                        ->orWhere(function ($fallbackQuery) {
                            $fallbackQuery->whereIn('action', [2, 5])
                                ->whereNull('product')
                                ->whereNull('brand_ids');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('livewire.discount-rules', [
            'rules' => $rules,
        ]);
    }

    private function usesTargeting(): bool
    {
        return in_array($this->action, [2, 3, 5], true);
    }

    private function requiresBrandScope(): bool
    {
        return $this->action === 5;
    }

    private function requiresProductScope(): bool
    {
        return in_array($this->action, [2, 3], true);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingRuleId = null;
        $this->ruleName = '';
        $this->metaTitle = '';
        $this->description = '';
        $this->amount = '';
        $this->action = 5;
        $this->freeShipping = false;
        $this->isActive = true;
        $this->discountCode = '';
        $this->scopeType = 'brand';
        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
        $this->brandSearch = '';
        $this->productSearch = '';
        $this->selectedBrandIds = [];
        $this->selectedProductIds = [];
    }

    private function showFlash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    private function emptyToNull(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : $value;
    }
}
