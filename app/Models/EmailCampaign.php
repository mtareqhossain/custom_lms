<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaign extends Model
{
    protected $fillable = [
        'email_template_id', 'name', 'audience_type', 'audience_filter',
        'recipients_count', 'sent_count', 'failed_count', 'status',
        'created_by', 'sent_at',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'sent_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
