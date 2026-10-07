<?php

namespace App\Services;

/**
 * Pure-PHP cosine similarity search over embeddings already stored in MySQL.
 * Fine at this scale (a single course's chunks loaded per search); if a
 * course's content library grows into the thousands of chunks, this is the
 * piece to swap for a dedicated vector database — nothing else in the
 * chatbot flow would need to change.
 */
class EmbeddingSearch
{
    /**
     * @param float[] $queryEmbedding
     * @param \Illuminate\Support\Collection<int,\App\Models\CourseContentChunk> $chunks
     * @return \Illuminate\Support\Collection<int,\App\Models\CourseContentChunk> top matches, best first
     */
    public function topMatches(array $queryEmbedding, \Illuminate\Support\Collection $chunks, int $limit = 4): \Illuminate\Support\Collection
    {
        return $chunks
            ->filter(fn ($chunk) => !empty($chunk->embedding))
            ->map(function ($chunk) use ($queryEmbedding) {
                $chunk->similarity = $this->cosineSimilarity($queryEmbedding, $chunk->embedding);
                return $chunk;
            })
            ->sortByDesc('similarity')
            ->take($limit)
            ->values();
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $count = min(count($a), count($b));
        if ($count === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $dot += $a[$i] * $b[$i];
            $magA += $a[$i] ** 2;
            $magB += $b[$i] ** 2;
        }

        if ($magA == 0.0 || $magB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }
}
