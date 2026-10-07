<?php

namespace App\Services\AI;

class CourseContentGenerator
{
    public function __construct(private readonly AIManager $ai)
    {
    }

    protected const SYSTEM_PROMPT = <<<'PROMPT'
You are an instructional content writer helping an admin draft content for an
online course on a learning management system. Write clear, well-structured
HTML (use <p>, <ul>, <ol>, <strong>, <h3> as appropriate — no <html>/<body>
wrapper, no inline styles, no markdown). Keep the tone professional and
educational. Do not invent facts you're not given — if the topic requires
domain expertise the admin hasn't supplied, write generally accurate,
widely-known content and keep claims conservative.
PROMPT;

    protected const QUIZ_SYSTEM_PROMPT = <<<'PROMPT'
You write multiple-choice quiz questions for an online course. You MUST
respond with ONLY valid JSON — no markdown fences, no commentary before or
after — matching exactly this shape:

[
  {"question": "...", "options": ["...", "...", "...", "..."], "correct_index": 0},
  ...
]

Each question must have exactly 4 options, with correct_index (0-3) pointing
to the single correct one. Do not invent facts you're not given — keep
questions generally accurate and conservative if the topic requires expertise
the admin hasn't supplied.
PROMPT;

    /**
     * @param string $contentType one of: lesson_content, course_description, quiz_questions
     */
    public function generate(string $contentType, string $topic, string $instructions = ''): string
    {
        $userPrompt = match ($contentType) {
            'lesson_content' => "Write the lesson content for a lesson titled \"{$topic}\". "
                . "Additional instructions: " . ($instructions ?: 'none') . ". "
                . "Aim for 300-600 words of HTML body content.",
            'course_description' => "Write a compelling course description/overview for a course titled \"{$topic}\". "
                . "Additional instructions: " . ($instructions ?: 'none') . ". "
                . "Aim for 100-200 words, as HTML.",
            default => throw new \InvalidArgumentException("Use generateQuizQuestions() for quiz_questions."),
        };

        return $this->ai->text()->generateText(self::SYSTEM_PROMPT, $userPrompt, maxTokens: 1500);
    }

    /**
     * Returns structured question data — NOT html — so the caller can create
     * real Quiz/QuizQuestion/QuizQuestionOption records instead of dumping
     * text into a content field.
     *
     * @return array<int,array{question:string,options:string[],correct_index:int}>
     */
    public function generateQuizQuestions(string $topic, string $instructions = '', int $count = 5): array
    {
        $userPrompt = "Write {$count} multiple-choice quiz questions testing understanding of \"{$topic}\". "
            . "Additional instructions: " . ($instructions ?: 'none');

        $raw = $this->ai->text()->generateText(self::QUIZ_SYSTEM_PROMPT, $userPrompt, maxTokens: 1800);

        // Models sometimes wrap JSON in ```json fences despite instructions — strip if present.
        $cleaned = trim($raw);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $decoded = json_decode($cleaned, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('The AI did not return valid quiz JSON. Raw response: ' . substr($raw, 0, 500));
        }

        foreach ($decoded as $i => $q) {
            if (!isset($q['question'], $q['options'], $q['correct_index']) || !is_array($q['options']) || count($q['options']) < 2) {
                throw new \RuntimeException("Question #{$i} from the AI is malformed — try generating again.");
            }
        }

        return $decoded;
    }
}
