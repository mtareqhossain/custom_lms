<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PruneUnverifiedUsers extends Command
{
    protected $signature = 'users:prune-unverified';
    protected $description = 'Delete accounts that never verified their email within 7 days';

    public function handle(): int
    {
        $deleted = User::whereNull('email_verified_at')
            ->where('created_at', '<', now()->subDays(7))
            ->whereDoesntHave('enrollments') // never touch anyone who actually enrolled in something
            ->delete();

        $this->info("Deleted {$deleted} unverified account(s).");

        return self::SUCCESS;
    }
}
