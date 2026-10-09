<?php

namespace App\Filament\Resources\OrganizationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('seat_limit')
                ->numeric()
                ->minValue(1)
                ->placeholder('Unlimited')
                ->helperText('Leave blank for unlimited seats on this course.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Course'),
                Tables\Columns\TextInputColumn::make('seat_limit')
                    ->label('Seat limit')
                    ->placeholder('Unlimited')
                    ->rules(['nullable', 'integer', 'min:1'])
                    ->getStateUsing(fn ($record) => $record->pivot->seat_limit)
                    ->updateStateUsing(function ($record, $state) {
                        $record->pivot->update(['seat_limit' => $state === '' ? null : $state]);
                    }),
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Seats used')
                    ->state(function ($record) {
                        return $record->enrollments()
                            ->where('organization_id', $this->getOwnerRecord()->id)
                            ->count();
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('seat_limit')
                            ->numeric()->minValue(1)->placeholder('Unlimited')
                            ->helperText('Leave blank for unlimited seats.'),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
