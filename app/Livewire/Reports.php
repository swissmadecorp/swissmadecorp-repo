<?php

namespace App\Livewire;

use App\Models\Order;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Reports extends Component
{
    use WithPagination;

    #[Url(keep: true)]
    public string $view = 'paid';

    #[Url(keep: true)]
    public string $search = '';

    #[Url(as: 'from', keep: true)]
    public string $fromDate = '';

    #[Url(as: 'to', keep: true)]
    public string $toDate = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFromDate(): void { $this->resetPage(); }
    public function updatingToDate(): void { $this->resetPage(); }
    public function updatingView(): void { $this->resetPage(); }

    public function updatedView(): void
    {
        if (! in_array($this->view, ['paid', 'unpaid', 'memo', 'all'], true)) {
            $this->view = 'paid';
        }
    }

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['paid', 'unpaid', 'memo', 'all'], true) ? $view : 'paid';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'fromDate', 'toDate');
        $this->resetPage();
    }

    private function ordersQuery()
    {
        $query = Order::query()
            ->orderByDesc('orders.created_at');

        if ($this->view === 'paid') {
            $query->where('status', 1)->where('method', '<>', 'On Memo');
        } elseif ($this->view === 'unpaid') {
            $query->where('status', 0)->where('method', '<>', 'On Memo');
        } elseif ($this->view === 'memo') {
            $query->where('method', 'On Memo');
        }

        if ($this->fromDate !== '') {
            $query->where('orders.created_at', '>=', Carbon::parse($this->fromDate)->startOfDay());
        }
        if ($this->toDate !== '') {
            $query->where('orders.created_at', '<=', Carbon::parse($this->toDate)->endOfDay());
        }

        $search = trim($this->search);
        if ($search !== '') {
            // Keep the wildcard behavior aligned with the Paid Invoices page:
            // customer groups 1 through 4 represent the supported sales channels.
            if ($search === '*') {
                $query->whereHas('customers', function ($customerQuery) {
                    $customerQuery->whereIn('customers.cgroup', [1, 2, 3, 4]);
                });

                return $query;
            }

            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($query) use ($like, $search) {
                $query->where('s_company', 'like', $like)
                    ->orWhere('s_firstname', 'like', $like)
                    ->orWhere('s_lastname', 'like', $like)
                    ->orWhere('orders.id', $search)
                    ->orWhereHas('customers', function ($customerQuery) use ($like) {
                        $customerQuery->where('company', 'like', $like)
                            ->orWhere('firstname', 'like', $like)
                            ->orWhere('lastname', 'like', $like);
                    });
            });
        }

        return $query;
    }

    public function render()
    {
        $query = $this->ordersQuery();
        // Only hydrate customers and products for the ten visible orders.
        $orders = (clone $query)->with(['customers', 'products'])->paginate(10);
        $pagePayments = DB::table('order_payment')
            ->whereIn('order_id', $orders->getCollection()->pluck('id'))
            ->selectRaw('order_id, SUM(amount) as payments_received')
            ->groupBy('order_id')
            ->pluck('payments_received', 'order_id');
        $reportAmounts = $orders->getCollection()->mapWithKeys(function (Order $order) use ($pagePayments) {
            $payments = (float) ($pagePayments[$order->id] ?? 0);
            $invoiceAmount = (float) ($order->total ?: $order->subtotal);
            $amount = in_array($this->view, ['unpaid', 'memo'], true)
                ? max(0, $invoiceAmount - $payments)
                : ($this->view === 'all'
                    ? $invoiceAmount
                    : (((int) $order->status === 1 && $order->method !== 'On Memo')
                        ? $invoiceAmount
                        : $payments));

            return [$order->id => $amount];
        });
        $reportProfits = $orders->getCollection()->mapWithKeys(function (Order $order) {
            $profit = $order->products->sum(function ($product) {
                $cost = (float) ($product->pivot->cost ?: $product->p_price);
                return ((float) $product->pivot->price - $cost) * (int) ($product->pivot->qty ?: 1);
            });

            return [$order->id => $profit];
        });
        // Summary queries need scalar results, without sorting or model hydration.
        $totalQuery = (clone $query)->toBase()->reorder();
        if (in_array($this->view, ['unpaid', 'memo'], true)) {
            $paymentTotals = DB::table('order_payment')
                ->selectRaw('order_id, SUM(amount) as payments_received')
                ->groupBy('order_id');
            $totalQuery->leftJoinSub($paymentTotals, 'payment_totals', 'payment_totals.order_id', '=', 'orders.id');
        }
        $totalReceived = in_array($this->view, ['unpaid', 'memo'], true)
            ? (float) $totalQuery->selectRaw('SUM(GREATEST(COALESCE(NULLIF(orders.total, 0), orders.subtotal) - COALESCE(payment_totals.payments_received, 0), 0)) as report_total')->value('report_total')
            : (float) $totalQuery->selectRaw('SUM(COALESCE(NULLIF(orders.total, 0), orders.subtotal)) as report_total')->value('report_total');
        $totalProfit = (float) (clone $query)
            ->toBase()->reorder()
            ->join('order_product', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_product.product_id')
            ->selectRaw('SUM((order_product.price - COALESCE(NULLIF(order_product.cost, 0), products.p_price, 0)) * COALESCE(order_product.qty, 1)) as report_profit')
            ->value('report_profit');

        return view('livewire.reports', compact('orders', 'reportAmounts', 'reportProfits', 'totalReceived', 'totalProfit'))
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Reports'])
            ->title('Reports');
    }
}
