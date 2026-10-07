<?php

namespace App\Exports;

use App\Models\Quiz;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Flattens every quiz + its questions + options into one row per question —
 * the exact column layout ImportQuizzesJob expects, so export -> edit in
 * Excel -> re-import works as a round trip.
 */
class QuizzesExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'quiz_title', 'course_title', 'lesson_title', 'is_required', 'pass_percentage',
            'max_attempts', 'time_limit_minutes', 'question_type', 'question_text', 'points',
            'option_1', 'option_1_correct', 'option_2', 'option_2_correct',
            'option_3', 'option_3_correct', 'option_4', 'option_4_correct',
        ];
    }

    public function collection()
    {
        $rows = collect();

        Quiz::with(['course', 'lesson', 'questions.options'])->get()->each(function (Quiz $quiz) use ($rows) {
            if ($quiz->questions->isEmpty()) {
                $rows->push($this->baseRow($quiz));
                return;
            }

            foreach ($quiz->questions as $question) {
                $row = $this->baseRow($quiz);
                $row['question_type'] = $question->type;
                $row['question_text'] = $question->question_text;
                $row['points'] = $question->points;

                foreach ($question->options->values() as $i => $option) {
                    if ($i >= 4) {
                        break;
                    }
                    $n = $i + 1;
                    $row["option_{$n}"] = $option->option_text;
                    $row["option_{$n}_correct"] = $option->is_correct ? 'yes' : 'no';
                }

                $rows->push($row);
            }
        });

        return $rows;
    }

    private function baseRow(Quiz $quiz): array
    {
        return [
            'quiz_title' => $quiz->title,
            'course_title' => $quiz->course->title,
            'lesson_title' => $quiz->lesson?->title ?? '',
            'is_required' => $quiz->is_required ? 'yes' : 'no',
            'pass_percentage' => $quiz->pass_percentage,
            'max_attempts' => $quiz->max_attempts ?? '',
            'time_limit_minutes' => $quiz->time_limit_minutes ?? '',
            'question_type' => '',
            'question_text' => '',
            'points' => '',
            'option_1' => '', 'option_1_correct' => '',
            'option_2' => '', 'option_2_correct' => '',
            'option_3' => '', 'option_3_correct' => '',
            'option_4' => '', 'option_4_correct' => '',
        ];
    }
}
