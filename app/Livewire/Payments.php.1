<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Models\Payment;

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

    public function savePayment($totalLeft) {
        $validated=$this->validate();

        if ($this->paymentAmount > $totalLeft)
            $applyAmount = $totalLeft;
        else $applyAmount = $this->paymentAmount;

        $orderId = $this->order->id;
        
        Payment::create ([
            'amount' => $applyAmount,
            'ref' => $this->paymentRef,
            'order_id' => $orderId
        ]);

        if ($this->order->payments->sum('amount') == $this->order->total) {
            $this->order->update([
                'status' => 1
            ]);
        }

        $this->reset('paymentRef','paymentAmount');
        request()->session()->flash('message', "Successfully saved payment!");
        
    }

    public function deletePayment($id) {
        $payment = Payment::find($id);
        $payment->delete();

        Order::find($payment->order_id)->update(['status' => 0]);

        request()->session()->flash('message', "Successfully deleted payment!");
    }
}
