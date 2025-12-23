<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;
use Livewire\WithPagination;
use App\SearchCriteriaTrait;

class InvoicePayments extends Component
{
    use WithPagination, SearchCriteriaTrait;

    public $page = 1;
    public $search = '';

    protected $queryString = [
        'page',
    ];

    public function updatingSearch()
    { 
        $this->resetPage();
    }

    public function getPayment($id) {
        $countries = new \App\Libs\Countries;
        $customer = Customer::find($id);
        ob_start();
        ?>
        <div class="bg-gray-100 mb-2 p-2 pb-2 rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-300" style="clear: both">
        <?php 
            $address2 = '';
            
            $state_b = $countries->getStateCodeFromCountry($customer->state);
            $country = $countries->getCountry($customer->country);

            echo $customer->company.'<br>';
            echo !empty($customer->address1) ? $customer->address1 .'<br>' : '';
            echo !empty($customer->address2) ? $customer->address2 .'<br>' : '';
            echo !empty($customer->city) ? $customer->city .', '. $state_b . ' ' . $customer->zip.'<br>': '';
            
            echo !empty($customer->phone) ? $customer->phone . '<br>' : '';
            echo !empty($customer->po) ? 'PO #: '.$customer->po . '<br>' : '';
                //die($customer->company);
        ?>
        </div>
        <table id="payments" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-500" cellspacing="0" width="100%">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-500">
                <tr>
                    <th scope="col" class="px-3 py-3">Id</th>
                    <th scope="col" class="text-right px-3 py-3">Amount</th>
                    <th scope="col" class="text-right px-3 py-3">Reference</th>
                    <th scope="col" class="text-right px-3 py-3">Payment</th>
                    <th scope="col" class="text-right px-3 py-3">Date</th>

                </tr>
            </thead>
            <tbody class="dark:text-gray-400">
                <?php 
                    $orders = $customer->orders()->sortit()->get(); 
                    $order_id = 1; 
                ?>
                <?php foreach ($orders as $order) { ?>
                    <?php 
                        $calc = $order->total;
                        $totalLeft = $order->payments->sum('amount'); 
                        $id = $order->id;
                        foreach ($order->payments as $payment) { ?>
                            <tr class="<?= $order->payments->count() > 1 ? 'bg-gray-50' : '' ?> border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-3 py-3">
                                    <a href="#" @click="$dispatch('load-invoice', { id: <?= $id ?> })" data-id="<?= $id ?>" class="editinvoice cursor-pointer dark:hover:text-white text-sky-600"><?= $id ?></a>
                                </td>
                                <td class="text-right px-3 py-3">$<?= number_format($calc,2) ?></td>
                                <td class="text-right px-3 py-3"><?= $payment->ref ?></td>
                                <td class="text-right px-3 py-3">$<?= number_format($payment->amount,2) ?></td>
                                <td class="text-right px-3 py-3"><?= $payment->created_at->format('m/d/Y') ?></td>
                            </tr>
                            <?php $calc -= $payment->amount; ?>
                        <?php } ?>

                        <?php if ($totalLeft) { ?>
                            <?php 
                                if ($order->total-$totalLeft == 0)
                                    $totalLeft = 0; 
                                else $totalLeft = $order->total - $totalLeft;
                            ?>
                            <tr>
                                <td class="px-3 py-3"><span class="font-bold">Total Owed</span></td>
                                <td class="text-right w-32 px-3 py-3" colspan="4"><span class="font-bold">$<?= number_format($totalLeft,2) ?></span></td>
                            </tr>
                        <?php } ?>
                        
                <?php } ?>
            </tbody>
        </table>
        <?php

        $content=ob_get_clean();
        $this->dispatch('viewPayment',$content);
        return $content;
    }

    public function render(){
        
        $columns = ['company'];
        $searchTerm = $this->generateSearchQuery($this->search, $columns);

        // $orders = Customer::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
        //     $query->whereRaw($searchTerm);
        // })
        // ->select(\DB::Raw('customer_id, company, amount, max_date'))
        // ->join(\DB::Raw('(select customer_id, sum(amount) amount, max(order_payment.created_at) max_date
        //         FROM customer_order JOIN order_payment ON customer_order.order_id = order_payment.order_id
        //         GROUP BY customer_id) name_date'),'customers.id','=','name_date.customer_id')
        // ->groupBy('customer_id','company')
        // ->orderByRaw('max_date desc')
        // ->paginate(perPage: 10);
        
        $orders = Customer::when(strlen($searchTerm)>0, function($query) use ($searchTerm) {
            $query->whereRaw($searchTerm);
        })
        ->joinSub(
            \DB::table('customer_order')
                ->join('order_payment', 'customer_order.order_id', '=', 'order_payment.order_id')
                ->select(
                    'customer_order.customer_id',
                    \DB::raw('SUM(order_payment.amount) as amount'),
                    \DB::raw('MAX(order_payment.created_at) as max_date')
                )
                ->groupBy('customer_order.customer_id'),
            'name_date',
            'customers.id',
            '=',
            'name_date.customer_id'
        )
        ->join('customer_order', 'customers.id', '=', 'customer_order.customer_id')
        ->join('order_product', 'customer_order.order_id', '=', 'order_product.order_id')
        ->join('orders', 'customer_order.order_id', '=', 'orders.id')
        // ->where('orders.status', '=', 1)
        // ->where('method','Invoice')
        ->select(
            'name_date.customer_id',
            'customers.company',
            'name_date.amount',
            'name_date.max_date',
            \DB::raw('SUM(order_product.cost * order_product.qty) as total_cost')
        )
        ->groupBy('name_date.customer_id', 'customers.company')
        ->orderBy('name_date.max_date', 'desc')
        ->paginate(perPage: 10);

        return view('livewire.invoice-payments',['orders' => $orders,'pageName' => "Payments"]);
    }
}
