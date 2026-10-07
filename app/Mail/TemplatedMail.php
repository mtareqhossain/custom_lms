<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $renderedSubject,
        public string $renderedBody,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->renderedSubject)
            ->view('emails.templated')
            ->with(['bodyHtml' => $this->renderedBody]);
    }
}
