<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AppointmentOverviewService
{
    private const DISPLAY_TIMEZONE = 'America/New_York';

    public function query(array $filters = []): Builder
    {
        $query = Booking::query()
            ->with(['product.images'])
            ->whereNotNull('book_date')
            ->orderBy('book_date');

        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDateRange($query, $filters['date_from'] ?? null, $filters['date_to'] ?? null);
        $this->applyNamedFilter($query, $filters['filter'] ?? 'upcoming');

        return $query;
    }

    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate($perPage)
            ->through(fn (Booking $booking) => $this->map($booking));
    }

    public function urgentBanner(int $hours = 72, int $limit = 8): array
    {
        $nowUtc = now('UTC');
        $endUtc = $nowUtc->copy()->addHours($hours);

        $items = Booking::query()
            ->with(['product.images'])
            ->whereBetween('book_date', [$nowUtc, $endUtc])
            ->orderBy('book_date')
            ->limit($limit)
            ->get()
            ->map(fn (Booking $booking) => $this->map($booking, $nowUtc));

        $localStart = $nowUtc->copy()->timezone(self::DISPLAY_TIMEZONE);
        $localEnd = $endUtc->copy()->timezone(self::DISPLAY_TIMEZONE);

        return [
            'count' => Booking::query()->whereBetween('book_date', [$nowUtc, $endUtc])->count(),
            'range_label' => $localStart->format('M j') . ' - ' . $localEnd->format('M j'),
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
            'all' => Booking::query()->whereNotNull('book_date')->count(),
            'upcoming' => $this->countForFilter('upcoming'),
        ];
    }

    public function todayAgenda(int $limit = 8): Collection
    {
        $query = Booking::query()->with(['product.images'])->orderBy('book_date');
        $this->applyNamedFilter($query, 'today');

        return $query->limit($limit)->get()->map(fn (Booking $booking) => $this->map($booking));
    }

    public function upcoming(int $limit = 8): Collection
    {
        $query = Booking::query()->with(['product.images'])->orderBy('book_date');
        $this->applyNamedFilter($query, 'upcoming');

        return $query->limit($limit)->get()->map(fn (Booking $booking) => $this->map($booking));
    }

    public function groupedUpcoming(int $limitDays = 14): Collection
    {
        $startUtc = $this->localNow()->copy()->startOfDay()->utc();
        $endUtc = $this->localNow()->copy()->addDays($limitDays)->endOfDay()->utc();

        return Booking::query()
            ->with(['product.images'])
            ->whereBetween('book_date', [$startUtc, $endUtc])
            ->orderBy('book_date')
            ->get()
            ->map(fn (Booking $booking) => $this->map($booking))
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
        ];
    }

    private function countForFilter(string $filter): int
    {
        $query = Booking::query()->whereNotNull('book_date');
        $this->applyNamedFilter($query, $filter);

        return $query->count();
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($search) {
            $builder->where('contact_name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%')
                ->orWhere('product_id', 'like', '%' . $search . '%')
                ->orWhereHas('product', function (Builder $productQuery) use ($search) {
                    $productQuery->where('title', 'like', '%' . $search . '%');
                });
        });
    }

    private function applyDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom) {
            $query->where('book_date', '>=', Carbon::parse($dateFrom, self::DISPLAY_TIMEZONE)->startOfDay()->utc());
        }

        if ($dateTo) {
            $query->where('book_date', '<=', Carbon::parse($dateTo, self::DISPLAY_TIMEZONE)->endOfDay()->utc());
        }
    }

    private function applyNamedFilter(Builder $query, string $filter): void
    {
        $nowLocal = $this->localNow();
        $todayStartUtc = $nowLocal->copy()->startOfDay()->utc();
        $todayEndUtc = $nowLocal->copy()->endOfDay()->utc();
        $weekStartUtc = $nowLocal->copy()->startOfWeek()->utc();
        $weekEndUtc = $nowLocal->copy()->endOfWeek()->utc();
        $monthStartUtc = $nowLocal->copy()->startOfMonth()->utc();
        $monthEndUtc = $nowLocal->copy()->endOfMonth()->utc();
        $nowUtc = $nowLocal->copy()->utc();

        switch ($filter) {
            case 'approaching':
                $query->whereBetween('book_date', [$nowUtc, $nowUtc->copy()->addHours(72)]);
                break;
            case 'today':
                $query->whereBetween('book_date', [$todayStartUtc, $todayEndUtc]);
                break;
            case 'week':
                $query->whereBetween('book_date', [$weekStartUtc, $weekEndUtc]);
                break;
            case 'month':
                $query->whereBetween('book_date', [$monthStartUtc, $monthEndUtc]);
                break;
            case 'later':
                $query->where('book_date', '>', $nowUtc->copy()->addDays(30));
                break;
            case 'all':
                break;
            case 'upcoming':
            default:
                $query->where('book_date', '>=', $todayStartUtc);
                break;
        }
    }

    private function localNow(): Carbon
    {
        return now(self::DISPLAY_TIMEZONE);
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
                'label' => 'Past Due',
                'classes' => 'bg-stone-100 text-stone-700',
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
