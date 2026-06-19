<div class="container-fluid toolbar-container">
    <div class="row" style="margin-right: -3px">
        <div class="announcement col-12 text-center" style="line-height: 45px;">
            @php
                $daysLeft = null;

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
            @endphp
            @if ($discount->action == 5 || $discount->action == 1)
            <h1>{{ $discount->title}}</h1>
            <h4>{!! $discount->description !!}</h4>
            @else
            @php $saleLabelDays = $daysLeft ?? 1; @endphp
            <h1 class="p-1 pt-2 font-bold text-xl text-green-500 bg-amber-50">
                {{ $saleLabelDays }} {{ $saleLabelDays === 1 ? 'DAY' : 'DAYS' }} LEFT
            </h1>
            <h4 class="p-1 text-xl">{!! $discount->title !!}</h4>
            <h5 class="p-1 text-xl">Sale ends on {{ $discount->end_date->format('m-d-Y')}}</h5>
            @endif
        </div>
    </div>
</div>
