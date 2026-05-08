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
    protected string $activityPageName = 'activityPage';

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
        $currentPage = $this->getPage($this->activityPageName);

        $this->setPage($currentPage + 1, $this->activityPageName);
    }

    public function showNewerDates(): void
    {
        $currentPage = $this->getPage($this->activityPageName);

        $this->setPage(max($currentPage - 1, 1), $this->activityPageName);
    }

    public function render(ProductActivityMonitorService $activityService)
    {
        $currentPage = $this->getPage($this->activityPageName);
        $recentEventDatesPaginator = $activityService->paginatedRecentEventDates(
            perPage: 10,
            page: $currentPage,
            pageName: $this->activityPageName
        );

        $recentEventDates = $recentEventDatesPaginator->getCollection()
            ->sortByDesc('date_key')
            ->values();
        $dateKeys = $recentEventDates
            ->pluck('date_key')
            ->values();

        $this->expandedDates = array_values(array_intersect($this->expandedDates, $dateKeys->all()));

        $recentEventDatesPaginator->setCollection($recentEventDates);

        return view('livewire.product-activity-monitor', [
            'stats' => $activityService->stats(),
            'activeSessions' => $activityService->activeSessions(),
            'recentEventDates' => $recentEventDates,
            'recentEventDatesPaginator' => $recentEventDatesPaginator,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
