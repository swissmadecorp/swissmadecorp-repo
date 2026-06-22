<div class="container-fluid toolbar-container">
    <div class="row" style="margin-right: -3px">
        <div class="announcement col-12 text-center" style="line-height: 45px;">
            @php
                $daysLeft = null;
                $discountValue = null;

                if ($discount->end_date) {
                    $todayDate = \Illuminate\Support\Carbon::parse(
                        now('America/New_York')->toDateString(),
                        'America/New_York'
                    );
                    $endDate = \Illuminate\Support\Carbon::parse(
                        $discount->end_date->toDateString(),
                        'America/New_York'
                    );

                    $daysLeft = max(((int) $todayDate->diffInDays($endDate, false)) + 1, 0);
                }

                if (in_array((int) $discount->action, [1, 4, 5], true)) {
                    $discountValue = rtrim(rtrim(number_format((float) $discount->amount, 2), '0'), '.') . '% OFF';
                } elseif ((float) $discount->amount > 0) {
                    $discountValue = '$' . number_format((float) $discount->amount, 2) . ' OFF';
                }
            @endphp
            @php $saleLabelDays = $daysLeft ?? 1; @endphp
            <h1 class="p-1 pt-2 font-bold text-xl text-green-500 bg-amber-50">
                @if ($discountValue)
                    {{ $discountValue }} •
                @endif
                {{ $saleLabelDays }} {{ $saleLabelDays === 1 ? 'DAY' : 'DAYS' }} LEFT
            </h1>
            <h4 class="p-1 text-xl">{!! $discount->title !!}</h4>
            @if (!empty($discount->description))
            <h5 class="p-1 text-lg">{!! $discount->description !!}</h5>
            @endif
            <h5 class="p-1 text-xl">Sale ends on {{ $discount->end_date->format('m-d-Y')}}</h5>
        </div>
    </div>
</div>
