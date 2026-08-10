<?php

namespace App\Livewire;

use App\Mail\GMailer;
use App\Models\AppointmentBannerDismissal;
use App\Models\Booking;
use App\Models\Product;
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
    public bool $showAppointmentForm = false;
    public bool $isEditingAppointment = false;
    public ?int $appointmentFormId = null;
    public string $formContactName = '';
    public string $formPhone = '';
    public string $formEmail = '';
    public string $formDate = '';
    public string $formTime = '';
    public string $formProductId = '';
    public string $formProductLabel = '';

    protected function rules(): array
    {
        return [
            'formContactName' => ['required', 'string', 'max:255'],
            'formPhone' => ['nullable', 'string', 'max:50'],
            'formEmail' => ['nullable', 'email', 'max:255'],
            'formDate' => ['required', 'date'],
            'formTime' => ['required', 'date_format:H:i'],
            'formProductId' => ['required', 'integer', 'exists:products,id'],
        ];
    }

    protected array $messages = [
        'formContactName.required' => 'Customer name is required.',
        'formDate.required' => 'Appointment date is required.',
        'formTime.required' => 'Appointment time is required.',
        'formTime.date_format' => 'Appointment time must use a valid time.',
        'formProductId.required' => 'Product ID is required.',
        'formProductId.integer' => 'Product ID must be a number.',
        'formProductId.exists' => 'That product ID was not found.',
    ];

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

    public function updatedFormProductId(): void
    {
        $this->refreshProductLabel();
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

    public function openCreateAppointmentModal(): void
    {
        $this->resetAppointmentForm();
        $this->showAppointmentForm = true;
        $this->isEditingAppointment = false;
        $this->formDate = now('America/New_York')->format('Y-m-d');
        $this->formTime = now('America/New_York')->addHour()->startOfHour()->format('H:i');
    }

    public function editAppointment(int $bookingId): void
    {
        $booking = Booking::query()->with(['product'])->findOrFail($bookingId);
        $mapped = app(AppointmentOverviewService::class)->map($booking);

        $this->resetAppointmentForm();
        $this->showAppointmentForm = true;
        $this->isEditingAppointment = true;
        $this->appointmentFormId = $booking->id;
        $this->formContactName = $booking->contact_name ?? '';
        $this->formPhone = $booking->phone ?? '';
        $this->formEmail = $booking->email ?? '';
        $this->formDate = $mapped['starts_at']->format('Y-m-d');
        $this->formTime = $mapped['starts_at']->format('H:i');
        $this->formProductId = (string) $booking->product_id;
        $this->refreshProductLabel();
    }

    public function closeAppointmentModal(): void
    {
        $this->resetAppointmentForm();
    }

    public function saveAppointment(): void
    {
        $validated = $this->validate();
        $product = Product::withTrashed()->findOrFail((int) $validated['formProductId']);
        $startsAtUtc = Carbon::parse($validated['formDate'] . ' ' . $validated['formTime'], 'America/New_York')->utc();

        if ($this->isEditingAppointment && $this->appointmentFormId) {
            $booking = Booking::query()->findOrFail($this->appointmentFormId);
            $booking->update([
                'contact_name' => trim($validated['formContactName']),
                'phone' => trim($validated['formPhone']),
                'email' => trim($validated['formEmail']),
                'book_date' => $startsAtUtc,
                'product_id' => $product->id,
            ]);

            AppointmentBannerDismissal::query()->where('booking_id', $booking->id)->delete();
            session()->flash('appointmentMessage', 'Appointment updated successfully.');
        } else {
            $booking = Booking::create([
                'contact_name' => trim($validated['formContactName']),
                'phone' => trim($validated['formPhone']),
                'email' => trim($validated['formEmail']),
                'book_date' => $startsAtUtc,
                'product_id' => $product->id,
            ]);

            $this->sendAppointmentEmails($booking, $product);
            session()->flash('appointmentMessage', 'Appointment scheduled successfully.');
        }

        $this->resetAppointmentForm();
        $this->resetPage();
    }

    public function deleteAppointment(int $bookingId): void
    {
        Booking::query()->findOrFail($bookingId)->delete();
        AppointmentBannerDismissal::query()->where('booking_id', $bookingId)->delete();

        if ($this->appointmentFormId === $bookingId) {
            $this->resetAppointmentForm();
        }

        session()->flash('appointmentMessage', 'Appointment canceled and removed.');
        $this->resetPage();
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

    private function refreshProductLabel(): void
    {
        $productId = trim($this->formProductId);

        if ($productId === '' || ! ctype_digit($productId)) {
            $this->formProductLabel = '';

            return;
        }

        $product = Product::withTrashed()->find((int) $productId);
        $this->formProductLabel = $product ? $product->title . ' (#' . $product->id . ')' : '';
    }

    private function resetAppointmentForm(): void
    {
        $this->showAppointmentForm = false;
        $this->isEditingAppointment = false;
        $this->appointmentFormId = null;
        $this->formContactName = '';
        $this->formPhone = '';
        $this->formEmail = '';
        $this->formDate = '';
        $this->formTime = '';
        $this->formProductId = '';
        $this->formProductLabel = '';
        $this->resetValidation();
    }

    private function sendAppointmentEmails(Booking $booking, Product $product): void
    {
        $bookDateLocal = $booking->book_date->copy()->timezone('America/New_York');

        try {
            (new GMailer([
                'template' => 'emails.booking-1',
                'to' => 'info@swissmadecorp.com',
                'subject' => 'Scheduled for ' . $bookDateLocal->format('m-d-Y, g:i A') . ' with Swiss Made Corp.',
                'contactname' => $booking->contact_name,
                'book_date' => $bookDateLocal->format('l jS \of F Y'),
                'book_time' => $bookDateLocal->format('g:i A'),
                'phone' => $booking->phone,
                'email' => $booking->email,
                'wristwatch' => $product->title,
                'product_id' => $product->id,
            ]))->send();

            if ($booking->email) {
                (new GMailer([
                    'template' => 'emails.booking',
                    'to' => $booking->email,
                    'subject' => 'Scheduled for ' . $bookDateLocal->format('m-d-Y, g:i A') . ' with Swiss Made Corp.',
                    'contactname' => $booking->contact_name,
                    'book_date' => $bookDateLocal->format('l jS \of F Y'),
                    'book_time' => $bookDateLocal->format('g:i A'),
                    'wristwatch' => $product->title,
                    'product_id' => $product->id,
                ]))->send();
            }
        } catch (\Throwable $exception) {
            report($exception);
            session()->flash('appointmentMessage', 'Appointment saved, but the confirmation email could not be sent.');
        }
    }
}
