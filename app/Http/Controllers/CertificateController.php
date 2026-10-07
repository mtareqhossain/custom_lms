<?php
namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificatePdf;

class CertificateController extends Controller
{
    public function show(string $code)
    {
        $certificate = Certificate::where('code', $code)->with('enrollment.user', 'enrollment.course')->firstOrFail();

        return view('certificates.verify', compact('certificate'));
    }

    public function download(string $code, CertificatePdf $pdf)
    {
        $certificate = Certificate::where('code', $code)->with('enrollment.user', 'enrollment.course')->firstOrFail();
        $user = auth()->user();
        $owns = $user && ($user->id === $certificate->enrollment->user_id || $user->hasRole('admin')
            || ($user->organization_id && $user->organization_id === $certificate->enrollment->organization_id));
        abort_unless($owns, 403);

        return response($pdf->render($certificate->enrollment))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="certificate.pdf"');
    }
}
