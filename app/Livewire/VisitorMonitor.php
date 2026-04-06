<?php

namespace App\Livewire;

use App\Services\VisitorTrackingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        $knownCustomers = $this->mapPaginator(
            $trackingService->knownCustomersHistory(12),
            $trackingService,
        );
        $returningVisitors = $this->mapPaginator(
            $trackingService->returningVisitorsHistory(12),
            $trackingService,
        );
        $totalVisits = $this->mapPaginator(
            $trackingService->totalVisitsHistory(12),
            $trackingService,
        );

        return view('livewire.visitor-monitor', [
            'stats' => $trackingService->stats(),
            'activeVisitors' => $trackingService->activeVisitors(),
            'knownCustomers' => $knownCustomers,
            'returningVisitors' => $returningVisitors,
            'totalVisits' => $totalVisits,
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Visitor Monitor'])
            ->title('Visitor Monitor');
    }

    private function mapPaginator(LengthAwarePaginator $paginator, VisitorTrackingService $trackingService): LengthAwarePaginator
    {
        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn ($session) => $trackingService->sessionSummary($session, false)
            )
        );

        return $paginator;
    }
}
