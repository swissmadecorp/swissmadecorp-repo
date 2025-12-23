@extends ("layouts.admin-new-default")

@section ('header')
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
@endsection

@section ('content')
<!-- <div class="dashboard-graph">Monthly Sales
    <div id="myfirstchart" style="height: 300px;background: #fff"></div>
</div>
     -->
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