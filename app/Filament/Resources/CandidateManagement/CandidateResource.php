<?php

namespace App\Filament\Resources\CandidateManagement;

use App\Filament\Resources\CandidateManagement\CandidateResource\Pages;
use App\Filament\Resources\CandidateManagement\CandidateResource\RelationManagers;
use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\InterviewBatch;
use App\Mail\InterviewScheduledMail;
use Carbon\Carbon;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Textarea, FileUpload, Select, DatePicker, TimePicker, Section, Grid};
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, BulkAction, ActionGroup};
use Filament\Tables\Columns\{TextColumn, BadgeColumn, IconColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

use App\Traits\HasInterviewActions;

class CandidateResource extends Resource
{
    use HasInterviewActions;

    protected static ?string $model = Application::class;
    protected static ?string $navigationGroup = 'Candidate Management';
    protected static ?string $modelLabel = 'Candidate';
    protected static ?string $pluralModelLabel = 'Candidates';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Candidate Application Details')
                    ->description('Complete candidate personal details, academic background, and internship preferences')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('application_code')
                                ->label('Application Code')
                                ->prefixIcon('heroicon-m-hashtag')
                                ->placeholder('Auto-generated on save')
                                ->disabled()
                                ->dehydrated(false),

                            Select::make('status')
                                ->label('Application Status')
                                ->prefixIcon('heroicon-m-flag')
                                ->options([
                                    'applied' => 'Applied',
                                    'interview_scheduled' => 'Interview Scheduled',
                                    'interviewed' => 'Interviewed',
                                    'shortlisted' => 'Shortlisted',
                                    'rejected' => 'Rejected',
                                ])
                                ->default('applied')
                                ->required(),

                            TextInput::make('domain')
                                ->label('Field / Role Applied')
                                ->prefixIcon('heroicon-m-briefcase')
                                ->placeholder('e.g. Web Development, AI/ML, UI/UX')
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('name')
                                ->label('Full Name')
                                ->prefixIcon('heroicon-m-user')
                                ->required()
                                ->maxLength(255)
                                ->regex('/^(?=(?:.*?\s){1,5}(?![^\s]*\s))[a-zA-Z\s]+$/')
                                ->validationMessages([
                                    'regex' => 'The name must only contain letters and 1 to 5 spaces.',
                                ]),

                            TextInput::make('email')
                                ->email()
                                ->prefixIcon('heroicon-m-envelope')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->prefixIcon('heroicon-m-phone')
                                ->required()
                                ->maxLength(15),
                        ]),

                        Grid::make(4)->schema([
                            TextInput::make('college')
                                ->label('College / Institution')
                                ->prefixIcon('heroicon-m-academic-cap')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            TextInput::make('degree')
                                ->label('Degree / Branch')
                                ->prefixIcon('heroicon-m-bookmark')
                                ->placeholder('e.g. B.Tech Computer Engineering')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('year')
                                ->label('Year / Semester')
                                ->prefixIcon('heroicon-m-calendar')
                                ->placeholder('e.g. Sem 6 / 3rd Year')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('cgpa')
                                ->label('CGPA / Percentage')
                                ->prefixIcon('heroicon-m-star')
                                ->numeric()
                                ->step(0.01)
                                ->required()
                                ->maxValue(100),

                            TextInput::make('duration')
                                ->label('Duration')
                                ->prefixIcon('heroicon-m-clock')
                                ->numeric()
                                ->required(),

                            Select::make('duration_unit')
                                ->label('Duration Unit')
                                ->prefixIcon('heroicon-m-scale')
                                ->options([
                                    'months' => 'Months',
                                    'days' => 'Days',
                                    'hours' => 'Hours',
                                ])
                                ->default('months')
                                ->required(),
                        ]),

                        Textarea::make('skills')
                            ->label('Skills & Technologies (comma separated)')
                            ->placeholder('e.g. PHP, Laravel, React, Tailwind CSS, MySQL')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('resume_path')
                            ->label('Resume / Curriculum Vitae (PDF)')
                            ->disk('public')
                            ->directory('resumes')
                            ->acceptedFileTypes(['application/pdf'])
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 3,
            ])
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->poll('5s')
            ->columns([
                Tables\Columns\Layout\View::make('filament.candidate-management.candidate-card')
                    ->components([
                        TextColumn::make('application_code')->searchable(),
                        TextColumn::make('name')->searchable(),
                        TextColumn::make('email')->searchable(),
                        TextColumn::make('phone')->searchable(),
                        TextColumn::make('college')->searchable(),
                        TextColumn::make('degree')->searchable(),
                        TextColumn::make('domain')->searchable(),
                    ]),
            ])

            ->filters([
                SelectFilter::make('archive_status')
                    ->label('Archival Status')
                    ->options([
                        'active' => 'Active Candidates',
                        'archived' => 'Archived Candidates',
                        'all' => 'All Candidates',
                    ])
                    ->default('active')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? 'active';
                        if ($value === 'active') {
                            return $query->where(function ($q) {
                                $q->where('is_archived', false)->orWhereNull('is_archived');
                            });
                        } elseif ($value === 'archived') {
                            return $query->where('is_archived', true);
                        }
                        return $query;
                    }),

                SelectFilter::make('cohort_archive_name')
                    ->label('Recruitment Cycle')
                    ->placeholder('All Cycles')
                    ->options(fn () => Application::whereNotNull('cohort_archive_name')
                        ->where('cohort_archive_name', '!=', '')
                        ->distinct()
                        ->pluck('cohort_archive_name', 'cohort_archive_name')
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->where('cohort_archive_name', $data['value']);
                        }
                        return $query;
                    }),

                SelectFilter::make('interview_batch_id')
                    ->label('Filter by Batch')
                    ->placeholder('All Batches')
                    ->options(fn () => InterviewBatch::orderBy('interview_date', 'desc')
                        ->get()
                        ->mapWithKeys(fn ($b) => [
                            $b->id => $b->interview_batch_name . ($b->interview_date ? ' (' . \Carbon\Carbon::parse($b->interview_date)->format('d M') . ($b->start_time ? ' ' . \Carbon\Carbon::parse($b->start_time)->format('h:i A') : '') . ')' : '')
                        ])
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('interviewAssignments', function (Builder $q) use ($data) {
                            $q->where('interview_batch_id', $data['value']);
                        });
                    }),

                SelectFilter::make('status')
                    ->options([
                        'applied' => 'Applied',
                        'interview_scheduled' => 'Interview Scheduled',
                        'interviewed' => 'Interviewed',
                        'shortlisted' => 'Shortlisted',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('domain')
                    ->label('Field / Domain')
                    ->options(fn () => Application::whereNotIn('status', ['pending', 'verified'])
                        ->whereNotNull('domain')
                        ->distinct()
                        ->pluck('domain', 'domain')
                        ->toArray()
                    ),
            ])

            ->actions([
                // Schedule Interview Action (Visible when Applied)
                static::getScheduleInterviewAction()
                    ->button()
                    ->size('sm')
                    ->visible(fn ($record) => $record->status === 'applied'),

                // Evaluate Candidate Action (Visible when Scheduled)
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->button()
                    ->size('sm')
                    ->visible(fn ($record) => $record->status === 'interview_scheduled' && $record->interviewAssignments()->exists())
                    ->modalHeading('Evaluate Interview')
                    ->fillForm(function ($record) {
                        $assignment = $record->interviewAssignments()->latest()->first();
                        return [
                            'attendance' => $assignment?->attendance,
                            'problem_solving' => $assignment?->problem_solving,
                            'communication' => $assignment?->communication,
                            'overall_score' => $assignment?->overall_score,
                            'remarks' => $assignment?->remarks,
                        ];
                    })
                    ->form([
                        Forms\Components\Select::make('attendance')
                            ->label('Attendance')
                            ->options([
                                'present' => '✅ Present',
                                'absent' => '❌ Absent',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('problem_solving')
                                ->label('Technical Skills (Max 25)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(25)
                                ->disabled(fn (Forms\Get $get) => $get('attendance') !== 'present')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $ps = ($state !== null && $state !== '') ? (float) $state : null;
                                    $comm = ($get('communication') !== null && $get('communication') !== '') ? (float) $get('communication') : null;
                                    $set('overall_score', ($ps !== null || $comm !== null) ? (($ps ?? 0) + ($comm ?? 0)) : null);
                                }),

                            Forms\Components\TextInput::make('communication')
                                ->label('Communication (Max 25)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(25)
                                ->disabled(fn (Forms\Get $get) => $get('attendance') !== 'present')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $comm = ($state !== null && $state !== '') ? (float) $state : null;
                                    $ps = ($get('problem_solving') !== null && $get('problem_solving') !== '') ? (float) $get('problem_solving') : null;
                                    $set('overall_score', ($ps !== null || $comm !== null) ? (($ps ?? 0) + ($comm ?? 0)) : null);
                                }),
                        ]),

                        Forms\Components\TextInput::make('overall_score')
                            ->label('Total Score (Auto-calculated)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('remarks')
                            ->label('Remarks / Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $assignment = $record->interviewAssignments()->latest()->first();
                        if (!$assignment) return;

                        $updateData = [
                            'attendance' => $data['attendance'],
                            'remarks' => $data['remarks'] ?? null,
                        ];

                        $hasProblemSolving = isset($data['problem_solving']) && $data['problem_solving'] !== '' && $data['problem_solving'] !== null;
                        $hasCommunication = isset($data['communication']) && $data['communication'] !== '' && $data['communication'] !== null;

                        if ($data['attendance'] === 'present') {
                            $updateData['problem_solving'] = $hasProblemSolving ? (float) $data['problem_solving'] : null;
                            $updateData['communication'] = $hasCommunication ? (float) $data['communication'] : null;
                            $updateData['overall_score'] = ($hasProblemSolving || $hasCommunication)
                                ? ((float)($updateData['problem_solving'] ?? 0)) + ((float)($updateData['communication'] ?? 0))
                                : null;
                        } else {
                            $updateData['problem_solving'] = null;
                            $updateData['communication'] = null;
                            $updateData['overall_score'] = null;
                        }

                        $assignment->update($updateData);

                        // Only mark as interviewed when BOTH marks are given AND candidate is present!
                        if ($data['attendance'] === 'present' && $hasProblemSolving && $hasCommunication) {
                            if (!in_array($record->status, ['shortlisted', 'rejected'])) {
                                $record->update(['status' => 'interviewed']);
                            }
                        }

                        Notification::make()
                            ->title('Evaluation Saved Successfully')
                            ->success()
                            ->send();
                    }),

                // Reschedule Interview Action (Visible when Candidate is Scheduled)
                Tables\Actions\Action::make('rescheduleInterview')
                    ->label('Reschedule')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->button()
                    ->size('sm')
                    ->visible(fn ($record) => $record->status === 'interview_scheduled' && $record->interviewAssignments()->exists())
                    ->modalHeading('Reschedule Interview')
                    ->modalDescription('Select a new interview batch for this candidate. A new schedule invitation email will be sent.')
                    ->form([
                        Forms\Components\Select::make('interview_batch_id')
                            ->label('Select New Interview Batch')
                            ->options(fn () => InterviewBatch::where('capacity_status', 'open')
                                ->pluck('interview_batch_name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('reschedule_reason')
                            ->label('Reason for Rescheduling (Optional)')
                            ->placeholder('e.g. Candidate requested date change / Candidate was absent')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $batch = InterviewBatch::find($data['interview_batch_id']);
                        if (!$batch) return;

                        $remainingSlots = $batch->batch_size - $batch->assignments()->count();
                        if ($remainingSlots <= 0) {
                            Notification::make()->title('Selected batch is already full')->danger()->send();
                            return;
                        }

                        $latestAssignment = $record->interviewAssignments()->latest()->first();
                        $oldBatch = $latestAssignment?->batch;

                        if ($latestAssignment) {
                            $latestAssignment->update([
                                'interview_batch_id' => $batch->id,
                                'attendance' => null,
                                'problem_solving' => null,
                                'communication' => null,
                                'overall_score' => null,
                                'remarks' => !empty($data['reschedule_reason'])
                                    ? ($latestAssignment->remarks ? $latestAssignment->remarks . ' | Rescheduled: ' . $data['reschedule_reason'] : 'Rescheduled: ' . $data['reschedule_reason'])
                                    : $latestAssignment->remarks,
                            ]);
                        } else {
                            InterviewAssignment::create([
                                'application_id' => $record->id,
                                'interview_batch_id' => $batch->id,
                            ]);
                        }

                        // Check if old batch was full and can now be reopened
                        if ($oldBatch && $oldBatch->id !== $batch->id && $oldBatch->capacity_status === 'full') {
                            if ($oldBatch->assignments()->count() < $oldBatch->batch_size) {
                                $oldBatch->update(['capacity_status' => 'open']);
                            }
                        }

                        // Check if new batch is now full
                        if ($batch->assignments()->count() >= $batch->batch_size) {
                            $batch->update(['capacity_status' => 'full']);
                        }

                        $record->update(['status' => 'interview_scheduled']);

                        try {
                            Mail::to($record->email)->send(new InterviewScheduledMail($batch, $record, true));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Failed sending reschedule interview email: " . $e->getMessage());
                        }

                        Notification::make()
                            ->title('Interview Rescheduled Successfully')
                            ->body("Rescheduled to batch: {$batch->interview_batch_name}. New schedule email sent.")
                            ->success()
                            ->send();
                    }),

                // Select Action (Visible when candidate is Scheduled or Interviewed)
                Tables\Actions\Action::make('selectCandidate')
                    ->label('Select')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Select Candidate')
                    ->modalDescription('Are you sure you want to select/shortlist this candidate? A selection email will be sent.')
                    ->visible(fn ($record) => $record->status === 'interviewed')
                    ->action(function ($record) {
                        $record->update(['status' => 'shortlisted']);
                        $assignment = $record->interviewAssignments()->latest()->first();
                        if ($assignment) {
                            $assignment->update(['result' => 'selected']);
                        }
                        
                        try {
                            Mail::to($record->email)->send(new \App\Mail\CandidateSelectedMail($assignment ?? $record));
                            Notification::make()->title('Candidate Shortlisted & Email Sent')->success()->send();
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Failed sending candidate selected mail to {$record->email}: " . $e->getMessage());
                            Notification::make()
                                ->title('Shortlisted, but Email Failed')
                                ->body('Status updated. Error: ' . $e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),

                // Resend Selection Email Action (Visible when candidate is Shortlisted)
                Tables\Actions\Action::make('resendSelectionEmail')
                    ->label('Resend Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Resend Selection Email')
                    ->modalDescription(fn ($record) => "Resend congratulations & selection email to {$record->email}?")
                    ->visible(fn ($record) => $record->status === 'shortlisted')
                    ->action(function ($record) {
                        $assignment = $record->interviewAssignments()->latest()->first();
                        try {
                            Mail::to($record->email)->send(new \App\Mail\CandidateSelectedMail($assignment ?? $record));
                            Notification::make()->title('Selection Email Sent Successfully')->success()->send();
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Failed resending candidate selected mail to {$record->email}: " . $e->getMessage());
                            Notification::make()
                                ->title('Failed to Send Email')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Reject Action (Visible as primary button when candidate is Interviewed)
                Tables\Actions\Action::make('rejectCandidate')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Candidate')
                    ->modalDescription('Are you sure you want to reject this candidate? A rejection email will be sent.')
                    ->visible(fn ($record) => $record->status === 'interviewed')
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);
                        $assignment = $record->interviewAssignments()->latest()->first();
                        if ($assignment) {
                            $assignment->update(['result' => 'rejected']);
                        }
                        
                        try {
                            Mail::to($record->email)->send(new \App\Mail\CandidateRejectedMail($record));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error("Failed sending candidate rejected mail: " . $e->getMessage());
                        }

                        Notification::make()->title('Candidate Rejected & Email Sent')->danger()->send();
                    }),

                // Compact Dropdown for Secondary & Management Actions
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View Details')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit Candidate')
                        ->icon('heroicon-o-pencil-square'),

                    // Edit Marks Action (For evaluated candidates)
                    Tables\Actions\Action::make('editMarks')
                        ->label('Edit Interview Marks')
                        ->icon('heroicon-o-academic-cap')
                        ->color('gray')
                        ->visible(fn ($record) => in_array($record->status, ['interviewed', 'shortlisted', 'rejected']) && $record->interviewAssignments()->exists() && $record->interviewAssignments()->latest()->first()?->overall_score !== null)
                        ->modalHeading('Edit Interview Evaluation')
                        ->fillForm(function ($record) {
                            $assignment = $record->interviewAssignments()->latest()->first();
                            return [
                                'attendance' => $assignment?->attendance,
                                'problem_solving' => $assignment?->problem_solving,
                                'communication' => $assignment?->communication,
                                'overall_score' => $assignment?->overall_score,
                                'remarks' => $assignment?->remarks,
                            ];
                        })
                        ->form([
                            Forms\Components\Select::make('attendance')
                                ->label('Attendance')
                                ->options([
                                    'present' => '✅ Present',
                                    'absent' => '❌ Absent',
                                ])
                                ->required()
                                ->live(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('problem_solving')
                                    ->label('Technical Skills (Max 25)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(25)
                                    ->disabled(fn (Forms\Get $get) => $get('attendance') !== 'present')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $ps = ($state !== null && $state !== '') ? (float) $state : null;
                                        $comm = ($get('communication') !== null && $get('communication') !== '') ? (float) $get('communication') : null;
                                        $set('overall_score', ($ps !== null || $comm !== null) ? (($ps ?? 0) + ($comm ?? 0)) : null);
                                    }),

                                Forms\Components\TextInput::make('communication')
                                    ->label('Communication (Max 25)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(25)
                                    ->disabled(fn (Forms\Get $get) => $get('attendance') !== 'present')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $comm = ($state !== null && $state !== '') ? (float) $state : null;
                                        $ps = ($get('problem_solving') !== null && $get('problem_solving') !== '') ? (float) $get('problem_solving') : null;
                                        $set('overall_score', ($ps !== null || $comm !== null) ? (($ps ?? 0) + ($comm ?? 0)) : null);
                                    }),
                            ]),

                            Forms\Components\TextInput::make('overall_score')
                                ->label('Total Score (Auto-calculated)')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\Textarea::make('remarks')
                                ->label('Remarks / Notes')
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            $assignment = $record->interviewAssignments()->latest()->first();
                            if (!$assignment) return;

                            $updateData = [
                                'attendance' => $data['attendance'],
                                'remarks' => $data['remarks'] ?? null,
                            ];

                            $hasProblemSolving = isset($data['problem_solving']) && $data['problem_solving'] !== '' && $data['problem_solving'] !== null;
                            $hasCommunication = isset($data['communication']) && $data['communication'] !== '' && $data['communication'] !== null;

                            if ($data['attendance'] === 'present') {
                                $updateData['problem_solving'] = $hasProblemSolving ? (float) $data['problem_solving'] : null;
                                $updateData['communication'] = $hasCommunication ? (float) $data['communication'] : null;
                                $updateData['overall_score'] = ($hasProblemSolving || $hasCommunication)
                                    ? ((float)($updateData['problem_solving'] ?? 0)) + ((float)($updateData['communication'] ?? 0))
                                    : null;
                            } else {
                                $updateData['problem_solving'] = null;
                                $updateData['communication'] = null;
                                $updateData['overall_score'] = null;
                            }

                            $assignment->update($updateData);

                            // Only mark as interviewed when BOTH marks are given AND candidate is present!
                            if ($data['attendance'] === 'present' && $hasProblemSolving && $hasCommunication) {
                                if (!in_array($record->status, ['shortlisted', 'rejected'])) {
                                    $record->update(['status' => 'interviewed']);
                                }
                            }

                            Notification::make()
                                ->title('Evaluation Updated Successfully')
                                ->success()
                                ->send();
                        }),

                    // Direct Select for Scheduled candidates (bypassing evaluation if needed)
                    Tables\Actions\Action::make('directSelect')
                        ->label('Direct Select')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Direct Select Candidate')
                        ->modalDescription('Select this candidate directly without evaluation? A selection email will be sent.')
                        ->visible(fn ($record) => $record->status === 'interview_scheduled')
                        ->action(function ($record) {
                            $record->update(['status' => 'shortlisted']);
                            $assignment = $record->interviewAssignments()->latest()->first();
                            if ($assignment) {
                                $assignment->update(['result' => 'selected']);
                            }
                            try {
                                Mail::to($record->email)->send(new \App\Mail\CandidateSelectedMail($assignment ?? $record));
                                Notification::make()->title('Candidate Shortlisted & Email Sent')->success()->send();
                            } catch (\Throwable $e) {
                                Notification::make()->title('Shortlisted, but Email Failed')->body($e->getMessage())->warning()->send();
                            }
                        }),

                    // Quick Reject for Applied or Scheduled
                    Tables\Actions\Action::make('quickReject')
                        ->label('Reject Candidate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Candidate')
                        ->modalDescription('Are you sure you want to reject this candidate? A rejection email will be sent.')
                        ->visible(fn ($record) => in_array($record->status, ['applied', 'interview_scheduled']))
                        ->action(function ($record) {
                            $record->update(['status' => 'rejected']);
                            $assignment = $record->interviewAssignments()->latest()->first();
                            if ($assignment) {
                                $assignment->update(['result' => 'rejected']);
                            }
                            try {
                                Mail::to($record->email)->send(new \App\Mail\CandidateRejectedMail($record));
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error("Failed sending candidate rejected mail: " . $e->getMessage());
                            }
                            Notification::make()->title('Candidate Rejected & Email Sent')->danger()->send();
                        }),

                    // Archive Single Candidate
                    Tables\Actions\Action::make('archiveCandidate')
                        ->label('Archive Candidate')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->visible(fn ($record) => !(bool) $record->is_archived)
                        ->modalHeading('Archive Candidate Application')
                        ->modalDescription('Archive this candidate into a recruitment cycle so active views stay clean.')
                        ->form([
                            Forms\Components\TextInput::make('cohort_archive_name')
                                ->label('Recruitment Cycle / Archive Name')
                                ->default(fn () => 'Recruitment Cycle ' . date('M Y'))
                                ->required(),
                            Forms\Components\Textarea::make('archive_note')
                                ->label('Archive Note (Optional)')
                                ->placeholder('e.g. End of 2026 hiring cycle')
                                ->rows(2),
                        ])
                        ->action(function ($record, array $data) {
                            $record->archive($data['cohort_archive_name'], $data['archive_note'] ?? null);
                            Notification::make()
                                ->title('Candidate Archived')
                                ->body("Moved to cycle: {$data['cohort_archive_name']}")
                                ->warning()
                                ->send();
                        }),

                    // Restore Single Candidate
                    Tables\Actions\Action::make('restoreCandidate')
                        ->label('Restore to Active')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->color('success')
                        ->visible(fn ($record) => (bool) $record->is_archived)
                        ->requiresConfirmation()
                        ->modalHeading('Restore Candidate')
                        ->modalDescription('Restore this candidate application back to the active pipeline?')
                        ->action(function ($record) {
                            $record->unarchive();
                            Notification::make()
                                ->title('Candidate Restored to Active')
                                ->success()
                                ->send();
                        }),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button()
                ->size('sm'),
            ])

            ->bulkActions([
                static::getScheduleInterviewBulkAction(),
                BulkAction::make('bulkShortlist')
                    ->label('Bulk Select')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Shortlisted')
                    ->modalDescription('Are you sure you want to shortlist the selected candidates directly? They will receive a selection email.')
                    ->action(function (Collection $records) {
                        $sentCount = 0;
                        $total = $records->count();
                        $records->each(function ($record, $index) use (&$sentCount, $total) {
                            $record->update(['status' => 'shortlisted']);
                            $assignment = $record->interviewAssignments()->latest()->first();
                            if ($assignment) {
                                $assignment->update(['result' => 'selected']);
                            }
                            try {
                                // Add delay between emails to prevent Gmail SMTP throttling
                                if ($index > 0) {
                                    sleep(2);
                                }
                                Mail::to($record->email)->send(new \App\Mail\CandidateSelectedMail($assignment ?? $record));
                                $sentCount++;
                                \Illuminate\Support\Facades\Log::info("Bulk shortlist email sent to {$record->email} ({$sentCount}/{$total})");
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error("Bulk shortlist email failed for {$record->email}: " . $e->getMessage());
                            }
                        });
                        Notification::make()
                            ->title("{$records->count()} Candidates Shortlisted")
                            ->body("{$sentCount} selection email(s) sent successfully.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('bulkReject')
                    ->label('Bulk Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Rejected')
                    ->modalDescription('Are you sure you want to reject the selected candidates? They will receive a rejection email.')
                    ->action(function (Collection $records) {
                        $sentCount = 0;
                        $total = $records->count();
                        $records->each(function ($record, $index) use (&$sentCount, $total) {
                            $record->update(['status' => 'rejected']);
                            $assignment = $record->interviewAssignments()->latest()->first();
                            if ($assignment) {
                                $assignment->update(['result' => 'rejected']);
                            }
                            try {
                                // Add delay between emails to prevent Gmail SMTP throttling
                                if ($index > 0) {
                                    sleep(2);
                                }
                                Mail::to($record->email)->send(new \App\Mail\CandidateRejectedMail($record));
                                $sentCount++;
                                \Illuminate\Support\Facades\Log::info("Bulk reject email sent to {$record->email} ({$sentCount}/{$total})");
                            } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error("Bulk reject email failed for {$record->email}: " . $e->getMessage());
                            }
                        });
                        Notification::make()
                            ->title("{$records->count()} Candidates Rejected")
                            ->body("{$sentCount} rejection email(s) sent.")
                            ->danger()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('bulkArchive')
                    ->label('Archive Selected')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->modalHeading('Archive Selected Candidates')
                    ->modalDescription('Move selected candidate applications into an archive cycle so they do not clutter active hiring.')
                    ->form([
                        TextInput::make('cohort_archive_name')
                            ->label('Recruitment Cycle / Cohort Name')
                            ->default(fn () => 'Recruitment Cycle ' . date('M Y'))
                            ->required(),
                        Textarea::make('archive_note')
                            ->label('Archive Note (Optional)')
                            ->placeholder('e.g. Winter 2026 Recruitment Drive')
                            ->rows(2),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $cycleName = trim($data['cohort_archive_name']);
                        $note = $data['archive_note'] ?? null;
                        $count = $records->count();

                        $records->each(function ($record) use ($cycleName, $note) {
                            $record->archive($cycleName, $note);
                        });

                        Notification::make()
                            ->title("{$count} Candidates Archived")
                            ->body("Archived under cycle: {$cycleName}")
                            ->warning()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('bulkRestore')
                    ->label('Restore to Active')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Selected Candidates')
                    ->modalDescription('Are you sure you want to restore all selected candidates back to active status?')
                    ->action(function (Collection $records) {
                        $count = $records->count();
                        $records->each(function ($record) {
                            $record->unarchive();
                        });

                        Notification::make()
                            ->title("{$count} Candidates Restored to Active")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'view' => Pages\ViewCandidate::route('/{record}'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotIn('status', ['pending', 'verified'])
            ->with(['interviewAssignments.batch']);
    }

    public static function getInterviewMarksHtml($record): ?\Illuminate\Support\HtmlString
    {
        if (!in_array($record->status, ['interview_scheduled', 'interviewed', 'shortlisted', 'rejected'])) {
            return null;
        }

        $assignment = $record->interviewAssignments->sortByDesc('id')->first();
        if (!$assignment) {
            if ($record->status === 'interview_scheduled') {
                return new \Illuminate\Support\HtmlString('
                    <div class="mt-1 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-white/5 p-2 text-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Interview scheduled &bull; Pending evaluation
                        </span>
                    </div>
                ');
            }
            return null;
        }

        // If marked absent
        if ($assignment->attendance === 'absent') {
            return new \Illuminate\Support\HtmlString('
                <div class="fi-ta-marks-card">
                    <div class="fi-ta-marks-header" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
                        <span class="fi-ta-marks-title">
                            <svg style="width: 0.95rem; height: 0.95rem; display: inline-block; vertical-align: -2px; margin-right: 0.25rem;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Attendance
                        </span>
                        <span class="fi-ta-badge-absent">
                            Absent
                        </span>
                    </div>
                </div>
            ');
        }

        $ps = $assignment->problem_solving;
        $comm = $assignment->communication;
        $total = $assignment->overall_score;

        $hasPs = !is_null($ps) && $ps !== '';
        $hasComm = !is_null($comm) && $comm !== '';

        $psFormatted = $hasPs ? (float) $ps : null;
        $commFormatted = $hasComm ? (float) $comm : null;

        if ($hasPs) {
            $psHtml = '<span class="fi-ta-marks-box-val">' . $psFormatted . '</span><span style="font-size: 0.72rem; opacity: 0.6; margin-left: 0.25rem; font-weight: normal;">/ 25</span>';
        } else {
            $psHtml = '<span class="fi-ta-marks-pending">Pending</span>';
        }

        if ($hasComm) {
            $commHtml = '<span class="fi-ta-marks-box-val">' . $commFormatted . '</span><span style="font-size: 0.72rem; opacity: 0.6; margin-left: 0.25rem; font-weight: normal;">/ 25</span>';
        } else {
            $commHtml = '<span class="fi-ta-marks-pending">Pending</span>';
        }

        if ($hasPs && $hasComm) {
            $computedTotal = (float)($total ?? ($psFormatted + $commFormatted));
            $totalBadge = '<span class="fi-ta-badge-evaluated">' . $computedTotal . ' / 50</span>';
        } elseif ($hasPs || $hasComm) {
            $partialTotal = (float)($total ?? (($hasPs ? $psFormatted : 0) + ($hasComm ? $commFormatted : 0)));
            $totalBadge = '<span class="fi-ta-badge-partial">' . $partialTotal . ' / 50 <span style="font-size: 0.65rem; font-weight: normal; opacity: 0.85; margin-left: 0.15rem;">(Partial)</span></span>';
        } else {
            $totalBadge = '<span class="fi-ta-badge-notgraded">Not Graded</span>';
        }

        return new \Illuminate\Support\HtmlString('
            <div class="fi-ta-marks-card">
                <div class="fi-ta-marks-header">
                    <span class="fi-ta-marks-title">
                        <svg style="width: 0.95rem; height: 0.95rem; display: inline-block; vertical-align: -2px; margin-right: 0.25rem; color: #f59e0b;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Interview Marks
                    </span>
                    <div>' . $totalBadge . '</div>
                </div>
                <div class="fi-ta-marks-grid">
                    <div class="fi-ta-marks-box">
                        <span class="fi-ta-marks-box-label">Technical</span>
                        <div style="display: flex; align-items: baseline; margin-top: 0.2rem;">' . $psHtml . '</div>
                    </div>
                    <div class="fi-ta-marks-box">
                        <span class="fi-ta-marks-box-label">Communication</span>
                        <div style="display: flex; align-items: baseline; margin-top: 0.2rem;">' . $commHtml . '</div>
                    </div>
                </div>
            </div>
        ');
    }
}
