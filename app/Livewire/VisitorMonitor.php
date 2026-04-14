<?php

namespace App\Livewire;

use App\SearchCriteriaTrait;
use App\Services\VisitorTrackingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class VisitorMonitor extends Component
{
    use SearchCriteriaTrait;
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->resetPage('knownPage');
        $this->resetPage('returningPage');
        $this->resetPage('leftPage');
    }

    public function refreshMonitor(): void
    {
    }

    public function render()
    {
        $trackingService = app(VisitorTrackingService::class);
        $searchTerm = $this->generateSearchQuery($this->search, $this->searchColumns());
        $knownCustomers = $this->mapPaginator(
            $trackingService->knownCustomersHistory(12, $searchTerm),
            $trackingService,
        );
        $returningVisitors = $this->mapPaginator(
            $trackingService->returningVisitorsHistory(12, $searchTerm),
            $trackingService,
        );
        $totalVisits = $this->mapPaginator(
            $trackingService->totalVisitsHistory(12, $searchTerm),
            $trackingService,
        );

        return view('livewire.visitor-monitor', [
            'stats' => $trackingService->stats($searchTerm),
            'activeVisitors' => $trackingService->activeVisitors($searchTerm),
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

    private function searchColumns(): array
    {
        return [
            'visitor_sessions.ip_address',
            'visitor_sessions.current_path',
            'visitor_sessions.current_url',
            'visitor_sessions.landing_path',
            'visitor_sessions.landing_url',
            'visitor_sessions.referrer_url',
            'visitor_sessions.referrer_host',
        ];
    }
}
