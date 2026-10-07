<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'slug', 'name', 'subject', 'body', 'description',
        'available_placeholders', 'is_active', 'is_system',
    ];

    protected $casts = [
        'available_placeholders' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Replace {{token}} placeholders in subject/body with real values.
     * Unknown tokens are left as-is (rather than silently becoming blank)
     * so a typo in a template is visible instead of hidden.
     *
     * @param array<string,string> $vars e.g. ['student_name' => 'Jane', 'certificate_url' => '...']
     * @return array{subject: string, body: string}
     */
    public function render(array $vars): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($vars as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', (string) $value, $subject);
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return ['subject' => $subject, 'body' => $body];
    }

    public static function findActiveBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }
}
