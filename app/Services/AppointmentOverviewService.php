<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\AppointmentBannerDismissal;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class AppointmentOverviewService
{
    private const DISPLAY_TIMEZONE = 'America/New_York';
    private const UTC_STORAGE_FIXED_AT = '2026-06-02 00:00:00';

    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage() ?: 1;
        $appointments = $this->filteredAppointments($filters)->values();
        $items = $appointments->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $appointments->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function urgentBanner(int $hours = 72, int $limit = 8, ?int $userId = null): array
    {
        $nowLocal = $this->localNow();
        $endLocal = $nowLocal->copy()->addHours($hours);
        $items = $this->filteredAppointments(['filter' => 'approaching']);

        if ($userId) {
            $items = $this->filterDismissedBannerItems($items, $userId);
        }

        $items = $items->take($limit)->values();

        return [
            'count' => $userId
                ? $this->filterDismissedBannerItems($this->filteredAppointments(['filter' => 'approaching']), $userId)->count()
                : $this->filteredAppointments(['filter' => 'approaching'])->count(),
            'range_label' => $nowLocal->format('M j') . ' - ' . $endLocal->format('M j'),
            'items' => $items,
        ];
    }

    public function stats(): array
    {
        return [
            'today' => $this->countForFilter('today'),
            'approaching' => $this->countForFilter('approaching'),
            'this_week' => $this->countForFilter('week'),
            'far_out' => $this->countForFilter('later'),
            'this_month' => $this->countForFilter('month'),
            'all' => $this->appointmentsCollection()->count(),
            'upcoming' => $this->countForFilter('upcoming'),
        ];
    }

    public function todayAgenda(int $limit = 8): Collection
    {
        return $this->filteredAppointments(['filter' => 'today'])
            ->take($limit)
            ->values();
    }

    public function upcoming(int $limit = 8): Collection
    {
        return $this->filteredAppointments(['filter' => 'upcoming'])
            ->take($limit)
            ->values();
    }

    public function groupedUpcoming(int $limitDays = 14): Collection
    {
        $startLocal = $this->localNow()->copy()->startOfDay();
        $endLocal = $this->localNow()->copy()->addDays($limitDays)->endOfDay();

        return $this->appointmentsCollection()
            ->filter(fn (array $appointment) => $appointment['starts_at']->betweenIncluded($startLocal, $endLocal))
            ->groupBy(fn (array $appointment) => $appointment['starts_at']->toDateString())
            ->map(function (Collection $items) {
                return [
                    'label' => $items->first()['starts_at']->format('D, M j'),
                    'items' => $items->values(),
                ];
            })
            ->values();
    }

    public function map(Booking $booking, ?Carbon $nowUtc = null): array
    {
        $nowUtc ??= now('UTC');
        $startsAtLocal = $this->bookingStartsAtLocal($booking);
        $product = $booking->product;
        $productTitle = $product?->title ?: 'Product unavailable';
        $image = '/images/no-image.jpg';

        if ($product && $product->images->first() && ! str_contains($product->images->first()->location, 'snapshot')) {
            $image = '/images/thumbs/' . $product->images->first()->location;
        }

        $status = $this->statusFor($startsAtLocal);
        $priority = $this->priorityFor($startsAtLocal);

        return [
            'id' => $booking->id,
            'customer_name' => $booking->contact_name ?: 'Customer',
            'phone' => $this->formatPhone($booking->phone),
            'email' => $booking->email ?: 'No email',
            'product_id' => $booking->product_id,
            'product_name' => $productTitle,
            'image' => $image,
            'starts_at' => $startsAtLocal,
            'date_label' => $startsAtLocal->format('M j'),
            'full_date_label' => $startsAtLocal->format('D, M j'),
            'time_label' => $startsAtLocal->format('g:i'),
            'time_suffix' => $startsAtLocal->format('A'),
            'relative_label' => $this->relativeLabel($startsAtLocal, $nowUtc->copy()->timezone(self::DISPLAY_TIMEZONE)),
            'status' => $status['label'],
            'status_classes' => $status['classes'],
            'status_border_class' => $status['border'],
            'priority' => $priority['label'],
            'priority_classes' => $priority['classes'],
            'record_touched_at' => ($booking->updated_at ?: $booking->created_at)?->copy(),
        ];
    }

    private function countForFilter(string $filter): int
    {
        return $this->filteredAppointments(['filter' => $filter])->count();
    }

    private function appointmentsCollection(): Collection
    {
        return Booking::query()
            ->with(['product.images'])
            ->whereNotNull('book_date')
            ->orderBy('book_date','desc')
            ->get()
            ->map(fn (Booking $booking) => $this->map($booking));
    }

    private function filteredAppointments(array $filters = []): Collection
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $filter = $filters['filter'] ?? 'upcoming';
        $nowLocal = $this->localNow();

        return $this->appointmentsCollection()
            ->filter(function (array $appointment) use ($search, $dateFrom, $dateTo, $filter, $nowLocal) {
                if (! $this->matchesSearch($appointment, $search)) {
                    return false;
                }

                if (! $this->matchesDateRange($appointment, $dateFrom, $dateTo)) {
                    return false;
                }

                return $this->matchesNamedFilter($appointment, $filter, $nowLocal);
            })
            ->sortByDesc(fn (array $appointment) => $appointment['starts_at']->timestamp)
            ->values();
    }

    private function matchesSearch(array $appointment, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $haystack = implode(' ', [
            $appointment['customer_name'],
            $appointment['email'],
            $appointment['phone'],
            (string) $appointment['product_id'],
            $appointment['product_name'],
        ]);

        return str_contains(mb_strtolower($haystack), mb_strtolower($search));
    }

    private function matchesDateRange(array $appointment, ?string $dateFrom, ?string $dateTo): bool
    {
        $startsAt = $appointment['starts_at'];

        if ($dateFrom) {
            $from = Carbon::parse($dateFrom, self::DISPLAY_TIMEZONE)->startOfDay();
            if ($startsAt->lt($from)) {
                return false;
            }
        }

        if ($dateTo) {
            $to = Carbon::parse($dateTo, self::DISPLAY_TIMEZONE)->endOfDay();
            if ($startsAt->gt($to)) {
                return false;
            }
        }

        return true;
    }

    private function matchesNamedFilter(array $appointment, string $filter, Carbon $nowLocal): bool
    {
        $startsAt = $appointment['starts_at'];

        switch ($filter) {
            case 'approaching':
                return $startsAt->betweenIncluded($nowLocal, $nowLocal->copy()->addHours(72));
            case 'today':
                return $startsAt->isSameDay($nowLocal);
            case 'week':
                return $startsAt->betweenIncluded($nowLocal->copy()->startOfWeek(), $nowLocal->copy()->endOfWeek());
            case 'month':
                return $startsAt->betweenIncluded($nowLocal->copy()->startOfMonth(), $nowLocal->copy()->endOfMonth());
            case 'later':
                return $startsAt->gt($nowLocal->copy()->addDays(30));
            case 'all':
                return true;
            case 'upcoming':
            default:
                return $startsAt->gte($nowLocal);
        }
    }

    private function localNow(): Carbon
    {
        return now(self::DISPLAY_TIMEZONE);
    }

    private function filterDismissedBannerItems(Collection $appointments, int $userId): Collection
    {
        $dismissals = AppointmentBannerDismissal::query()
            ->where('user_id', $userId)
            ->get()
            ->keyBy('booking_id');

        return $appointments->reject(function (array $appointment) use ($dismissals) {
            $dismissal = $dismissals->get($appointment['id']);

            if (! $dismissal) {
                return false;
            }

            $recordTouchedAt = $appointment['record_touched_at'];

            if (! $recordTouchedAt) {
                return true;
            }

            return optional($dismissal->booking_updated_at)?->equalTo($recordTouchedAt) ?? false;
        })->values();
    }

    private function relativeLabel(Carbon $startsAtLocal, Carbon $nowLocal): string
    {
        if ($startsAtLocal->lessThan($nowLocal)) {
            return 'Past Due';
        }

        $minutes = $nowLocal->diffInMinutes($startsAtLocal);

        if ($minutes < 60) {
            return 'In ' . $minutes . 'm';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours < 24) {
            return $remainingMinutes > 0
                ? 'In ' . $hours . 'h ' . $remainingMinutes . 'm'
                : 'In ' . $hours . 'h';
        }

        $days = intdiv($hours, 24);
        $remainingHours = $hours % 24;

        return $remainingHours > 0
            ? 'In ' . $days . 'd ' . $remainingHours . 'h'
            : 'In ' . $days . 'd';
    }

    private function statusFor(Carbon $startsAtLocal): array
    {
        $nowLocal = $this->localNow();

        if ($startsAtLocal->lessThan($nowLocal)) {
            return [
                'label' => 'Past Due',
                'classes' => 'bg-stone-100 text-stone-700 ring-stone-200',
                'border' => 'border-stone-400',
            ];
        }

        if ($startsAtLocal->isSameDay($nowLocal)) {
            return [
                'label' => 'Today',
                'classes' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'border' => 'border-amber-500',
            ];
        }

        if ($startsAtLocal->lessThanOrEqualTo($nowLocal->copy()->addHours(72))) {
            return [
                'label' => 'Approaching',
                'classes' => 'bg-red-50 text-red-700 ring-red-200',
                'border' => 'border-red-500',
            ];
        }

        if ($startsAtLocal->lessThanOrEqualTo($nowLocal->copy()->endOfWeek())) {
            return [
                'label' => 'This Week',
                'classes' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'border' => 'border-sky-500',
            ];
        }

        return [
            'label' => 'Upcoming',
            'classes' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'border' => 'border-emerald-500',
        ];
    }

    private function priorityFor(Carbon $startsAtLocal): array
    {
        $nowLocal = $this->localNow();

        if ($startsAtLocal->lessThan($nowLocal)) {
            return [
                'label' => null,
                'classes' => '',
            ];
        }

        if ($startsAtLocal->lessThanOrEqualTo($nowLocal->copy()->addHours(24))) {
            return [
                'label' => 'High Priority',
                'classes' => 'bg-red-50 text-red-700',
            ];
        }

        if ($startsAtLocal->lessThanOrEqualTo($nowLocal->copy()->addDays(7))) {
            return [
                'label' => 'Medium',
                'classes' => 'bg-amber-50 text-amber-700',
            ];
        }

        return [
            'label' => 'Low',
            'classes' => 'bg-emerald-50 text-emerald-700',
        ];
    }

    private function formatPhone(?string $phone): string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return 'No phone';
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return sprintf('(%s) %s-%s', substr($digits, 1, 3), substr($digits, 4, 3), substr($digits, 7, 4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6, 4));
        }

        return $phone;
    }

    private function bookingStartsAtLocal(Booking $booking): Carbon
    {
        if ($this->usesUtcStorage($booking)) {
            return $booking->book_date->copy()->timezone(self::DISPLAY_TIMEZONE);
        }

        // Legacy rows were often saved as local wall-clock times but tagged as UTC.
        // Rebuild from the stored components first, then nudge obviously shifted
        // early-morning times back into the expected business-hour range.
        return $this->normalizeLegacyDisplayTime(Carbon::create(
            $booking->book_date->year,
            $booking->book_date->month,
            $booking->book_date->day,
            $booking->book_date->hour,
            $booking->book_date->minute,
            $booking->book_date->second,
            self::DISPLAY_TIMEZONE
        ));
    }

    private function usesUtcStorage(Booking $booking): bool
    {
        $touchedAt = $booking->updated_at ?: $booking->created_at;

        if (! $touchedAt) {
            return false;
        }

        return $touchedAt
            ->copy()
            ->timezone(self::DISPLAY_TIMEZONE)
            ->greaterThanOrEqualTo(Carbon::parse(self::UTC_STORAGE_FIXED_AT, self::DISPLAY_TIMEZONE));
    }

    private function normalizeLegacyDisplayTime(Carbon $startsAtLocal): Carbon
    {
        // Older appointment rows appear to have been saved several hours early.
        // If a saved wall-clock time lands before the business-day range,
        // shift it forward by the New York UTC offset for that date.
        if ($startsAtLocal->hour < 10) {
            $offsetMinutes = abs($startsAtLocal->offsetMinutes);
            return $startsAtLocal->copy()->addMinutes($offsetMinutes);
        }

        return $startsAtLocal;
    }
}
