<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InquiryEmail extends Mailable
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
	        //->replyTo('info@swissmadecorp.com')
            ->subject('Swissmade New Inquiry')
            ->view('emails.test')
            ->with(['contact_name'=>$this->event['contact_name'],
                    'product' => $this->event['product'],
                    'product_id' => $this->event['product_id'],
                    'image' => $this->event['image'],
                    'email'=>$this->event['email'],
                    'phone' => $this->event['phone'],
                    'notes'=>$this->event['notes']]);

    }
}
