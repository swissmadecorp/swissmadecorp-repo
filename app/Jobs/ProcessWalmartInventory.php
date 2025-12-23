<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Libs\WalmartClass;

class ProcessWalmartInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $skus = '';
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    public function __construct($skus){
        $this->skus = $skus;
    }    

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        
        if ($this->skus) {
            $walmart = new WalmartClass();
            $walmart->updateInventory($this->skus);
            \Log::info($this->skus);

            sleep (5);
        }
    }
}
