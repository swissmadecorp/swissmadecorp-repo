<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailConfirm extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    
    public $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from($this->event['from'],$this->event['order_from'])
            ->subject($this->event['order_from'].' purchase')
            ->view($this->event['form'])
            ->with([
                'item' => $this->event['item'],
                'customer_email' =>$this->event['customer_email'],
                'fullname' =>$this->event['fullname'],
                'phone' =>$this->event['phone'],
                'is_confirmed'=>$this->event['is_confirmed'],
            ]);
        
    }
}
