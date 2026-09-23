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

    public int $activityPage = 1;
    public bool $activityLoaded = false;

    #[Url(keep: true)]
    public string $search = '';

    public function refreshMonitor(): void
    {
    }

    public function loadActivity(): void
    {
        $this->activityLoaded = true;
    }

    #[On('echo-private:admin.product-activity,.ProductActivityUpdated')]
    public function refreshRealtime(): void
    {
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
        $this->activityLoaded = true;
    }

    public function render(ProductActivityMonitorService $activityService)
    {
        $searchTerm = $this->generateSearchQuery($this->search, [
            'product_activity_events.product_id',
        ]);

        $dateWindow = [
            'date_sections' => [],
            'current_page' => 1,
            'last_page' => 1,
            'total_days' => 0,
            'days_per_page' => 10,
            'has_newer' => false,
            'has_older' => false,
        ];
        $recentEventDates = collect();

        if ($this->activityLoaded) {
            $dateWindow = $activityService->recentEventDateWindow(
                daysPerPage: 10,
                page: $this->activityPage,
                searchTerm: $searchTerm
            );

            $this->activityPage = $dateWindow['current_page'];
            $recentEventDates = collect($dateWindow['date_sections'])
                ->sortByDesc('date_key')
                ->values();
        }

        $activeSessions = $activityService->activeSessions();

        return view('livewire.product-activity-monitor', [
            'stats' => [
                'active' => $activeSessions->count(),
            ],
            'activeSessions' => $activeSessions,
            'recentEventDates' => $recentEventDates,
            'dateWindow' => $dateWindow,
            'search' => $this->search,
            'activityLoaded' => $this->activityLoaded,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
