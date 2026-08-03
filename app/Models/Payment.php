<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    
    protected $table = "order_payment";
    protected $guarded = [];

    protected float $overpaymentAmount = 0;
    protected ?int $creditCustomerId = null;

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (!$payment->order_id) {
                return;
            }

            $order = Order::with('customers')->findOrFail($payment->order_id);
            $amountReceived = round((float) $payment->amount, 2);
            $amountPaid = round((float) static::where('order_id', $order->id)->sum('amount'), 2);
            $amountOwed = max(0, round((float) $order->total - $amountPaid, 2));

            $payment->amount = min($amountReceived, $amountOwed);
            $payment->overpaymentAmount = max(0, round($amountReceived - (float) $payment->amount, 2));
            $payment->creditCustomerId = $order->customers->first()?->id;
        });

        static::created(function (Payment $payment) {
            if ($payment->overpaymentAmount <= 0 || !$payment->creditCustomerId) {
                return;
            }

            CustomerCredit::create([
                'customer_id' => $payment->creditCustomerId,
                'amount' => $payment->overpaymentAmount,
            ]);
        });
    }

    public function orders() {
        return $this->belongsTo(Order::class);
    }
}
