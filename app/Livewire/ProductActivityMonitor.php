<?php

namespace App\Livewire;

use App\Services\ProductActivityMonitorService;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductActivityMonitor extends Component
{
    public array $expandedDates = [];

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
        $recentEvents = $activityService->recentEvents();
        $dateKeys = $recentEvents
            ->pluck('display_date_key')
            ->unique()
            ->values();

        if (empty($this->expandedDates) && $dateKeys->isNotEmpty()) {
            $this->expandedDates = [$dateKeys->first()];
        } else {
            $this->expandedDates = array_values(array_intersect($this->expandedDates, $dateKeys->all()));
        }

        return view('livewire.product-activity-monitor', [
            'stats' => $activityService->stats(),
            'activeSessions' => $activityService->activeSessions(),
            'recentEvents' => $recentEvents,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
