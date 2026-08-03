<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Models\Payment;
use App\Models\CustomerCredit;
use Illuminate\Support\Facades\DB;

class Payments extends Component
{
    public $order = null;
    
    #[Validate('required', message: 'Payment Amount is required')]
    public $paymentAmount;
    #[Validate('required', message: 'Payment Reference is required')]
    public $paymentRef;
    public $orderId;

    public function render()
    {
        return view('livewire.payments');
    }

    public function hydrate() {
        $this->resetValidation();
    }

    #[On('current-order')]
    public function getOrder($id = null) {
        $order = Order::find($id);
        $this->order = $order;
        //$this->dispatch('current-order',$id);
    }

    public function savePayment() {
        $this->paymentAmount = preg_replace('/[^0-9.\-]/', '', (string) $this->paymentAmount);
        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:0.01'],
            'paymentRef' => ['required'],
        ]);

        $result = DB::transaction(function () {
            $order = Order::lockForUpdate()->findOrFail($this->order->id);

            $amountReceived = round((float) $this->paymentAmount, 2);
            $amountPaid = round((float) Payment::where('order_id', $order->id)->sum('amount'), 2);
            $amountOwed = max(0, round((float) $order->total - $amountPaid, 2));
            $applyAmount = min($amountReceived, $amountOwed);
            $creditAmount = round($amountReceived - $applyAmount, 2);

            if ($applyAmount <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'paymentAmount' => 'This invoice has already been paid in full.',
                ]);
            }

            Payment::create([
                'amount' => $applyAmount,
                'ref' => $this->paymentRef,
                'order_id' => $order->id,
            ]);

            $order->update(['status' => $applyAmount >= $amountOwed ? 1 : 0]);

            return compact('applyAmount', 'creditAmount');
        });

        $this->order = Order::with('payments')->findOrFail($this->order->id);
        $this->reset('paymentRef','paymentAmount');

        $message = '$'.number_format($result['applyAmount'], 2).' was applied to the invoice.';
        if ($result['creditAmount'] > 0) {
            $message .= ' $'.number_format($result['creditAmount'], 2).' was saved as customer credit.';
        }
        request()->session()->flash('message', $message);
    }

    public function deletePayment($id) {
        DB::transaction(function () use ($id) {
            $order = Order::lockForUpdate()->findOrFail($this->order->id);
            $order->load('customers');
            $payment = Payment::where('order_id', $order->id)->lockForUpdate()->findOrFail($id);

            CustomerCredit::create([
                'customer_id' => $order->customers->firstOrFail()->id,
                'amount' => round((float) $payment->amount, 2),
            ]);
            $payment->delete();
            $order->update(['status' => 0]);
        });

        $this->order = Order::with('payments')->findOrFail($this->order->id);

        request()->session()->flash('message', "Payment deleted and returned to customer credit.");
    }
}
