<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    private static function isAdmin(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('courses.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('courses.create');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('courses.edit');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('courses.delete');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return self::isAdmin() ? $query : $query->where('instructor_id', auth()->id());
    }

    /** Shared by the create and edit pages. */
    public static function prepare(array $data, ?Course $record = null): array
    {
        if (! self::isAdmin() || empty($data['instructor_id'])) {
            $data['instructor_id'] = $record?->instructor_id ?? auth()->id();
        }

        // Defense in depth: even if a non-admin somehow submits status=published
        // (tampered request, future bug in the form), never let it through.
        // Admins publish only via the explicit "Publish" table action below.
        if (! self::isAdmin() && ($data['status'] ?? null) === 'published') {
            $data['status'] = $record?->status === 'published' ? 'published' : 'pending_review';
        }

        if (($data['status'] ?? null) === 'published' && ! $record?->published_at) {
            $data['published_at'] = now();
        }
        if (($data['pricing_type'] ?? null) === 'free') {
            $data['price_cents'] = 0;
        }

        return $data;
    }

    public static function form(Form $form): Form
    {
        $notAdmin = fn () => ! self::isAdmin();

        return $form->schema([
            Tabs::make('Course')->columnSpanFull()->tabs([

                Tabs\Tab::make('Details')->columns(2)->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(200)->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set, ?Course $record) => $record ? null : $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                    Forms\Components\Select::make('instructor_id')->relationship('instructor', 'name')->searchable()->preload()
                        ->visible(fn () => self::isAdmin()),
                    Forms\Components\Select::make('category_id')->relationship('category', 'name')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('slug')->required()->unique('categories', 'slug'),
                        ]),
                    Forms\Components\Select::make('level')->required()->default('beginner')
                        ->options(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']),

                    Forms\Components\Select::make('status')->required()->default('draft')
                        ->options(fn () => self::isAdmin()
                            ? [
                                'draft' => 'Draft',
                                'pending_review' => 'Pending review',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ]
                            : [
                                'draft' => 'Draft',
                                'pending_review' => 'Submit for review',
                            ])
                        ->helperText(fn () => self::isAdmin()
                            ? null
                            : 'Choose "Submit for review" when ready — an admin will review and publish it.'),

                    Forms\Components\Textarea::make('summary')->rows(2)->columnSpanFull(),
                    Forms\Components\RichEditor::make('description')->columnSpanFull(),
                    // Forms\Components\FileUpload::make('thumbnail')->image()->directory('thumbnails')->imageEditor(),
                    Forms\Components\FileUpload::make('thumbnail')
                            ->image()
                            ->directory('thumbnails')
                            ->imageEditor()
                            ->saveUploadedFileUsing(fn ($file) => \App\Models\Media::storeUpload($file, 'thumbnails')->path)
                            ->hintAction(\App\Filament\Support\MediaLibraryAction::make('thumbnail')),
                    Forms\Components\Select::make('intro_video_provider')->label('Intro video provider')
                        ->options(['youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'bunny' => 'Bunny Stream', 's3' => 'Uploaded file (S3 path)'])
                        ->helperText('Shown on the public course page before someone enrolls. Keep it short and unlocked.'),
                    Forms\Components\TextInput::make('intro_video_ref')->label('Intro video ID or path'),
                ]),

                Tabs\Tab::make('Pricing and access')->columns(2)->schema([
                    Forms\Components\Select::make('pricing_type')->required()->default('free')->live()
                        ->options(['free' => 'Free', 'paid' => 'Paid'])->disabled($notAdmin),
                    Forms\Components\Select::make('currency')->default('GBP')
                        ->options(['GBP' => 'GBP', 'USD' => 'USD', 'EUR' => 'EUR'])
                        ->visible(fn (Get $get) => $get('pricing_type') === 'paid')->disabled($notAdmin),
                    Forms\Components\TextInput::make('price_cents')->label('Price')->numeric()->minValue(0)->step(0.01)
                        ->formatStateUsing(fn ($state) => $state === null ? null : $state / 100)
                        ->dehydrateStateUsing(fn ($state) => (int) round(((float) $state) * 100))
                        ->visible(fn (Get $get) => $get('pricing_type') === 'paid')->disabled($notAdmin),
                    Forms\Components\TextInput::make('access_days')->label('Access length (days)')->numeric()->minValue(1)
                        ->helperText('Leave empty for lifetime access. Applies to new enrollments only.')
                        ->disabled($notAdmin),
                ]),

                Tabs\Tab::make('Curriculum')->schema([
                    Forms\Components\Repeater::make('sections')->relationship()
                        ->orderColumn('position')->reorderableWithButtons()->collapsible()
                        ->addActionLabel('Add section')
                        ->itemLabel(fn (array $state) => $state['title'] ?? 'New section')
                        ->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\Repeater::make('lessons')->relationship()
                                ->orderColumn('position')->reorderableWithButtons()->collapsible()->collapsed()
                                ->addActionLabel('Add lesson')
                                ->itemLabel(fn (array $state) => $state['title'] ?? 'New lesson')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
                                    Forms\Components\Select::make('type')->required()->default('video')->live()
                                        ->options(['video' => 'Video', 'text' => 'Text', 'file' => 'File']),
                                    Forms\Components\TextInput::make('duration_seconds')->label('Duration (seconds)')->numeric(),
                                    Forms\Components\Select::make('video_provider')
                                        ->options(['youtube' => 'YouTube', 'vimeo' => 'Vimeo', 'bunny' => 'Bunny Stream', 's3' => 'Uploaded file (S3 path)'])
                                        ->visible(fn (Get $get) => $get('type') === 'video'),
                                    Forms\Components\TextInput::make('video_ref')->label('Video ID or path')
                                        ->visible(fn (Get $get) => $get('type') === 'video'),
                                    Forms\Components\RichEditor::make('content')->columnSpanFull(),
                                    Forms\Components\FileUpload::make('attachment')->directory('attachments'),
                                    Forms\Components\Toggle::make('is_preview')->label('Free preview'),
                                ]),
                        ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')->square(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('instructor.name')->visible(fn () => self::isAdmin()),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'pending_review' => 'warning',
                        'draft' => 'gray',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending_review' => 'Pending review',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->getStateUsing(fn (Course $r) => $r->isFree() ? 'Free' : $r->currency.' '.number_format($r->price_cents / 100, 2)),
                Tables\Columns\TextColumn::make('access')
                    ->getStateUsing(fn (Course $r) => $r->access_days ? $r->access_days.' days' : 'Lifetime'),
                Tables\Columns\TextColumn::make('enrollments_count')->counts('enrollments')->label('Students')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'pending_review' => 'Pending review', 'published' => 'Published', 'archived' => 'Archived',
                ]),
                Tables\Filters\SelectFilter::make('pricing_type')->options(['free' => 'Free', 'paid' => 'Paid']),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Course $record) => auth()->user()->can('courses.publish') && $record->status === 'pending_review')
                    ->requiresConfirmation()
                    ->modalDescription('This makes the course live and visible to students.')
                    ->action(function (Course $record) {
                        $record->update([
                            'status' => 'published',
                            'published_at' => $record->published_at ?? now(),
                        ]);
                        Notification::make()->title('Course published')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Send back')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Course $record) => auth()->user()->can('courses.publish') && $record->status === 'pending_review')
                    ->requiresConfirmation()
                    ->modalDescription('Sends this course back to draft so the instructor can make changes.')
                    ->action(function (Course $record) {
                        $record->update(['status' => 'draft']);
                        Notification::make()->title('Sent back to draft')->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
