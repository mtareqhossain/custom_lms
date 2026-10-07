<?php

namespace App\Services\AI;

interface AIProvider
{
    /**
     * Generate text from a prompt (content generation, chatbot answers).
     *
     * @param string $systemPrompt instructions that shape the whole response
     * @param string $userPrompt the actual request/question
     * @param int $maxTokens cap on response length (controls cost)
     */
    public function generateText(string $systemPrompt, string $userPrompt, int $maxTokens = 1000): string;

    /**
     * Turn text into an embedding vector for similarity search.
     *
     * @return float[] the embedding vector
     */
    public function embed(string $text): array;
}
