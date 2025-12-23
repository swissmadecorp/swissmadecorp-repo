<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailCustomer extends Mailable
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
        $isArray = false;
        $purchasedFrom=$this->event['purchasedFrom'];
        $email = $this;

        // $attachments is an array with file paths of attachments
        if (is_array($this->event['filename']) && count($this->event['filename'])>1) {
            $isArray = true;
        } 

        if (is_array($this->event['filename'])) {
            foreach($this->event['filename'] as $filePath){
                $email->attach(public_path().'/uploads/'.$filePath,[
                    'as' => $filePath,
                    'mime' => 'application/pdf',
                ]);
            }
        } else {
            $email->attach(public_path().'/uploads/'.$this->event['filename'], [
                'as' => $this->event['filename'],
                'mime' => 'application/pdf',
            ]);
        }


        return $email
            ->from($this->event['from'],'SwissMade Corp. Invoice')
            //->to('edba1970@yahoo.com')
            ->subject('Thank you for your order!')
            ->view('emails.invoice')
            ->with([
                'company'=>$this->event['company'],
                'order_id'=>$this->event['order_id'],
                'purchasedFrom'=>$purchasedFrom,
                ]);
        
    }
}
