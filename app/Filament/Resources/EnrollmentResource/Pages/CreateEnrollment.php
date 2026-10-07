<?php
namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Enums\EnrollmentSource;
use App\Filament\Resources\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    /** Goes through Enrollment::grant() so manual enrollments behave exactly like every other kind. */
    protected function handleRecordCreation(array $data): Model
    {
        $enrollment = Enrollment::grant(
            User::findOrFail($data['user_id']),
            Course::findOrFail($data['course_id']),
            EnrollmentSource::Admin,
            $data['organization_id'] ?? null,
        );

        if (! empty($data['expires_at'])) {
            $enrollment->update(['expires_at' => $data['expires_at']]);
        }

        return $enrollment;
    }
}
