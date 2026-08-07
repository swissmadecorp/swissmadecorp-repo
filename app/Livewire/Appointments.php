<?php

namespace App\Livewire;

use App\Models\AppointmentBannerDismissal;
use App\Models\Booking;
use App\Services\AppointmentOverviewService;
use Carbon\Carbon;
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
    public bool $showEditModal = false;
    public ?int $editingAppointmentId = null;
    public string $editContactName = '';
    public string $editPhone = '';
    public string $editEmail = '';
    public string $editDate = '';
    public string $editTime = '';
    public string $editProductLabel = '';

    protected function rules(): array
    {
        return [
            'editContactName' => ['required', 'string', 'max:255'],
            'editPhone' => ['nullable', 'string', 'max:50'],
            'editEmail' => ['nullable', 'email', 'max:255'],
            'editDate' => ['required', 'date'],
            'editTime' => ['required', 'date_format:H:i'],
        ];
    }

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

    public function editAppointment(int $bookingId): void
    {
        $booking = Booking::query()->with(['product'])->findOrFail($bookingId);
        $appointments = app(AppointmentOverviewService::class);
        $mapped = $appointments->map($booking);

        $this->editingAppointmentId = $booking->id;
        $this->editContactName = $booking->contact_name ?? '';
        $this->editPhone = $booking->phone ?? '';
        $this->editEmail = $booking->email ?? '';
        $this->editDate = $mapped['starts_at']->format('Y-m-d');
        $this->editTime = $mapped['starts_at']->format('H:i');
        $this->editProductLabel = $mapped['product_name'] . ' (#' . $mapped['product_id'] . ')';
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal(): void
    {
        $this->resetEditState();
    }

    public function saveAppointment(): void
    {
        $this->validate();

        $booking = Booking::query()->findOrFail($this->editingAppointmentId);

        $booking->update([
            'contact_name' => trim($this->editContactName),
            'phone' => trim($this->editPhone),
            'email' => trim($this->editEmail),
            'book_date' => Carbon::parse($this->editDate . ' ' . $this->editTime, 'America/New_York')->utc(),
        ]);

        AppointmentBannerDismissal::query()->where('booking_id', $booking->id)->delete();

        session()->flash('appointmentMessage', 'Appointment updated successfully.');
        $this->resetEditState();
    }

    public function deleteAppointment(int $bookingId): void
    {
        Booking::query()->findOrFail($bookingId)->delete();
        AppointmentBannerDismissal::query()->where('booking_id', $bookingId)->delete();

        session()->flash('appointmentMessage', 'Appointment canceled and removed.');
        $this->resetPage();

        if ($this->editingAppointmentId === $bookingId) {
            $this->resetEditState();
        }
    }

    private function resetEditState(): void
    {
        $this->showEditModal = false;
        $this->editingAppointmentId = null;
        $this->editContactName = '';
        $this->editPhone = '';
        $this->editEmail = '';
        $this->editDate = '';
        $this->editTime = '';
        $this->editProductLabel = '';
        $this->resetValidation();
    }

    public function render(): mixed
    {
        $appointments = app(AppointmentOverviewService::class);

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
