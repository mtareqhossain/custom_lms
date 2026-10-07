<?php

namespace App\Services;

use App\Mail\TemplatedMail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TemplateMailer
{
    /**
     * Render and send a named template to one recipient.
     *
     * If the template is missing or inactive, this logs a warning and
     * returns false instead of throwing — a missing/disabled template
     * (e.g. an admin accidentally deactivates "course_completed_student")
     * should never break the student-facing flow that triggered it
     * (course completion, registration, etc).
     *
     * @param array<string,string> $vars placeholder values, e.g. ['student_name' => 'Jane']
     */
    public static function send(string $slug, string $toEmail, string $toName, array $vars = []): bool
    {
        $template = EmailTemplate::findActiveBySlug($slug);

        if (!$template) {
            Log::warning("TemplateMailer: template [{$slug}] not found or inactive; email to {$toEmail} skipped.");
            return false;
        }

        $rendered = $template->render($vars);

        Mail::to($toEmail, $toName)->send(new TemplatedMail($rendered['subject'], $rendered['body']));

        return true;
    }
}
