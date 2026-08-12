<?php

namespace App\Livewire;

use App\Libs\MassMail as InventoryEmail;
use App\Models\Category;
use App\Models\MailMass;
use App\Models\Newsletter;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class MailMassManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $title = '';

    public string $content = '';

    public array $categoryIds = [];

    public bool $active = false;

    public string $search = '';

    public string $subscriberSearch = '';

    public string $subscriberSort = 'created_at';

    public string $subscriberSortDirection = 'desc';

    public array $selectedSubscriberIds = [];

    public bool $showEditor = false;

    public function mount(int|string|null $campaign = null, ?string $editor = null): void
    {
        if (filled($campaign)) {
            $this->edit((int) $campaign);
        } elseif ($editor === 'create') {
            $this->createCampaign();
        } elseif ($activeCampaign = MailMass::query()->where('is_active', true)->latest('updated_at')->first()) {
            $this->edit($activeCampaign->id);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSubscriberSearch(): void
    {
        $this->resetPage('subscribersPage');
    }

    public function sortSubscribers(string $column): void
    {
        if (! in_array($column, ['email', 'subscribed', 'validation', 'created_at'], true)) {
            return;
        }

        if ($this->subscriberSort === $column) {
            $this->subscriberSortDirection = $this->subscriberSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->subscriberSort = $column;
            $this->subscriberSortDirection = 'asc';
        }

        $this->resetPage('subscribersPage');
    }

    public function createCampaign(): void
    {
        $this->resetEditor();
        $this->content = $this->standardTemplate();
        $this->showEditor = true;
        $this->dispatch('mail-editor-content', content: $this->content);
    }

    public function edit(int $id): void
    {
        $campaign = MailMass::findOrFail($id);

        $this->editingId = $campaign->id;
        $this->title = $campaign->title ?? '';
        $this->content = $campaign->content ?? '';
        $this->categoryIds = $campaign->categoryIds();
        $this->active = (bool) $campaign->is_active;
        $this->showEditor = true;
        $this->resetValidation();
        $this->dispatch('mail-editor-content', content: $this->content);
    }

    public function cancelEditor(): void
    {
        $this->resetEditor();
        $this->dispatch('mail-editor-destroy');
    }

    public function loadStandardTemplate(): void
    {
        $this->content = $this->standardTemplate();
        $this->dispatch('mail-editor-content', content: $this->content);
    }

    public function refreshInventory(?string $editorContent = null): void
    {
        if ($editorContent !== null) {
            $this->content = $editorContent;
        }

        $this->content = InventoryEmail::process([
            'category' => $this->categoryIds,
            'loadWithTemplate' => false,
            'template' => $this->content ?: $this->standardTemplate(),
        ]);

        $this->dispatch('mail-editor-content', content: $this->content);
        session()->flash('campaign_message', 'The latest available watches were added to the email.');
    }

    public function save(?string $editorContent = null): void
    {
        if ($editorContent !== null) {
            $this->content = $editorContent;
        }

        $wasCreating = $this->editingId === null;

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'categoryIds' => ['array'],
            'categoryIds.*' => ['integer', Rule::exists('categories', 'id')],
            'active' => ['boolean'],
        ]);

        $campaign = DB::transaction(function () use ($validated) {
            if ($this->active) {
                MailMass::query()
                    ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
                    ->update(['is_active' => false]);
            }

            return MailMass::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'title' => trim($validated['title']),
                    'content' => $validated['content'],
                    'category' => $validated['categoryIds'] === []
                        ? ''
                        : serialize(array_map('intval', $validated['categoryIds'])),
                    'is_active' => $validated['active'],
                ],
            );
        });

        $this->editingId = $campaign->id;
        session()->flash('campaign_message', 'Campaign saved.');

        if ($wasCreating) {
            $this->redirectRoute('massmail.edit', ['campaign' => $campaign->id], navigate: true);
        }
    }

    public function delete(int $id): void
    {
        MailMass::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->resetEditor();
            $this->dispatch('mail-editor-destroy');
        }

        session()->flash('campaign_message', 'Campaign deleted.');
    }

    public function deleteSubscriber(int $id): void
    {
        Newsletter::findOrFail($id)->delete();
        $this->selectedSubscriberIds = array_values(array_filter(
            $this->selectedSubscriberIds,
            fn ($selectedId) => (int) $selectedId !== $id,
        ));

        session()->flash('subscriber_message', 'Email address permanently removed.');
    }

    public function toggleSubscriberPage(array $ids): void
    {
        $pageIds = collect($ids)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedIds = collect($this->selectedSubscriberIds)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique();

        $allPageIdsSelected = $pageIds->isNotEmpty()
            && $pageIds->every(fn ($id) => $selectedIds->contains($id));

        $this->selectedSubscriberIds = ($allPageIdsSelected
            ? $selectedIds->reject(fn ($id) => $pageIds->contains($id))
            : $selectedIds->merge($pageIds)->unique())
            ->values()
            ->all();
    }

    public function deleteSelectedSubscribers(): void
    {
        $ids = collect($this->selectedSubscriberIds)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $deleted = Newsletter::query()->whereKey($ids)->delete();
        $this->selectedSubscriberIds = [];

        session()->flash(
            'subscriber_message',
            $deleted.' '.str('email address')->plural($deleted).' permanently removed.',
        );
    }

    private function resetEditor(): void
    {
        $this->reset(['editingId', 'title', 'content', 'categoryIds', 'active', 'showEditor']);
        $this->resetValidation();
    }

    private function standardTemplate(): string
    {
        $path = public_path('template/mass-mail-tinymce.html');

        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    public function render()
    {
        $subscriberSort = in_array($this->subscriberSort, ['email', 'subscribed', 'validation', 'created_at'], true)
            ? $this->subscriberSort
            : 'created_at';
        $subscriberSortDirection = $this->subscriberSortDirection === 'asc' ? 'asc' : 'desc';

        $campaigns = MailMass::query()
            ->when($this->search, fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->orderByDesc('is_active')
            ->latest('updated_at')
            ->paginate(10);

        $subscriberQuery = Newsletter::query()
            ->when($this->subscriberSearch, function ($query) {
                $query->where('email', 'like', '%'.$this->subscriberSearch.'%');
            });

        if ($subscriberSort === 'validation') {
            $page = $this->getPage('subscribersPage');
            $perPage = 15;
            $sortedSubscribers = $subscriberQuery
                ->orderByDesc('id')
                ->get()
                ->sortBy(
                    fn (Newsletter $subscriber) => filter_var($subscriber->email, FILTER_VALIDATE_EMAIL) !== false ? 1 : 0,
                    SORT_REGULAR,
                    $subscriberSortDirection === 'desc',
                )
                ->values();

            $subscribers = new LengthAwarePaginator(
                $sortedSubscribers->forPage($page, $perPage),
                $sortedSubscribers->count(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'subscribersPage',
                ],
            );
        } else {
            $subscribers = $subscriberQuery
                ->orderBy($subscriberSort, $subscriberSortDirection)
                ->orderByDesc('id')
                ->paginate(15, ['*'], 'subscribersPage');
        }

        return view('livewire.mail-mass-manager', [
            'campaigns' => $campaigns,
            'subscribers' => $subscribers,
            'subscriberSort' => $subscriberSort,
            'subscriberSortDirection' => $subscriberSortDirection,
            'activeCampaign' => MailMass::query()->where('is_active', true)->latest('updated_at')->first(),
            'categories' => Category::query()->orderBy('category_name')->get(['id', 'category_name']),
            'subscriberCount' => Newsletter::query()->where('subscribed', 1)->count(),
            'recentInventoryCount' => Product::query()
                ->where('created_at', '>=', now()->subWeeks(3)->startOfDay())
                ->where('p_qty', '>', 0)
                ->count(),
        ])->layout('components.layouts.admin')
            ->title('Email Campaigns')
            ->layoutData(['pageName' => 'Email Campaigns']);
    }
}
