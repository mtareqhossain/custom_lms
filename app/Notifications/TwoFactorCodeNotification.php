<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $code)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your ' . config('app.name') . ' login code')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your one-time login code is:')
            ->line(new \Illuminate\Support\HtmlString(
                '<div style="font-size:32px; font-weight:700; letter-spacing:6px; text-align:center; margin:16px 0;">' . $this->code . '</div>'
            ))
            ->line('This code expires in 10 minutes. If you did not try to log in, you can safely ignore this email.');
    }
}
