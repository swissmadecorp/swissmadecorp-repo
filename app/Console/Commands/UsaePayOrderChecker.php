<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Omnipay\Omnipay;
use App\Models\Order;
use App\Models\Product;
use App\Mail\GMailer; 
use Illuminate\Console\Command;

class UsaePayOrderChecker extends Command
{
    protected $signature = 'verify:creditcard';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify credit card through UsaePay.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    private function checkIfOrderIsValid($latestOrderId) {
        $post_array = array(
            'ApiLogin' => 'bCbp27XH',
            'ApiKey' => '66r6neTYbIzEUlon',
            'Action' => 'getOrderStatus',
            'OrderNumber'  => $latestOrderId
        );

        // Convert post array into a query string
        $post_query = http_build_query($post_array);
        
        // Do the POST
        $ch = curl_init('https://eye4fraud.com/api/');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Show response
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

        return $array;
    }

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
        $orders = Order::whereNotNull('sale_id')
            ->get();

        foreach ($orders as $order) {
            $return = $this->checkIfOrderIsValid($order->sale_id);
            
            if (!isset($return['error'])) {
                if(in_array($return['keyvalue'][1]['value'],['A','I','ALW'])) {
                    $saleId = $order['sale_id'];
                    $code = $order['code'];
                    $order->update([
                        'sale_id' => null,
                        'cc_status' => $return['keyvalue'][2]['value']
                    ]);

                    // Captured from the authorization response.
                    $gateway = Omnipay::create('AuthorizeNetApi_Api');
                    $gateway->setAuthName(config('authorize_net.login_id'));
                    $gateway->setTransactionKey(config('authorize_net.key'));

                    $response = $gateway->capture([
                        'amount' => $order['total'],
                        'currency' => 'USD',
                        'transactionReference' => $code,
                    ])->send();
                    
                    $data = array(
                        'template' => 'emails.ccverification',
                        'to' =>'info@swissmadecorp.com',
                        'status' => 'Insured',
                        'order_id' => $order->id,
                        'subject' => 'Order: '.$order->id. " Insured",
                        'from' => 'info@swissmadecorp.com'
                    );
            
                    $gmail = new GMailer($data);
                    $gmail->send();
                } elseif(in_array($return['keyvalue'][1]['value'],['C','R','Inv','M','Err','W','U','D','E','F'])) {
                    $order->sale_id = null;
                    $order->cc_status = $return['keyvalue'][2]['value'];
                    $order->save();
                } elseif(in_array($return['keyvalue'][1]['value'],['P'])) {
                    $order->update([
                        'cc_status' => $return['keyvalue'][2]['value']
                    ]);
                }

            } else {
                $order->update([
                    'cc_status' => $return['error']
                ]);
            }
        }

    }
}
