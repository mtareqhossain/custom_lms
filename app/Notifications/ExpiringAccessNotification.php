<?php
namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiringAccessNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Enrollment $enrollment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->enrollment->course;
        $mail = (new MailMessage)
            ->subject('Your access to '.$course->title.' ends soon')
            ->line('Your access to "'.$course->title.'" ends on '.$this->enrollment->expires_at->format('j F Y').'.')
            ->line('You are '.$this->enrollment->progress_percent.'% of the way through. Finish up before your access ends.')
            ->action('Continue the course', route('learn.show', $course));

        if ($this->enrollment->organization_id) {
            $bcc = $this->enrollment->organization?->contact_email;
            if ($bcc) {
                $mail->bcc($bcc);
            }
        }

        return $mail;
    }
}
