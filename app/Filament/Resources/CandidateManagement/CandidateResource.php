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
use Filament\Tables\Actions\{Action, BulkAction};
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

    // Candidates come from public application form — no manual creation
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Candidate Details')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('application_code')
                                ->label('Application Code')
                                ->placeholder('Auto-generated on save')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->maxLength(255)
                                ->regex('/^(?=(?:.*?\s){1,5}(?![^\s]*\s))[a-zA-Z\s]+$/')
                                ->validationMessages([
                                    'regex' => 'The name must only contain letters and 1 to 5 spaces.',
                                ]),

                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->required()
                                ->maxLength(15),

                            TextInput::make('college')
                                ->label('College Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('degree')
                                ->required()
                                ->maxLength(100),

                            TextInput::make('year')
                                ->label('Year / Semester')
                                ->placeholder('e.g. Sem 3, Sem 6')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('cgpa')
                                ->label('CGPA / Percentage')
                                ->numeric()
                                ->step(0.01)
                                ->required()
                                ->maxValue(100),

                            TextInput::make('domain')
                                ->label('Interested Internship Field')
                                ->required(),
                        ]),
                    ]),

                Section::make('Internship Duration')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextInput::make('duration')
                            ->label('Duration')
                            ->numeric()
                            ->required(),

                        Select::make('duration_unit')
                            ->label('Unit')
                            ->options([
                                'months' => 'Months',
                                'days' => 'Days',
                                'hours' => 'Hours',
                            ])
                            ->required(),
                    ]),

                Section::make('Skills')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Textarea::make('skills')
                            ->label('Skills (comma separated)')
                            ->rows(3)
                            ->required(),
                    ]),

                Section::make('Resume')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        FileUpload::make('resume_path')
                            ->label('Resume')
                            ->disk('public')
                            ->directory('resumes')
                            ->acceptedFileTypes(['application/pdf'])
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
                '2xl' => 3,
            ])
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->poll('5s')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // Row 1: Application Code & Status Badge (responsive wrap with safe icon padding)
                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('application_code')
                            ->weight('bold')
                            ->size('sm')
                            ->copyable()
                            ->color('primary')
                            ->searchable()
                            ->grow(false),

                        TextColumn::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'applied' => 'Applied',
                                'interview_scheduled' => 'Scheduled',
                                'interviewed' => 'Interviewed',
                                'shortlisted' => 'Shortlisted',
                                'rejected' => 'Rejected',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state) => match ($state) {
                                'applied' => 'gray',
                                'interview_scheduled' => 'info',
                                'interviewed' => 'warning',
                                'shortlisted' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->grow(false),
                    ])->extraAttributes([
                        'class' => 'fi-ta-card-header-split',
                    ]),

                    // Row 2: Candidate Name (responsive word wrapping)
                    TextColumn::make('name')
                        ->weight('bold')
                        ->size('lg')
                        ->searchable()
                        ->extraAttributes([
                            'class' => 'fi-ta-card-name',
                        ]),

                    // Row 3: Domain Badge (on its own line so long text never wraps)
                    TextColumn::make('domain')
                        ->badge()
                        ->color('info')
                        ->size('sm'),

                    // Row 4: Contact & Education with icons and ellipsis truncation
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->size('sm')
                            ->color('gray')
                            ->searchable()
                            ->extraAttributes([
                                'class' => 'fi-ta-card-truncate',
                            ]),

                        TextColumn::make('college')
                            ->icon('heroicon-m-academic-cap')
                            ->size('sm')
                            ->color('gray')
                            ->searchable()
                            ->extraAttributes([
                                'class' => 'fi-ta-card-truncate',
                            ]),
                    ])->space(1),

                    // Row 5: Interview Batch Badge (Visible for Scheduled / Interviewed candidates)
                    TextColumn::make('interview_batch')
                        ->html()
                        ->placeholder(null)
                        ->state(function ($record) {
                            if (!in_array($record->status, ['interview_scheduled', 'interviewed', 'shortlisted'])) {
                                return null;
                            }
                            $assignment = $record->interviewAssignments->sortByDesc('id')->first();
                            $batch = $assignment?->batch;
                            if (!$batch) return null;

                            $date = $batch->interview_date ? \Carbon\Carbon::parse($batch->interview_date)->format('d M') : '';
                            $time = $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : '';
                            $meta = trim("{$date} {$time}");

                            return new \Illuminate\Support\HtmlString('
                                <div class="fi-ta-card-batch-badge" title="Interview Batch: ' . e($batch->interview_batch_name) . '">
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    <span style="font-weight: 700;">' . e($batch->interview_batch_name) . '</span>' .
                                    ($meta ? '<span style="opacity: 0.75; font-size: 0.72rem; margin-left: 0.25rem;">(' . e($meta) . ')</span>' : '') . '
                                </div>
                            ');
                        }),

                    // Row 6: CGPA & Applied Date (responsive split)
                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('cgpa')
                            ->formatStateUsing(fn ($state) => "CGPA: {$state}")
                            ->size('sm')
                            ->weight('bold')
                            ->color('warning')
                            ->grow(false),

                        TextColumn::make('created_at')
                            ->date('d M, Y')
                            ->size('sm')
                            ->color('gray')
                            ->grow(false),
                    ])->extraAttributes([
                        'class' => 'fi-ta-card-meta-split',
                    ]),

                    // Row 7: Interview Evaluation Marks (Visible for scheduled & interviewed candidates)
                    TextColumn::make('interview_marks')
                        ->html()
                        ->placeholder(null)
                        ->extraAttributes([
                            'class' => 'fi-ta-marks-col w-full',
                        ])
                        ->state(fn ($record) => static::getInterviewMarksHtml($record)),
                ])->space(3),
            ])

            ->filters([
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
                    ->visible(fn ($record) => $record->status === 'applied'),

                // Evaluate Candidate Action (Visible when Scheduled)
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->button()
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
                    ->requiresConfirmation()
                    ->modalHeading('Select Candidate')
                    ->modalDescription('Are you sure you want to select/shortlist this candidate? A selection email will be sent.')
                    ->visible(fn ($record) => in_array($record->status, ['interview_scheduled', 'interviewed']))
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

                // Reject Action (Visible when candidate is Scheduled or Interviewed)
                Tables\Actions\Action::make('rejectCandidate')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Reject Candidate')
                    ->modalDescription('Are you sure you want to reject this candidate? A rejection email will be sent.')
                    ->visible(fn ($record) => in_array($record->status, ['interview_scheduled', 'interviewed']))
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

                // Edit Marks Action (For interviewed/shortlisted/rejected candidates)
                Tables\Actions\Action::make('editMarks')
                    ->label('Edit Marks')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->button()
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

                // Top-Right Corner Resume Icon Action
                Tables\Actions\Action::make('viewResume')
                    ->label('')
                    ->icon('heroicon-o-document-text')
                    ->iconButton()
                    ->color('info')
                    ->tooltip(fn ($record) => $record->resume_path ? 'View Resume (PDF)' : 'No resume uploaded')
                    ->url(fn ($record) => $record->resume_path ? asset('storage/' . $record->resume_path) : null, shouldOpenInNewTab: true)
                    ->disabled(fn ($record) => empty($record->resume_path))
                    ->extraAttributes([
                        'class' => 'fi-ta-card-resume-btn',
                    ]),

                // Top-Right Corner Edit Icon Action
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Edit Candidate Application')
                    ->extraAttributes([
                        'class' => 'fi-ta-card-edit-btn',
                    ]),
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
