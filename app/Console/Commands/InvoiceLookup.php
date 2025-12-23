<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class InvoiceLookup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:lookup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Admin about past due invoices';

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
        $to = date('Y-m-d',time());
        $from = date('Y-m-d',strtotime("-30 days"));
        
        $invoices = Order::where("status",0)
            //->whereBetween('created_at', array($from, $to))->get();
            ->where('created_at', '<=', $from)->get();
        
        foreach ($invoices as $invoice) {
            //\Log::info($invoice->b_company.'('.$invoice->id . ') - Past Due');
        }

       // \Log::info('Invoice lookup has run successfully');
        $this->info('Invoice lookup has run successfully');
    }
}
