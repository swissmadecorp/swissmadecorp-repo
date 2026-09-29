<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Reminder;
use App\Services\LegacyReminderDetails;
use App\Services\WatchReminderMatcher;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Reminders extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';
    #[Url]
    public $filter = '';
    #[Locked]
    public $key = null;
    public $showEditor = false;
    public $reminder = [];
    public $legacyCriteria = '';

    public function mount(): void
    {
        $reminderId = request()->query('reminder');
        if ($reminderId !== null) {
            abort_unless(is_scalar($reminderId) && ctype_digit((string) $reminderId) && (int) $reminderId > 0, 404);
            $this->loadReminder((int) $reminderId);
        }
    }

    private function authorizeWrite(): void
    {
        abort_unless(auth()->user()?->hasRole('administrator'), 403);
    }

    protected function rules(): array
    {
        return [
            'reminder.customer_name' => ['required', 'string', 'max:255'],
            'reminder.customer_email' => ['nullable', 'email', 'max:255'],
            'reminder.customer_phone' => ['nullable', 'string', 'max:80'],
            'reminder.category_id' => ['required', Rule::exists('categories', 'id')],
            'reminder.watch_model' => ['required', 'string', 'max:255'],
            'reminder.watch_reference' => ['nullable', 'string', 'max:100'],
            'reminder.case_size' => ['nullable', 'numeric', 'gt:0', 'max:100'],
            'reminder.dial_color' => ['nullable', 'string', 'max:100'],
            'reminder.product_condition' => ['array'],
            'reminder.product_condition.*' => [Rule::in(Conditions()->keys()->filter(fn ($key) => $key > 0)->all())],
            'reminder.boxpapers' => ['array'],
            'reminder.boxpapers.*' => [Rule::in(['Box', 'Papers'])],
            'reminder.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected $validationAttributes = [
        'reminder.customer_name' => 'full name', 'reminder.customer_email' => 'email address',
        'reminder.customer_phone' => 'phone number', 'reminder.category_id' => 'brand',
        'reminder.watch_model' => 'model', 'reminder.watch_reference' => 'reference',
        'reminder.case_size' => 'case size', 'reminder.dial_color' => 'dial color',
        'reminder.notes' => 'notes',
    ];

    public function newReminder(): void
    {
        $this->authorizeWrite();
        $this->resetValidation();
        $this->key = null;
        $this->legacyCriteria = '';
        $this->reminder = array_fill_keys(['customer_name', 'customer_email', 'customer_phone',
            'category_id', 'watch_model', 'watch_reference', 'case_size', 'dial_color', 'notes'], '');
        $this->reminder['product_condition'] = [];
        $this->reminder['boxpapers'] = [];
        $this->showEditor = true;
    }

    public function loadReminder($id): void
    {
        $this->newReminder();
        $record = Reminder::findOrFail($id);
        $this->key = $record->id;
        foreach (array_keys($this->reminder) as $field) {
            $this->reminder[$field] = $record->$field ?? '';
        }
        $this->reminder['customer_name'] = $record->customer_name ?: $record->assigned_to;
        $this->reminder['case_size'] = WatchReminderMatcher::size($record->case_size);
        $missingWatchDetails = collect(['category_id', 'watch_model', 'watch_reference', 'case_size'])
            ->contains(fn ($field) => blank($this->reminder[$field]));
        if ($missingWatchDetails) {
            foreach (app(LegacyReminderDetails::class)->fromCriteria($record->criteria) as $field => $value) {
                if (blank($this->reminder[$field])) $this->reminder[$field] = $value;
            }
        }
        $this->reminder['product_condition'] = array_map('strval', $record->preferences('product_condition'));
        $this->reminder['boxpapers'] = $record->preferences('boxpapers');
        $this->legacyCriteria = $missingWatchDetails ? $record->criteria : '';
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->resetValidation();
        $this->dispatch('reminder-editor-closed');
    }

    public function saveReminder(): void
    {
        $this->authorizeWrite();
        foreach ($this->reminder as $field => $value) {
            if (is_string($value)) $this->reminder[$field] = trim($value);
        }
        $data = $this->validate()['reminder'];
        $data['case_size'] = WatchReminderMatcher::size($data['case_size']);
        $brand = Category::findOrFail($data['category_id']);
        $criteriaParts = [$brand->category_name, $data['watch_model']];
        if (filled($data['case_size'])) $criteriaParts[] = $data['case_size'].'mm';
        if (filled($data['watch_reference'])) $criteriaParts[] = $data['watch_reference'];
        $data['criteria'] = implode(' ', $criteriaParts);
        $data['pagename'] = 'product';
        $data['assigned_to'] = $data['customer_name'];
        $data['action'] = 'Contact customer';
        $record = $this->key ? Reminder::findOrFail($this->key) : new Reminder(['status' => Reminder::WATCHING]);
        $record->fill($data);
        // Changing watch requirements starts a new search. Editing contacts preserves the match.
        if ($record->exists && $record->isDirty(['category_id', 'watch_model', 'watch_reference',
            'case_size', 'dial_color', 'product_condition', 'boxpapers'])) {
            $record->fill(['status' => Reminder::WATCHING, 'matched_product_id' => null, 'matched_at' => null, 'contacted_at' => null]);
        }
        $record->save();
        app(WatchReminderMatcher::class)->checkExistingStock($record);
        $this->closeEditor();
        session()->flash('message', $record->fresh()->status === Reminder::MATCHED
            ? 'Reminder saved. A matching watch is already in stock — contact the customer below.' : 'Reminder saved. We will flag matching inventory here.');
    }

    public function markContacted($id): void
    {
        $this->authorizeWrite();
        Reminder::whereKey($id)->where('status', Reminder::MATCHED)
            ->update(['status' => Reminder::CONTACTED, 'contacted_at' => now()]);
        session()->flash('message', 'Customer marked as contacted.');
    }

    public function resumeWatching($id): void
    {
        $this->authorizeWrite();
        $record = Reminder::findOrFail($id);
        $record->update(['status' => Reminder::WATCHING, 'matched_product_id' => null, 'matched_at' => null, 'contacted_at' => null]);
        // Wait for the next inventory change instead of immediately matching existing stock again.
        $this->filter = '0';
        $this->resetPage();
        session()->flash('message', 'Watching inventory again. We will notify you when a matching watch is added or updated.');
    }

    public function deleteReminder($id): void
    {
        $this->authorizeWrite();
        Reminder::findOrFail($id)->delete();
        $this->resetPage();
        session()->flash('message', 'Reminder removed.');
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void { $this->resetPage(); }

    public function render()
    {
        $query = Reminder::with(['category', 'matchedProduct']);
        foreach (preg_split('/\s+/', trim($this->search), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $query->where(function ($query) use ($word) {
                foreach (['criteria', 'customer_name', 'customer_email', 'customer_phone', 'assigned_to'] as $column) {
                    $query->orWhere($column, 'like', '%'.$word.'%');
                }
            });
        }
        if (in_array((string) $this->filter, ['0', '1', '2'], true)) $query->where('status', $this->filter);
        return view('livewire.reminders', [
            'reminders' => $query->orderByRaw('CASE WHEN status = 1 THEN 0 WHEN status = 0 THEN 1 ELSE 2 END')->latest()->paginate(10),
            'counts' => Reminder::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'brands' => Category::orderBy('category_name')->get(['id', 'category_name']),
        ])->layout('components.layouts.admin')->layoutData(['pageName' => 'Reminders'])->title('Reminders');
    }
}
