<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
//use App\Mail\GmailCustomer;
use App\Mail\GMailer;
use App\Models\Order;

class EmailTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email tracking number to customers';

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
        $orders = Order::where('emailed_tracking','0')
            ->whereNotNull('tracking')
            ->get();

        foreach ($orders as $order) {
            $order->emailed_tracking=1;
            $order->update();

            if ($order->email) {
                $data = array(
                    'template' => 'emails.emailtracking',
                    'to' =>$order->email,
                    'tracking' => $order->tracking,
                    'subject' => 'Your item shipped!',
                    'country' => ($order->s_country == 231) ? "US" : 'Other'
                );

                $gmail = new GMailer($data);
                $gmail->send();
            }
        }

        return 0;
    }
}
