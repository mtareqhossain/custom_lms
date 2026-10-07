<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Services\TemplateMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $campaignId,
        public string $toEmail,
        public string $toName,
        public array $vars = [],
    ) {
    }

    public function handle(): void
    {
        $campaign = EmailCampaign::find($this->campaignId);
        if (!$campaign || !$campaign->template) {
            Log::warning("SendCampaignEmailJob: campaign {$this->campaignId} or its template is missing.");
            return;
        }

        $sent = TemplateMailer::send(
            $campaign->template->slug,
            $this->toEmail,
            $this->toName,
            $this->vars
        );

        if ($sent) {
            $campaign->increment('sent_count');
        } else {
            $campaign->increment('failed_count');
        }

        // Mark the campaign completed once every dispatched job has reported in.
        if ($campaign->sent_count + $campaign->failed_count >= $campaign->recipients_count) {
            $campaign->update(['status' => 'completed', 'sent_at' => $campaign->sent_at ?? now()]);
        }
    }
}
