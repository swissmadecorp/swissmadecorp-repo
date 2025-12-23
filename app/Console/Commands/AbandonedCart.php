<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\GmailCustomer; 
use App\Models\Order;

class AbandonedCart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abandoned:cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email customers back a day later if they have abandoned their cart';

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
        $abandonedCarts = \App\Models\AbandonedCart::whereNotNull('emailed')->get();
        if ($abandonedCarts) {
            $email = 'info@swissmadecorp.com';
            foreach ($abandonedCarts as $abandonedCart) {
                $data = array(
                    'template' => 'emails.abandonedcart',
                    'to' => $abandonedCart->email,
                    'fullname' => $abandonedCart->full_name,
                    'from' => $email,
                );
        
                $gmail = new GmailCustomer($data);
                $gmail->send();
            }
        }
        return 0;
    }
}
