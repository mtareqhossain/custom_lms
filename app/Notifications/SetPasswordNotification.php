<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Extends Laravel's own password-reset notification (as set up in Phase 4) so
 * bulk-imported students get a "set your password" link instead of a plaintext
 * password by email — this keeps the security property from Phase 4 intact.
 *
 * NEW in Phase 17: the wording comes from the "welcome_business_import"
 * EmailTemplate so admins can edit the copy from the admin panel, while the
 * actual signed URL is still generated securely in code via Laravel's own
 * token mechanism (never templated as a plain placeholder a well-meaning
 * admin could break).
 */
class SetPasswordNotification extends ResetPassword
{
    public function __construct(string $token, public ?string $organizationName = null)
    {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $template = EmailTemplate::findActiveBySlug('welcome_business_import');

        // Fallback to a safe hardcoded message if the admin deactivated or
        // deleted the template — the account-setup email must never silently
        // fail to go out.
        if (!$template) {
            return (new MailMessage)
                ->subject('Set your password for ' . config('app.name'))
                ->greeting('Hi ' . $notifiable->name . ',')
                ->line('An account has been created for you. Click below to set your password.')
                ->action('Set your password', $url);
        }

        $rendered = $template->render([
            'student_name' => $notifiable->name,
            'app_name' => config('app.name'),
            'organization_name' => $this->organizationName ?? 'your organization',
            'set_password_url' => $url,
        ]);

        return (new MailMessage)
            ->subject($rendered['subject'])
            ->view('emails.templated', ['bodyHtml' => $rendered['body']]);
    }
}
