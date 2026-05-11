<?php

namespace App\Livewire;

use App\SearchCriteriaTrait;
use App\Services\ProductActivityMonitorService;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProductActivityMonitor extends Component
{
    use SearchCriteriaTrait;

    public array $expandedDates = [];
    public int $activityPage = 1;

    #[Url(keep: true)]
    public string $search = '';

    public function refreshMonitor(): void
    {
    }

    #[On('echo-private:admin.product-activity,.ProductActivityUpdated')]
    public function refreshRealtime(): void
    {
    }

    public function toggleDateSection(string $dateKey): void
    {
        if (in_array($dateKey, $this->expandedDates, true)) {
            $this->expandedDates = array_values(array_filter(
                $this->expandedDates,
                fn (string $expandedDate) => $expandedDate !== $dateKey
            ));

            return;
        }

        $this->expandedDates[] = $dateKey;
    }

    public function showOlderDates(): void
    {
        $this->activityPage++;
        $this->dispatch('activity-page-changed', direction: 'older');
    }

    public function showNewerDates(): void
    {
        $this->activityPage = max($this->activityPage - 1, 1);
        $this->dispatch('activity-page-changed', direction: 'newer');
    }

    public function updatingSearch(): void
    {
        $this->activityPage = 1;
        $this->expandedDates = [];
    }

    public function render(ProductActivityMonitorService $activityService)
    {
        $searchTerm = $this->generateSearchQuery($this->search, [
            'product_activity_events.product_id',
        ]);

        $dateWindow = $activityService->recentEventDateWindow(
            daysPerPage: 10,
            page: $this->activityPage,
            searchTerm: $searchTerm
        );

        $this->activityPage = $dateWindow['current_page'];

        $recentEventDates = collect($dateWindow['date_sections'])
            ->sortByDesc('date_key')
            ->values();
        $dateKeys = $recentEventDates
            ->pluck('date_key')
            ->values();

        if (trim($this->search) !== '') {
            $this->expandedDates = $dateKeys->all();
        } else {
            $this->expandedDates = array_values(array_intersect($this->expandedDates, $dateKeys->all()));
        }

        return view('livewire.product-activity-monitor', [
            'stats' => $activityService->stats(),
            'activeSessions' => $activityService->activeSessions(),
            'recentEventDates' => $recentEventDates,
            'dateWindow' => $dateWindow,
            'search' => $this->search,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
