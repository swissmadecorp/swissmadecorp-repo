<?php

namespace App\Livewire;

use App\Services\ProductActivityMonitorService;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductActivityMonitor extends Component
{
    public function refreshMonitor(): void
    {
    }

    #[On('echo-private:admin.product-activity,.ProductActivityUpdated')]
    public function refreshRealtime(): void
    {
    }

    public function render(ProductActivityMonitorService $activityService)
    {
        return view('livewire.product-activity-monitor', [
            'stats' => $activityService->stats(),
            'activeSessions' => $activityService->activeSessions(),
            'recentEvents' => $activityService->recentEvents(),
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Product Activity'])
            ->title('Product Activity');
    }
}
