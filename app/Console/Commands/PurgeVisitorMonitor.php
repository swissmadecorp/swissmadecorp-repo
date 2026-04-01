<?php

namespace App\Console\Commands;

use App\Services\VisitorTrackingService;
use Illuminate\Console\Command;

class PurgeVisitorMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitor-monitor:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes visitor monitor records older than the configured retention window.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(VisitorTrackingService $trackingService)
    {
        $purged = $trackingService->purgeExpiredData();

        $this->info(sprintf(
            'Deleted %d visitor sessions and %d visitor profiles older than %d days.',
            $purged['sessions_deleted'],
            $purged['profiles_deleted'],
            $purged['retention_days'],
        ));

        return 0;
    }
}
