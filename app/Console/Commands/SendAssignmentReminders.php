<?php
namespace App\Console\Commands;

use App\Models\TrainingAssignment;
use App\Services\TemplateMailer;
use Illuminate\Console\Command;

class SendAssignmentReminders extends Command
{
    protected $signature = 'assignments:send-reminders';
    protected $description = 'Email learners whose assigned training is due soon or overdue (at most once a day)';

    public function handle(): int
    {
        $assignments = TrainingAssignment::with(['user', 'course'])
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now()->addDays(3))
            ->where(fn ($q) => $q->whereNull('last_reminder_at')->orWhere('last_reminder_at', '<', now()->startOfDay()))
            ->get();

        $sent = 0;

        foreach ($assignments as $a) {
            $overdue = $a->due_at->isPast();

            TemplateMailer::send(
                $overdue ? 'assignment_overdue' : 'assignment_due_soon',
                $a->user->email,
                $a->user->name,
                [
                    'student_name' => $a->user->name,
                    'course_title' => $a->course->title,
                    'due_date' => $a->due_at->format('j M Y'),
                    'days' => (string) abs(now()->diffInDays($a->due_at, false)),
                    'app_name' => config('app.name'),
                ]
            );

            $a->update(['last_reminder_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} assignment reminder(s).");

        return self::SUCCESS;
    }
}
