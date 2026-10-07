<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProvider implements AIProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $textModel = 'gemini-2.0-flash',
        private readonly string $embeddingModel = 'text-embedding-004',
    ) {
    }

    public function generateText(string $systemPrompt, string $userPrompt, int $maxTokens = 1000): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->textModel}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(60)->post($url, [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $userPrompt]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    public function embed(string $text): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}";

        $response = Http::timeout(60)->post($url, [
            'model' => "models/{$this->embeddingModel}",
            'content' => [
                'parts' => [['text' => $text]],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini embeddings API error: ' . $response->body());
        }

        return $response->json('embedding.values') ?? [];
    }
}
