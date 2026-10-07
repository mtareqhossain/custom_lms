<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\CourseContentChunk;
use App\Models\User;
use App\Services\AI\AIManager;

/**
 * Answers a student's question using ONLY that course's lesson content
 * (retrieval-augmented generation), rather than a general-purpose chatbot.
 * This keeps answers on-topic and reduces hallucination: if nothing in the
 * course covers the question, the model is instructed to say so rather than
 * answer from general knowledge.
 */
class CourseChatbot
{
    public function __construct(
        private readonly AIManager $ai,
        private readonly EmbeddingSearch $search,
    ) {
    }

    protected const SYSTEM_PROMPT = <<<'PROMPT'
You are a helpful teaching assistant for a specific online course. Answer the
student's question using ONLY the course excerpts provided below as context.
If the excerpts don't contain enough information to answer, say clearly that
this isn't covered in the course material and suggest the student ask their
instructor — do not answer from general knowledge outside the excerpts.
Keep answers concise and encouraging.
PROMPT;

    /**
     * @throws \RuntimeException if the student is not enrolled in the course
     */
    public function ask(User $student, Course $course, string $question): ChatMessage
    {
        $isEnrolled = $student->enrollments()->where('course_id', $course->id)->exists();
        if (!$isEnrolled && !$student->hasAnyRole(['admin', 'instructor'])) {
            throw new \RuntimeException('You must be enrolled in this course to use its chatbot.');
        }

        $chunks = CourseContentChunk::where('course_id', $course->id)->get();

        if ($chunks->isEmpty()) {
            $answer = "This course's content hasn't been indexed for the chatbot yet. "
                . "Please ask your instructor, or try again after the course content is updated.";

            return ChatMessage::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'question' => $question,
                'answer' => $answer,
                'source_chunk_ids' => [],
            ]);
        }

        $queryEmbedding = $this->ai->embeddings()->embed($question);
        $topChunks = $this->search->topMatches($queryEmbedding, $chunks, limit: 4);

        $context = $topChunks->map(fn ($c) => "- " . $c->chunk_text)->implode("\n");

        $userPrompt = "Course: {$course->title}\n\nRelevant course excerpts:\n{$context}\n\nStudent question: {$question}";

        $answer = $this->ai->text()->generateText(self::SYSTEM_PROMPT, $userPrompt, maxTokens: 600);

        return ChatMessage::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'question' => $question,
            'answer' => $answer,
            'source_chunk_ids' => $topChunks->pluck('id')->all(),
        ]);
    }
}
