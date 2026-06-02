@extends ("layouts.admin-new-default")

@section ('header')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
@endsection

@section ('content')
<!-- <div class="dashboard-graph">Monthly Sales
    <div id="myfirstchart" style="height: 300px;background: #fff"></div>
</div>
     -->
@if ($appointmentBanner['count'] > 0)
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-red-700 via-red-600 to-red-500 px-5 py-4 text-white shadow-lg">
        <div>
            <p class="text-sm font-semibold">{{ $appointmentBanner['count'] }} appointments in the next 72 hours</p>
            <p class="text-xs text-red-100">{{ $appointmentBanner['range_label'] }}</p>
        </div>
        <a href="/admin/appointments?filter=approaching" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold transition hover:bg-white/20">
            View All
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
@endif

<div class="mb-6 grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
    <div class="rounded-3xl border border-stone-200 bg-[#fcfbf7] p-5 shadow-sm dark:border-gray-600 dark:bg-gray-800">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-stone-900 dark:text-white">Upcoming Visitors</h2>
                <p class="text-sm text-stone-500 dark:text-gray-400">Compact dashboard summary, without the schedule page images.</p>
            </div>
            <a href="/admin/appointments" class="text-sm font-semibold text-sky-700 dark:text-sky-300">Open Appointments</a>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-stone-200 dark:bg-gray-700 dark:ring-gray-600">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">Today</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $appointmentStats['today'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-stone-200 dark:bg-gray-700 dark:ring-gray-600">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">Approaching</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $appointmentStats['approaching'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-stone-200 dark:bg-gray-700 dark:ring-gray-600">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">This Week</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $appointmentStats['this_week'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-stone-200 dark:bg-gray-700 dark:ring-gray-600">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-400 dark:text-gray-400">Far Out</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900 dark:text-white">{{ $appointmentStats['far_out'] }}</p>
            </div>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($upcomingAppointments as $appointment)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                    <div>
                        <p class="font-semibold text-stone-900 dark:text-white">{{ $appointment['customer_name'] }}</p>
                        <p class="text-sm text-stone-600 dark:text-gray-300">{{ $appointment['product_name'] }} <span class="text-stone-400 dark:text-gray-400">#{{ $appointment['product_id'] }}</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-stone-900 dark:text-white">{{ $appointment['full_date_label'] }} at {{ $appointment['time_label'] }} {{ $appointment['time_suffix'] }}</p>
                        <p class="text-xs text-stone-500 dark:text-gray-400">{{ $appointment['relative_label'] }}</p>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-4 py-8 text-center text-sm text-stone-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    No upcoming appointments found.
                </div>
            @endforelse
        </div>
    </div>

    <div class="rounded-3xl border border-stone-200 bg-[#fcfbf7] p-5 shadow-sm dark:border-gray-600 dark:bg-gray-800">
        <h2 class="text-xl font-semibold text-stone-900 dark:text-white">Today's Agenda</h2>
        <p class="text-sm text-stone-500 dark:text-gray-400">{{ now('America/New_York')->format('D, M j') }}</p>

        <div class="mt-5 space-y-3">
            @forelse ($todayAgenda as $appointment)
                <div class="rounded-2xl border border-stone-200 bg-white px-4 py-3 dark:border-gray-600 dark:bg-gray-700">
                    <p class="text-sm font-semibold text-stone-900 dark:text-white">{{ $appointment['time_label'] }} {{ $appointment['time_suffix'] }}</p>
                    <p class="mt-1 text-sm text-stone-700 dark:text-gray-200">{{ $appointment['customer_name'] }}</p>
                    <p class="text-xs text-stone-500 dark:text-gray-400">{{ $appointment['product_name'] }}</p>
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400 dark:text-gray-500">ID: #{{ $appointment['product_id'] }}</p>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-4 py-8 text-center text-sm text-stone-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    No appointments scheduled today.
                </div>
            @endforelse
        </div>
    </div>
</div>

<h1 class="dark:bg-black dark:text-gray-200">Past Due Invoices</h1>
<div class="overflow-x-auto">
    <table id="invoices" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400" cellspacing="0">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Id</th>
                <th scope="col" class="px-6 py-3">Invoice</th>
                <th scope="col" class="px-6 py-3">Company</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3" style="width: 80px">Past Due</th>
                <th scope="col" class="px-6 py-3">Amount</th>
            </tr>
        </thead>
        <tbody>
        
        @foreach ($invoices as $order)
            <?php
                if ($order->status==0)
                    $status='Unpaid';
                elseif ($order->status==1)
                    $status='Paid*';
                elseif ($order->status==2) 
                    $status = "Return";
                else $status='Transferred';
            ?>
            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200">
                <td class="px-6 py-4"><a href="admin/orders/{{ $order->id }}">{{ $order->id }}</a></td>
                <td class="px-6 py-4">@if ($order->method=='On Memo')
                        On Memo
                    @else
                        Invoiced
                    @endif

                    @if ($order->emailed)
                        <i class="far fa-envelope" title="Invoice was emailed"></i>
                    @endif
                </td>
                <td class="px-6 py-4">{{ $order->b_company }}</td>
                <td class="px-6 py-4">{{ $status }}</td>
                <td style="width: 100px">
                    <?php 
                        $to = date('Y-m-d',time());
                        
                        $dStart = new \DateTime($to);
                        $dEnd  = new \DateTime($order->created_at);
                        $dDiff = $dStart->diff($dEnd);
                        
                    ?>

                    @if ($dDiff->days>365)
                        {{$dDiff->y }} years
                    @elseif ($dDiff->days > 31)
                        {{ $dDiff->m }} months
                    @else
                        {{ $dDiff->days }} days
                    @endif
                </td>
                <?php $subtotal = $order->total - $order->payments->sum('amount') ?>
                
                @if($order->orderReturns)
                    @foreach($order->orderReturns as $returns)
                        <?php $subtotal -= $returns->pivot->amount*$returns->pivot->qty; ?>
                    @endforeach
                @endif
                
                <td class="text-right px-6 py-4">${{ number_format($subtotal,2) }}</td>
            </tr>
        @endforeach


        </tbody>
    </table>
</div>
@endsection

@section ('footer')
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
@endsection

<?php 
    $m = array();$c = array();
    
    foreach ($orders as $order) {
        $date = date('m-d-Y',strtotime($order->date));
        
        $m[]=array('year'=>$date,'value'=>$order->total);
    }
    
?>

@section ('jquery')
<script>
    $(document).ready( function() {
    //    new Morris.Bar({
    //         // ID of the element in which to draw the chart.
    //         element: 'myfirstchart',
    //         // Chart data records -- each entry in this array corresponds to a point on
    //         // the chart.
    //         data: <?php echo json_encode($m)?>,
    //         // The name of the data record attribute that contains x-values.
    //         xkey: 'year',
    //         // A list of names of data record attributes that contain y-values.
    //         ykeys: ['value'],
    //         // Labels for the ykeys -- will be displayed when you hover over the
    //         // chart.
    //         barColors: ['#457384'],
    //         hideHover: 'auto',
    //         labels: ['Sales']
    //     });
        
    })    
</script>
@endsection        
