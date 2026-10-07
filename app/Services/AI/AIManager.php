<?php

namespace App\Services\AI;

/**
 * Resolves which AI provider(s) to use based on .env config, so the rest of
 * the app (content generator, chatbot) never talks to Anthropic/OpenAI SDKs
 * directly — only to this manager. Swapping providers later is a .env change,
 * not a code change.
 *
 * Config (.env):
 *   AI_PROVIDER=anthropic|openai          — used for text generation
 *   AI_EMBEDDING_PROVIDER=openai          — used for embeddings (see note in
 *                                            AnthropicProvider; defaults to
 *                                            AI_PROVIDER if that provider
 *                                            supports embeddings, otherwise
 *                                            falls back to openai)
 *   ANTHROPIC_API_KEY=...
 *   ANTHROPIC_MODEL=claude-sonnet-4-5     (optional override)
 *   OPENAI_API_KEY=...
 *   OPENAI_MODEL=gpt-4o-mini              (optional override)
 *   OPENAI_EMBEDDING_MODEL=text-embedding-3-small (optional override)
 */
class AIManager
{
    public function text(): AIProvider
    {
        return $this->resolve(config('services.ai.provider', 'anthropic'));
    }

    public function embeddings(): AIProvider
    {
        $provider = config('services.ai.embedding_provider')
            ?: config('services.ai.provider', 'anthropic');

        // Anthropic has no embeddings endpoint — fall back to OpenAI automatically
        // rather than failing, unless the caller explicitly configured something else.
        if ($provider === 'anthropic' && !config('services.ai.embedding_provider')) {
            $provider = 'openai';
        }

        return $this->resolve($provider);
    }

    private function resolve(string $provider): AIProvider
    {
        return match ($provider) {
            'openai' => new OpenAIProvider(
                apiKey: config('services.ai.openai.key'),
                textModel: config('services.ai.openai.model', 'gpt-4o-mini'),
                embeddingModel: config('services.ai.openai.embedding_model', 'text-embedding-3-small'),
            ),
            'anthropic' => new AnthropicProvider(
                apiKey: config('services.ai.anthropic.key'),
                model: config('services.ai.anthropic.model', 'claude-sonnet-4-5'),
            ),
            'gemini' => new GeminiProvider(
            apiKey: config('services.ai.gemini.key'),
            textModel: config('services.ai.gemini.model', 'gemini-2.0-flash'),
            embeddingModel: config('services.ai.gemini.embedding_model', 'text-embedding-004'),
        ),
            default => throw new \InvalidArgumentException("Unknown AI provider [{$provider}]"),
        };
    }
}
