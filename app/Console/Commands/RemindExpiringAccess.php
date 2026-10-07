<?php
namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Notifications\ExpiringAccessNotification;
use Illuminate\Console\Command;

class RemindExpiringAccess extends Command
{
    protected $signature = 'lms:remind-expiring';
    protected $description = 'Email students whose paid course access expires in 3 days';

    public function handle(): int
    {
        $enrollments = Enrollment::with(['user', 'course', 'organization'])
            ->whereNull('completed_at')
            ->whereNull('reminded_at')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', now()->addDays(3)->toDateString())
            ->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->user->notify(new ExpiringAccessNotification($enrollment));
            $enrollment->update(['reminded_at' => now()]);
        }

        $this->info("Sent {$enrollments->count()} expiring-access reminder(s).");

        return self::SUCCESS;
    }
}
