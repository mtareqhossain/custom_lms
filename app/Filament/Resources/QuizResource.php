<?php

namespace App\Filament\Resources;

use App\Enums\QuizQuestionType;
use App\Filament\Resources\QuizResource\Pages;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool { return auth()->user()->can('quizzes.manage'); }


    /**
     * Admins see every quiz. Instructors only see/manage quizzes that belong
     * to courses they own — same data-isolation pattern used for businesses
     * (organization_id scoping) and instructor dashboards elsewhere in the app.
     * Assumes `courses.instructor_id` exists, matching the column already
     * referenced by ProgressService's completion-notification logic
     * ($course->instructor).
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('instructor') && !$user->hasRole('admin')) {
            $query->whereHas('course', fn ($q) => $q->where('instructor_id', $user->id));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Quiz details')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('course_id')
                        ->label('Course')
                        ->options(fn () => self::courseOptions())
                        ->required()
                        ->searchable()
                        ->live(),

                    Forms\Components\Select::make('lesson_id')
                        ->label('Lesson')
                        ->helperText('Leave blank to make this a course-final quiz (final exam).')
                        ->options(fn (Forms\Get $get) => Lesson::query()
                            ->whereHas('section', fn ($q) => $q->where('course_id', $get('course_id')))
                            ->pluck('title', 'id'))
                        ->searchable(),

                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_required')
                        ->label('Required to complete the course')
                        ->helperText('If on, the student must pass this quiz before the course can be marked complete and a certificate issued.'),

                    Forms\Components\Toggle::make('is_active')
                        ->default(true),

                    Forms\Components\TextInput::make('pass_percentage')
                        ->label('Pass mark (%)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(70)
                        ->required(),

                    Forms\Components\TextInput::make('max_attempts')
                        ->label('Max attempts')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Leave blank for unlimited attempts.'),

                    Forms\Components\TextInput::make('time_limit_minutes')
                        ->label('Time limit (minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Leave blank for no time limit.'),
                ]),

            Forms\Components\Section::make('Questions')
                ->schema([
                    Forms\Components\Repeater::make('questions')
                        ->relationship()
                        ->orderColumn('order_index')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->options(QuizQuestionType::options())
                                ->required()
                                ->live(),

                            Forms\Components\Textarea::make('question_text')
                                ->label('Question')
                                ->required()
                                ->rows(2),

                            Forms\Components\TextInput::make('points')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),

                            Forms\Components\Repeater::make('options')
                                ->relationship()
                                ->orderColumn('order_index')
                                ->visible(fn (Forms\Get $get) => $get('type') !== QuizQuestionType::ShortText->value)
                                ->schema([
                                    Forms\Components\TextInput::make('option_text')
                                        ->label('Option')
                                        ->required(),
                                    Forms\Components\Toggle::make('is_correct')
                                        ->label('Correct answer'),
                                ])
                                ->columns(2)
                                ->defaultItems(2)
                                ->minItems(2)
                                ->helperText('For True/False, add exactly two options: "True" and "False".'),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['question_text'] ?? 'New question')
                        ->defaultItems(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->searchable(),
                Tables\Columns\TextColumn::make('lesson.title')->label('Lesson')->default('Final exam'),
                Tables\Columns\IconColumn::make('is_required')->boolean()->label('Required'),
                Tables\Columns\TextColumn::make('pass_percentage')->label('Pass %')->suffix('%'),
                Tables\Columns\TextColumn::make('questions_count')->counts('questions')->label('Questions'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_required'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
    Tables\Actions\EditAction::make(),
    Tables\Actions\DeleteAction::make(),
])
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\BulkAction::make('activate')
            ->label('Activate')
            ->icon('heroicon-o-check')
            ->color('success')
            ->action(fn ($records) => $records->each->update(['is_active' => true])),
        Tables\Actions\BulkAction::make('deactivate')
            ->label('Deactivate')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->action(fn ($records) => $records->each->update(['is_active' => false])),
        Tables\Actions\DeleteBulkAction::make(),
    ]),
]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }

    private static function courseOptions(): array
    {
        $user = auth()->user();

        $query = Course::query();
        if ($user && $user->hasRole('instructor') && !$user->hasRole('admin')) {
            $query->where('instructor_id', $user->id);
        }

        return $query->pluck('title', 'id')->all();
    }
}
