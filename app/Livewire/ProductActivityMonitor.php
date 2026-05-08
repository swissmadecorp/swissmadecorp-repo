<?php

namespace App\Livewire;

use App\Services\ProductActivityMonitorService;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductActivityMonitor extends Component
{
    public array $expandedDates = [];
    public int $activityPage = 1;

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
    }

    public function showNewerDates(): void
    {
        $this->activityPage = max($this->activityPage - 1, 1);
    }

    public function render(ProductActivityMonitorService $activityService)
    {
        $dateWindow = $activityService->recentEventDateWindow(
            daysPerPage: 10,
            page: $this->activityPage
        );

        $this->activityPage = $dateWindow['current_page'];

        $recentEventDates = collect($dateWindow['date_sections'])
            ->sortByDesc('date_key')
            ->values();
        $dateKeys = $recentEventDates
            ->pluck('date_key')
            ->values();

        $this->expandedDates = array_values(array_intersect($this->expandedDates, $dateKeys->all()));

        return view('livewire.product-activity-monitor', [
            'stats' => $activityService->stats(),
            'activeSessions' => $activityService->activeSessions(),
            'recentEventDates' => $recentEventDates,
            'dateWindow' => $dateWindow,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
