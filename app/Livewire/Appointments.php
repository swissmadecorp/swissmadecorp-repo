<?php

namespace App\Livewire;

use App\Services\AppointmentOverviewService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Appointments extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public string $search = '';

    #[Url(keep: true)]
    public string $filter = 'upcoming';

    #[Url(keep: true)]
    public string $dateFrom = '';

    #[Url(keep: true)]
    public string $dateTo = '';

    public bool $showBookingSourceNotice = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function showBookingOriginNoticeModal(): void
    {
        $this->showBookingSourceNotice = true;
    }

    public function render(AppointmentOverviewService $appointments): mixed
    {
        return view('livewire.appointments', [
            'appointments' => $appointments->paginate([
                'search' => $this->search,
                'filter' => $this->filter,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
            ], 8),
            'stats' => $appointments->stats(),
            'urgentBanner' => $appointments->urgentBanner(),
            'todayAgenda' => $appointments->todayAgenda(),
        ])
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Appointments'])
            ->title('Appointments');
    }
}
