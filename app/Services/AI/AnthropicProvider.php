<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Talks to the Anthropic Messages API for text generation.
 *
 * NOTE on embeddings: Anthropic does not publish a first-party embeddings
 * endpoint. If AI_PROVIDER=anthropic, embeddings for the chatbot's RAG search
 * still need a source — set AI_EMBEDDING_PROVIDER=openai in .env (with its own
 * OPENAI_API_KEY) to use OpenAI just for embeddings while Claude handles all
 * the actual text generation (content drafts + chatbot answers). This is the
 * default pairing AIManager uses when AI_PROVIDER=anthropic.
 */
class AnthropicProvider implements AIProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'claude-sonnet-4-5',
    ) {
    }

    public function generateText(string $systemPrompt, string $userPrompt, int $maxTokens = 1000): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Anthropic API error: ' . $response->body());
        }

        $json = $response->json();

        return $json['content'][0]['text'] ?? '';
    }

    public function embed(string $text): array
    {
        throw new RuntimeException(
            'AnthropicProvider does not support embeddings. Set AI_EMBEDDING_PROVIDER=openai in .env '
            . 'and configure OPENAI_API_KEY to enable the chatbot\'s content search.'
        );
    }
}
