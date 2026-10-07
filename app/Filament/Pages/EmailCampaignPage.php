<?php

namespace App\Filament\Pages;

use App\Jobs\SendCampaignEmailJob;
use App\Models\Course;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Organization;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EmailCampaignPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Notifications';

    protected static ?string $navigationLabel = 'Email Campaigns';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.email-campaign-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'audience_type' => 'all_students',
        ]);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Campaign name')
                    ->required()
                    ->helperText('For your own reference — recipients never see this.'),

                Forms\Components\Select::make('email_template_id')
                    ->label('Email template')
                    ->options(EmailTemplate::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('audience_type')
                    ->label('Send to')
                    ->options([
                        'all_students' => 'All students',
                        'by_course' => 'Students enrolled in a specific course',
                        'by_organization' => 'Students belonging to a specific business/organization',
                        'individual' => 'Specific students (pick individually)',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(Course::pluck('title', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('audience_type') === 'by_course')
                    ->required(fn (Forms\Get $get) => $get('audience_type') === 'by_course')
                    ->searchable(),

                Forms\Components\Select::make('organization_id')
                    ->label('Organization')
                    ->options(Organization::pluck('name', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('audience_type') === 'by_organization')
                    ->required(fn (Forms\Get $get) => $get('audience_type') === 'by_organization')
                    ->searchable(),

                Forms\Components\Select::make('user_ids')
                    ->label('Students')
                    ->multiple()
                    ->options(User::role('student')->pluck('name', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('audience_type') === 'individual')
                    ->required(fn (Forms\Get $get) => $get('audience_type') === 'individual')
                    ->searchable(),
            ])
            ->statePath('data');
    }

    protected function resolveRecipients(array $state): \Illuminate\Support\Collection
    {
        return match ($state['audience_type']) {
            'all_students' => User::role('student')->where('is_active', true)->get(),
            'by_course' => User::role('student')
                ->whereHas('enrollments', fn ($q) => $q->where('course_id', $state['course_id']))
                ->get(),
            'by_organization' => User::role('student')
                ->where('organization_id', $state['organization_id'])
                ->get(),
            'individual' => User::whereIn('id', $state['user_ids'] ?? [])->get(),
            default => collect(),
        };
    }

    public function send(): void
    {
        $state = $this->form->getState();

        $recipients = $this->resolveRecipients($state);

        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('No recipients matched that audience')
                ->warning()
                ->send();
            return;
        }

        $campaign = EmailCampaign::create([
            'email_template_id' => $state['email_template_id'],
            'name' => $state['name'],
            'audience_type' => $state['audience_type'],
            'audience_filter' => [
                'course_id' => $state['course_id'] ?? null,
                'organization_id' => $state['organization_id'] ?? null,
                'user_ids' => $state['user_ids'] ?? null,
            ],
            'recipients_count' => $recipients->count(),
            'status' => 'sending',
            'created_by' => auth()->id(),
        ]);

        foreach ($recipients as $recipient) {
            SendCampaignEmailJob::dispatch(
                $campaign->id,
                $recipient->email,
                $recipient->name,
                ['student_name' => $recipient->name, 'app_name' => config('app.name')]
            );
        }

        Notification::make()
            ->title("Campaign queued for {$recipients->count()} recipient(s)")
            ->success()
            ->send();

        $this->form->fill(['audience_type' => 'all_students']);
    }
}
