<div class="container-fluid toolbar-container">    
    <div class="row" style="margin-right: -3px">
        <div class="announcement col-12 text-center" style="line-height: 45px;">
            <?php $date1 = new DateTime("now"); $date2 = new DateTime($discount->end_date);?>
            @if ($discount->action == 5 || $discount->action == 1)
            <h1>{{ $discount->title}}</h1>
            <h4>{!! $discount->description !!}</h4>
            @else
            <h1>{{$date1->diff($date2)->format('%a')+1}} DAY SALE</h1>
            <h4>Up to 53% Off retail price on <em>all</em> selected watches. Click <a href="/search?p=sale">here</a> to view.</h4>
            <h5 class="announcement-days-left">Sale ends on {{ $discount->end_date->format('m-d-Y')}}</h5>
            @endif
        </div>
    </div>
</div>