<?php
namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Manual enrollment and access control: enroll anyone, extend or end access. */
class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function canViewAny(): bool { return auth()->user()->can('enrollments.manage'); }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->label('Learner')->relationship('user', 'name')
                ->searchable()->preload()->required()->disabledOn('edit'),
            Forms\Components\Select::make('course_id')->label('Course')->relationship('course', 'title')
                ->searchable()->preload()->required()->disabledOn('edit'),
            Forms\Components\Select::make('organization_id')->label('Organization')->relationship('organization', 'name')
                ->searchable()->preload()->helperText('Optional. Links this enrollment to a business account.'),
            Forms\Components\DateTimePicker::make('expires_at')->label('Access expires')
                ->helperText('New enrollments: leave empty to use the course default. On edit, empty means access never expires.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Learner')->searchable(),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('organization.name')->placeholder('Individual'),
                Tables\Columns\TextColumn::make('source')->badge(),
                Tables\Columns\TextColumn::make('progress_percent')->label('Progress')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('expires_at')->label('Expires')->date()->placeholder('Never')->sortable(),
                Tables\Columns\IconColumn::make('completed_at')->label('Done')->boolean()
                    ->getStateUsing(fn (Enrollment $r) => $r->completed_at !== null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_id')->label('Course')->relationship('course', 'title')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('organization_id')->label('Organization')->relationship('organization', 'name'),
                Tables\Filters\SelectFilter::make('source')->options([
                    'free' => 'Free', 'purchase' => 'Purchase', 'business' => 'Business', 'admin' => 'Admin',
                ]),
                Tables\Filters\TernaryFilter::make('completed')->label('Completed')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('completed_at'),
                        false: fn ($q) => $q->whereNull('completed_at'),
                    ),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()->label('Revoke')])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
