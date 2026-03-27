<?php

namespace App\Livewire;

use App\Services\VisitorTrackingService;
use Livewire\Component;
use Livewire\WithPagination;

class VisitorMonitor extends Component
{
    use WithPagination;

    public function refreshMonitor(): void
    {
    }

    public function render()
    {
        $trackingService = app(VisitorTrackingService::class);
        $leftHistory = $trackingService->leftHistory(12);

        $leftHistory->setCollection(
            $leftHistory->getCollection()->map(
                fn ($session) => $trackingService->sessionSummary($session, false)
            )
        );

        return view('livewire.visitor-monitor', [
            'stats' => $trackingService->stats(),
            'activeVisitors' => $trackingService->activeVisitors(),
            'leftHistory' => $leftHistory,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Visitor Monitor'])
            ->title('Visitor Monitor');
    }
}
