<?php

namespace App\Console\Commands;

use App\Services\NewsletterSender;
use Illuminate\Console\Command;

class ProcessNewsLetter extends Command
{
    protected $signature = 'massmail:newsletter
                            {--start-from= : Start with this newsletter ID (inclusive)}';

    protected $description = 'Send the active monthly inventory email to newsletter subscribers';

    public function handle(NewsletterSender $sender): int
    {
        $startFrom = $this->option('start-from');

        if ($startFrom !== null && (! ctype_digit((string) $startFrom) || (int) $startFrom < 1)) {
            $this->error('The --start-from value must be a positive newsletter ID.');

            return self::FAILURE;
        }

        $startFromId = $startFrom === null ? null : (int) $startFrom;

        if ($startFromId !== null) {
            $this->info("Resuming with newsletter ID {$startFromId}.");
        }

        $sent = $sender->send(startFromId: $startFromId);
        $this->info("Newsletter delivered to {$sent} subscribers.");

        return self::SUCCESS;
    }
}
