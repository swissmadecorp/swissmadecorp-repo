<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SellEmail extends Mailable
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
            ->from($this->event['email'],$this->event['contact_name'])
            //->to('edba1970@yahoo.com')
            ->subject('Swissmade - I want to sell my watch')
            ->view('emails.sellwatch')
            ->with(['contact_name'=>$this->event['contact_name'],
                    'image' => $this->event['filename'],
                    'email'=>$this->event['email'],
                    'phone' => $this->event['phone'],
                    'notes'=>$this->event['notes']]);

    }
}
