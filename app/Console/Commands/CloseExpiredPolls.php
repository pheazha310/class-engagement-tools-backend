<?php

namespace App\Console\Commands;

use App\Services\PollService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:close-expired-polls')]
#[Description('Automatically close active polls whose timer has expired.')]
class CloseExpiredPolls extends Command
{
    public function __construct(
        private readonly PollService $pollService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $closed = $this->pollService->closeExpiredPolls();

        if ($closed > 0) {
            $this->info("Closed {$closed} expired poll(s).");
        } else {
            $this->comment('No expired polls to close.');
        }

        return self::SUCCESS;
    }
}
