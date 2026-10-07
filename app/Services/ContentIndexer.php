<?php

namespace App\Services;

use App\Models\CourseContentChunk;
use App\Models\Lesson;
use App\Services\AI\AIManager;
use Illuminate\Support\Str;

/**
 * Splits a lesson's HTML content into plain-text chunks and embeds each one,
 * so the chatbot can later search "what part of this course is relevant to
 * the student's question" without re-reading the whole course on every ask.
 *
 * Call indexLesson() any time a lesson's content is created or changed
 * (the AiContentGenerator page already does this when it applies a draft —
 * also wire it into your normal lesson Create/Edit Filament pages, see
 * wiring-notes.patch.md).
 */
class ContentIndexer
{
    private const CHUNK_WORD_COUNT = 180; // ~roughly 1 paragraph-ish chunk
    private const CHUNK_OVERLAP_WORDS = 30; // keeps context from being cut mid-thought

    public function __construct(private readonly AIManager $ai)
    {
    }

    public function indexLesson(Lesson $lesson): void
    {
        // Remove the lesson's previous chunks — simplest correct approach;
        // re-embedding an edited lesson is cheap at this content scale.
        CourseContentChunk::where('lesson_id', $lesson->id)->delete();

        $plainText = trim(strip_tags($lesson->content ?? ''));
        if ($plainText === '') {
            return;
        }

        $courseId = $lesson->section->course_id ?? $lesson->section()->value('course_id');

        $chunks = $this->splitIntoChunks($plainText);
        $embedder = $this->ai->embeddings();

        foreach ($chunks as $index => $chunkText) {
            try {
                $embedding = $embedder->embed($chunkText);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    "ContentIndexer: embedding failed for lesson {$lesson->id} chunk {$index}: " . $e->getMessage()
                );
                continue;
            }

            CourseContentChunk::create([
                'course_id' => $courseId,
                'lesson_id' => $lesson->id,
                'chunk_index' => $index,
                'chunk_text' => $chunkText,
                'embedding' => $embedding,
                'embedding_model' => config('services.ai.openai.embedding_model', 'text-embedding-3-small'),
            ]);
        }
    }

    public function indexCourse(\App\Models\Course $course): void
    {
        foreach ($course->sections as $section) {
            foreach ($section->lessons as $lesson) {
                $this->indexLesson($lesson);
            }
        }
    }

    /**
     * @return string[] word-bounded chunks with a small overlap between them
     */
    private function splitIntoChunks(string $text): array
    {
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $step = self::CHUNK_WORD_COUNT - self::CHUNK_OVERLAP_WORDS;

        for ($start = 0; $start < count($words); $start += $step) {
            $slice = array_slice($words, $start, self::CHUNK_WORD_COUNT);
            if (empty($slice)) {
                break;
            }
            $chunks[] = implode(' ', $slice);
            if ($start + self::CHUNK_WORD_COUNT >= count($words)) {
                break;
            }
        }

        return $chunks;
    }
}
