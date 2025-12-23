<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
//use App\Mail\GmailCustomer; 
use App\Mail\GMailer;
use App\Libs\MassMail;
use App\Models\Newsletter;
use App\Models\MailMass;

class ProcessNewsLetter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'massmail:newsletter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mass Mail Newsletter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$massmail = MailMass::where('is_active',1)->first();
        $request = ['loadWithTemplate'=>1,'category'=>''];

        $response = MassMail::process($request);
        //$i=0;
        if ($response) {
            $emails = Newsletter::where('subscribed','=',1)->get();
            foreach ($emails as $newsletter) {
                //$i++;
                $to =  $newsletter->email;
                $data = array(
                    'template' => 'emails.html',
                    'from' => 'info@swissmadecorp.com',
                    'to' => $to,
                    'subject' => 'Swiss Made Corp. - Newsletter',
                    'body' => $response. '<div style="text-align: center; background-color: #444; color: #fff; padding: 5px;">
                    <p>Copyright © 2019 - 2020 SWISS MADE CORP., All rights reserved.</p>
                    <p>NYC | 15 W 47TH ST | ROOM 503, 5TH FLOOR | NEW YORK, NY 10036 | 212-840-8463</p>
                    <p>Want to unsubscribe from this list, click <a style="color: #8ab5ff;text-decoration: none;" href="https://swissmadecorp.com/unsubscribe/'.$to.'">here</a>.</p>
                    </div>'
                );

                //if ($i==5) break;
                //$gmail = new GmailCustomer($data);
                //$gmail->send();
                $gmailer = new GMailer($data);
                $gmailer->send();
                // break;
            }
            
            //\Log::debug('sent');
            
        } 
    }
}
