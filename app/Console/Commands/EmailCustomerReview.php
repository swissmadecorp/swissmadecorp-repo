<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\GMailer; 
use App\Models\Order;
use SoapClient;

class EmailCustomerReview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email customer review to the customers when the order was received';

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
     * @return int
     */
    public function handle()
    {
        $order=Order::whereNull('review_emailed')
            ->whereNotNull('code')
            ->first();

        if ($order) {
            if ($order->tracking) {
                $order->update(['review_emailed'=>1]);

                $path_to_wsdl=realpath(dirname(__FILE__) . '/../..').'/Libs/TrackService_v18.wsdl';
                ini_set("soap.wsdl_cache_enabled", "0");

                $opts = array(
                    'ssl' => array('verify_peer' => false, 'verify_peer_name' => false)
                    );
                $client = new SoapClient($path_to_wsdl, array('trace' => 1,'stream_context' => stream_context_create($opts)));  // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

                $input['WebAuthenticationDetail'] = array(
                    'UserCredential' => array(
                        'Key' => 'cXf0zpcJkEVmYt13', 
                        'Password' => '18ouaGyRZ1fKJYwek3H3Cnju1'
                    )
                );

                $input['ClientDetail'] = array(
                    'AccountNumber' => '676149627', 
                    'MeterNumber' => '250604979'
                );
                $input['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
                $input['Version'] = array(
                    'ServiceId' => 'trck', 
                    'Major' => '18', 
                    'Intermediate' => '0', 
                    'Minor' => '0'
                );
                $input['SelectionDetails'] = array(
                    'PackageIdentifier' => array(
                        'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
                        'Value' => $order->tracking // Replace 'XXX' with a valid tracking identifier
                    )
                );
                $input['ProcessingOptions'] = 'INCLUDE_DETAILED_SCANS';
                
                try {            
                    $response = $client ->track($input);
                    
                    if ($response->CompletedTrackDetails->TrackDetails->Events[0]->EventDescription == 'Delivered') {
                        $timestamp = explode('T',$response->CompletedTrackDetails->TrackDetails->Events[0]->Timestamp);
                        $origin = new \DateTime($timestamp[0]);
                        $target = new \DateTime(now());
                        $interval = $origin->diff($target);
                        
                        $email = 'info@swissmadecorp.com';
                        $subject = 'Swiss Made Corp.';

                        if ($interval->format('%a') > 2) {
                            $data = array(
                                'template' => 'emails.review',
                                'to' => $order->email,
                                'fullname' => $order->s_company,
                                'code' => $order->code,
                                'subject' => $subject,
                                'from' => $email,
                            );
                    
                            $gmail = new GMailer($data);
                            $gmail->send();

                            return array('fullname'=>$order->s_company,'code'=>$order->code);
                        }
                    }
                } catch (SoapFault $exception) {
                    printFault($exception, $client);
                }
                //f34e7fd8-f49c-44f4-a8d3-aa08306773cc
            } else {
                return 1;
            }
        }
        
        return 0;
    }
}
