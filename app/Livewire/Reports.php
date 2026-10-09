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
        $paymentTotals = DB::table('order_payment')
            ->select('order_id', DB::raw('SUM(amount) as payments_received'))
            ->groupBy('order_id');

        $query = Order::query()
            ->select('orders.*', DB::raw('COALESCE(payment_totals.payments_received, 0) as payments_received'))
            ->leftJoinSub($paymentTotals, 'payment_totals', 'payment_totals.order_id', '=', 'orders.id')
            ->with('customers')
            ->orderByDesc('created_at');

        if ($this->view === 'paid') {
            $query->where('status', 1)->where('method', '<>', 'On Memo');
        } elseif ($this->view === 'unpaid') {
            $query->where('status', 0)->where('method', '<>', 'On Memo');
        } elseif ($this->view === 'memo') {
            $query->where('method', 'On Memo');
        }

        if ($this->fromDate !== '') {
            $query->where('created_at', '>=', Carbon::parse($this->fromDate)->startOfDay());
        }
        if ($this->toDate !== '') {
            $query->where('created_at', '<=', Carbon::parse($this->toDate)->endOfDay());
        }

        $search = trim($this->search);
        if ($search !== '') {
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
        $orders = $query->paginate(10);
        $reportAmounts = $orders->getCollection()->mapWithKeys(function (Order $order) {
            $payments = (float) $order->payments_received;
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
        $totalQuery = clone $query;
        $totalReceived = in_array($this->view, ['unpaid', 'memo'], true)
            ? (float) $totalQuery->selectRaw('SUM(GREATEST(COALESCE(NULLIF(orders.total, 0), orders.subtotal) - COALESCE(payment_totals.payments_received, 0), 0)) as report_total')->value('report_total')
            : (float) $totalQuery->selectRaw('SUM(COALESCE(NULLIF(orders.total, 0), orders.subtotal)) as report_total')->value('report_total');

        return view('livewire.reports', compact('orders', 'reportAmounts', 'totalReceived'))
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Reports'])
            ->title('Reports');
    }
}
