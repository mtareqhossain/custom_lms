<?php
namespace App\Jobs;

use App\Enums\EnrollmentSource;
use App\Imports\HeadingsImport;
use App\Models\BulkImport;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/** Replaces the Phase 4 job (four bugs fixed, see README). */
class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(private readonly int $bulkImportId, private readonly int $courseId) {}

    public function handle(): void
    {
        $import = BulkImport::findOrFail($this->bulkImportId);
        $course = Course::findOrFail($this->courseId);
        $organization = \App\Models\Organization::find($import->organization_id);
        $import->update(['status' => 'processing']);

        try {
            $rows = Excel::toCollection(new HeadingsImport, Storage::disk('local')->path($import->file_path))->first() ?? collect();
        } catch (\Throwable $e) {
            $import->update(['status' => 'failed', 'errors' => [['row' => 0, 'error' => 'Could not read the file: '.$e->getMessage()]]]);
            return;
        }

        $errors = [];
        $processed = 0;
        $failed = 0;

        foreach ($rows as $i => $row) {
            if ($row->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue; // blank spreadsheet row
            }

            $data = [
                'name' => trim((string) ($row['name'] ?? '')),
                'email' => trim(strtolower((string) ($row['email'] ?? ''))),
                'employee_ref' => trim((string) ($row['employee_ref'] ?? '')) ?: null,
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:150',
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[] = ['row' => $i + 2, 'error' => $validator->errors()->first()];
                continue;
            }

            $existing = User::where('email', $data['email'])->first();

            if ($existing && $existing->organization_id && $existing->organization_id !== $import->organization_id) {
                $failed++;
                $errors[] = ['row' => $i + 2, 'error' => 'Email belongs to a different organization'];
                continue;
            }

            $user = $existing ?? new User(['email' => $data['email']]);
            $isNew = ! $user->exists;

            $user->fill([
                'name' => $isNew ? $data['name'] : $user->name,
                'organization_id' => $import->organization_id,
                'employee_ref' => $data['employee_ref'] ?? $user->employee_ref,
            ]);
            if ($isNew) {
                $user->password = Hash::make(Str::random(32));
                $user->email_verified_at = now();
            }
            $user->save();

            if ($isNew) {
                $user->assignRole('student');
                // $user->notify(new SetPasswordNotification(Password::createToken($user)));
                $user->notify(new SetPasswordNotification(Password::createToken($user), $organization?->name));
            }

            Enrollment::grant($user, $course, EnrollmentSource::Business, $import->organization_id);
            $processed++;
        }

        $import->update([
            'status' => 'completed',
            'total_rows' => $processed + $failed,
            'processed_rows' => $processed,
            'failed_rows' => $failed,
            'errors' => array_slice($errors, 0, 100),
        ]);
    }
}
