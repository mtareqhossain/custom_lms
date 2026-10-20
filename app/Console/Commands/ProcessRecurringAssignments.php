<?php
namespace App\Console\Commands;

use App\Models\TrainingAssignment;
use Illuminate\Console\Command;

class ProcessRecurringAssignments extends Command
{
    protected $signature = 'assignments:process-recurring';
    protected $description = 'Spawn the next cycle for completed recurring training assignments';

    public function handle(): int
    {
        $due = TrainingAssignment::whereNotNull('completed_at')
            ->whereNotNull('recurrence')
            ->whereNull('renewed_at')
            ->get();

        $spawned = 0;

        foreach ($due as $assignment) {
            $nextDue = $assignment->nextDueDate($assignment->completed_at);
            if (! $nextDue) { continue; }

            TrainingAssignment::create([
                'organization_id' => $assignment->organization_id,
                'course_id' => $assignment->course_id,
                'user_id' => $assignment->user_id,
                'assigned_by_id' => $assignment->assigned_by_id,
                'due_at' => $nextDue,
                'recurrence' => $assignment->recurrence,
            ]);

            $assignment->update(['renewed_at' => now()]);
            $spawned++;
        }

        $this->info("Spawned {$spawned} renewed assignment(s).");

        return self::SUCCESS;
    }
}
