<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CertificatePdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class CertificateExportController extends Controller
{
    public function __invoke(Course $course, CertificatePdf $pdf): StreamedResponse
    {
        $orgId = Auth::user()->organization_id;

        $enrollments = $course->enrollments()
            ->where('organization_id', $orgId)
            ->whereNotNull('completed_at')
            ->with(['user', 'certificate'])
            ->get();

        abort_if($enrollments->isEmpty(), 404, 'No completed students yet for this course.');

        $zipPath = tempnam(sys_get_temp_dir(), 'certs').'.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($enrollments as $enrollment) {
            if (! $enrollment->certificate) {
                continue;
            }
            $filename = \Illuminate\Support\Str::slug($enrollment->user->name).'-certificate.pdf';
            $zip->addFromString($filename, $pdf->render($enrollment));
        }
        $zip->close();

        $downloadName = \Illuminate\Support\Str::slug($course->title).'-certificates.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend();
    }
}
