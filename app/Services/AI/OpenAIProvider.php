<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIProvider implements AIProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $textModel = 'gpt-4o-mini',
        private readonly string $embeddingModel = 'text-embedding-3-small',
    ) {
    }

    public function generateText(string $systemPrompt, string $userPrompt, int $maxTokens = 1000): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->textModel,
                'max_tokens' => $maxTokens,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function embed(string $text): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $this->embeddingModel,
                'input' => $text,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI embeddings API error: ' . $response->body());
        }

        return $response->json('data.0.embedding') ?? [];
    }
}
