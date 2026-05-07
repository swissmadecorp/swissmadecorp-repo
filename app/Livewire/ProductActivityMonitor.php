<?php

namespace App\Livewire;

use App\Services\ProductActivityMonitorService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductActivityMonitor extends Component
{
    use WithPagination;

    public array $expandedDates = [];
    protected string $paginationTheme = 'tailwind';

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

    public function render(ProductActivityMonitorService $activityService)
    {
        $recentEventsPaginator = $activityService->paginatedRecentEvents(
            perPage: 10,
            page: $this->getPage(),
            pageName: $this->getPageName()
        );

        $recentEvents = $recentEventsPaginator->getCollection();
        $dateKeys = $recentEvents
            ->pluck('display_date_key')
            ->unique()
            ->values();

        $this->expandedDates = array_values(array_intersect($this->expandedDates, $dateKeys->all()));

        return view('livewire.product-activity-monitor', [
            'stats' => $activityService->stats(),
            'activeSessions' => $activityService->activeSessions(),
            'recentEvents' => $recentEvents,
            'recentEventsPaginator' => $recentEventsPaginator,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
