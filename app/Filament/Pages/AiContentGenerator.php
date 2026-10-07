<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Services\AI\CourseContentGenerator;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Admin-only tool for drafting course content with AI. "Lesson content" and
 * "Course description" generate HTML that gets applied to the matching
 * field. "Quiz questions" is different: it generates STRUCTURED data and,
 * on Apply, creates a real Quiz + QuizQuestion + QuizQuestionOption records
 * (Phase 19's quiz system) — not HTML dumped into a lesson's content field.
 */
class AiContentGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'AI Content Generator';

    protected static string $view = 'filament.pages.ai-content-generator';

    public ?array $data = [];

    public ?string $generatedHtml = null;

    /** @var array<int,array{question:string,options:string[],correct_index:int}>|null */
    public ?array $generatedQuizQuestions = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'content_type' => 'lesson_content',
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('content_type')
                    ->label('What do you want to generate?')
                    ->options([
                        'lesson_content' => 'Lesson content',
                        'course_description' => 'Course description',
                        'quiz_questions' => 'Quiz questions (creates a real quiz)',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(Course::pluck('title', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),

                Forms\Components\Select::make('lesson_id')
                    ->label(fn (Forms\Get $get) => $get('content_type') === 'quiz_questions'
                        ? 'Lesson (leave blank for a course-final quiz)'
                        : 'Lesson (to apply the draft to)')
                    ->options(fn (Forms\Get $get) => Lesson::query()
                        ->whereHas('section', fn ($q) => $q->where('course_id', $get('course_id')))
                        ->pluck('title', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('content_type') !== 'course_description')
                    ->required(fn (Forms\Get $get) => $get('content_type') === 'lesson_content')
                    ->searchable(),

                Forms\Components\TextInput::make('topic')
                    ->label('Topic / title')
                    ->required()
                    ->helperText('E.g. the lesson title, or the subject to write about. For quizzes, this also becomes the quiz title.'),

                Forms\Components\TextInput::make('question_count')
                    ->label('Number of questions')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->default(5)
                    ->visible(fn (Forms\Get $get) => $get('content_type') === 'quiz_questions'),

                Forms\Components\Textarea::make('instructions')
                    ->label('Extra instructions (optional)')
                    ->rows(3)
                    ->helperText('Tone, length, specific points to cover, audience level, etc.'),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $state = $this->form->getState();
        $this->generatedHtml = null;
        $this->generatedQuizQuestions = null;

        try {
            /** @var CourseContentGenerator $generator */
            $generator = app(CourseContentGenerator::class);

            if ($state['content_type'] === 'quiz_questions') {
                $this->generatedQuizQuestions = $generator->generateQuizQuestions(
                    $state['topic'],
                    $state['instructions'] ?? '',
                    (int) ($state['question_count'] ?? 5)
                );
            } else {
                $this->generatedHtml = $generator->generate(
                    $state['content_type'],
                    $state['topic'],
                    $state['instructions'] ?? ''
                );
            }

            Notification::make()->title('Draft generated — review before applying')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('AI generation failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function apply(): void
    {
        $state = $this->form->getState();

        if ($state['content_type'] === 'quiz_questions' && $this->generatedQuizQuestions) {
            $this->applyQuizQuestions($state);
            return;
        }

        if (!$this->generatedHtml) {
            return;
        }

        if ($state['content_type'] === 'course_description') {
            $course = Course::findOrFail($state['course_id']);
            $course->update(['description' => $this->generatedHtml]);
            $target = "course \"{$course->title}\"";
        } else {
            $lesson = Lesson::findOrFail($state['lesson_id']);
            $lesson->update(['content' => $this->generatedHtml]);
            $target = "lesson \"{$lesson->title}\"";

            app(\App\Services\ContentIndexer::class)->indexLesson($lesson);
        }

        Notification::make()->title("Applied to {$target}")->success()->send();
        $this->generatedHtml = null;
    }

    private function applyQuizQuestions(array $state): void
    {
        $course = Course::findOrFail($state['course_id']);
        $lesson = !empty($state['lesson_id']) ? Lesson::find($state['lesson_id']) : null;

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson?->id,
            'title' => $state['topic'],
            'is_required' => false,
            'pass_percentage' => 70,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        foreach ($this->generatedQuizQuestions as $i => $q) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'type' => 'multiple_choice',
                'question_text' => $q['question'],
                'points' => 1,
                'order_index' => $i,
            ]);

            foreach ($q['options'] as $optIndex => $optionText) {
                QuizQuestionOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $optIndex === (int) $q['correct_index'],
                    'order_index' => $optIndex,
                ]);
            }
        }

        Notification::make()
            ->title("Quiz \"{$quiz->title}\" created with " . count($this->generatedQuizQuestions) . ' question(s)')
            ->body('Review it under Content → Quizzes — it\'s inactive-by-default settings (not required, 70% pass mark) can be adjusted there.')
            ->success()
            ->send();

        $this->generatedQuizQuestions = null;
    }
}
