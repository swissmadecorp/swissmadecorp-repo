<?php

namespace App\Mail;
use Dacastro4\LaravelGmail\Services\Message\Mail;

class GmailCustomer
{
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
    public function send()
    {
        $token = '';$purchasedFrom='';
        $fh = fopen(base_path().'/token.json','r');
        while ($line = fgets($fh)) {
            $token .= $line;
        }
        fclose($fh);

        $isArrays = false;

        if (isset($this->event['purchasedFrom']))
            $purchasedFrom=$this->event['purchasedFrom'];

        $mail = new Mail;
        $mail->using( $token );

        if (isset($this->event['filename'])) {
            // $attachments is an array with file paths of attachments
            if (is_array($this->event['filename']) && count($this->event['filename'])>0) {
                $isArrays = true;
            }

            if ($isArrays) {
                foreach($this->event['filename'] as $filePath){
                    $mail->attach(public_path().'/uploads/'.$filePath);
                }
            } else {
                $mail->attach(public_path().'/uploads/'.$this->event['filename']);
            }
        }

        if (isset($this->event['to']))
            $to = $this->event['to'];
        else $to = 'info@swissmadecorp.com';

        if (isset($this->event['from'])) {
            $from_email = $this->event['from'];
        } else {
            $from_email = config('mail.from.address');
        }

        $mail->to( $to , null );
        $mail->from( $from_email, $this->event['subject'] );
        $mail->subject( $this->event['subject'] );

        if ($this->event['template']=='html')
            $mail->message('<html><body>'.$this->event['body'].'</body></html>');
        else
            $mail->view($this->event['template'],$this->event);

            // dd($mail);
        return $mail->send();
        //dd($mail);
    }
}
