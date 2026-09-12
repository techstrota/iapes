<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\Pages;

use App\Filament\Resources\CandidateManagement\CandidateResource;
use App\Models\InterviewManagement\InterviewBatch;
use App\Models\InterviewManagement\InterviewAssignment;
use App\Mail\InterviewScheduledMail;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class ViewCandidate extends ViewRecord
{
    protected static string $resource = CandidateResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ── Status Banner (Full Width) ──
                Components\Section::make()
                    ->schema([
                        Components\Grid::make(3)->schema([
                            Components\TextEntry::make('application_code')
                                ->label('Application Code')
                                ->weight('bold')
                                ->size('lg')
                                ->copyable(),

                            Components\TextEntry::make('status')
                                ->label('Current Status')
                                ->badge()
                                ->formatStateUsing(fn (string $state) => match ($state) {
                                    'applied' => 'Applied',
                                    'interview_scheduled' => 'Interview Scheduled',
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
                                ->icon(fn (string $state) => match ($state) {
                                    'applied' => 'heroicon-m-inbox',
                                    'interview_scheduled' => 'heroicon-m-calendar',
                                    'interviewed' => 'heroicon-m-check-badge',
                                    'shortlisted' => 'heroicon-m-star',
                                    'rejected' => 'heroicon-m-x-circle',
                                    default => null,
                                }),

                            Components\TextEntry::make('created_at')
                                ->label('Applied On')
                                ->date('d M, Y  h:i A'),
                        ]),
                    ]),

                // ── Main Content Grid (60 / 40 Split) ──
                Components\Grid::make(3)
                    ->schema([
                        // LEFT COLUMN (2/3 width)
                        Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                // Personal Information (Includes Resume)
                                Components\Section::make('Personal Information')
                                    ->icon('heroicon-o-user')
                                    ->columns(3)
                                    ->schema([
                                        Components\TextEntry::make('name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->size('lg'),

                                        Components\TextEntry::make('email')
                                            ->label('Email Address')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable(),

                                        Components\TextEntry::make('phone')
                                            ->label('Phone Number')
                                            ->icon('heroicon-m-phone')
                                            ->copyable(),
                                            
                                        Components\TextEntry::make('college')
                                            ->label('College'),

                                        Components\TextEntry::make('degree')
                                            ->label('Degree'),

                                        Components\TextEntry::make('year')
                                            ->label('Year / Semester'),

                                        Components\TextEntry::make('cgpa')
                                            ->label('CGPA / Percentage')
                                            ->weight('bold'),

                                        Components\TextEntry::make('resume_path')
                                            ->label('Resume Document')
                                            ->formatStateUsing(fn ($state) => $state ? '📄 View Uploaded Resume' : 'No resume uploaded')
                                            ->url(fn ($record) => $record->resume_path
                                                ? asset('storage/' . $record->resume_path)
                                                : null
                                            )
                                            ->openUrlInNewTab()
                                            ->color(fn ($record) => $record->resume_path ? 'primary' : 'danger')
                                            ->weight('bold')
                                            ->columnSpan(2),
                                    ]),

                                // Internship Details & Skills
                                Components\Section::make('Internship & Skills')
                                    ->icon('heroicon-o-briefcase')
                                    ->columns(3)
                                    ->schema([
                                        Components\TextEntry::make('domain')
                                            ->label('Interested Field')
                                            ->badge()
                                            ->color('info'),

                                        Components\TextEntry::make('duration')
                                            ->label('Duration')
                                            ->formatStateUsing(fn ($state, $record) =>
                                                $state . ' ' . ucfirst($record->duration_unit ?? 'months')
                                            ),

                                        Components\TextEntry::make('skills')
                                            ->label('Skills')
                                            ->badge()
                                            ->separator(',')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // RIGHT COLUMN (1/3 width)
                        Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                // Timeline
                                Components\Section::make('Status Timeline')
                                    ->icon('heroicon-o-clock')
                                    ->schema([
                                        Components\View::make('filament.infolists.components.status-timeline')
                                    ]),

                                // Detailed Latest Interview & Evaluation Card
                                Components\Section::make('Latest Interview & Evaluation')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->visible(fn ($record) => $record->interviewAssignments()->exists())
                                    ->columns(2)
                                    ->schema([
                                        Components\TextEntry::make('latest_assignment_batch')
                                            ->label('Batch Name')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first()->batch)->interview_batch_name ?? 'N/A')
                                            ->weight('bold')
                                            ->columnSpan(2),

                                        Components\TextEntry::make('latest_assignment_date')
                                            ->label('Interview Date')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first()->batch)->interview_date 
                                                ? \Carbon\Carbon::parse(optional($record->interviewAssignments()->latest()->first()->batch)->interview_date)->format('d M, Y')
                                                : 'N/A'
                                            ),

                                        Components\TextEntry::make('latest_assignment_location')
                                            ->label('Location')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first()->batch)->interview_location ?? 'N/A'),

                                        Components\TextEntry::make('latest_attendance')
                                            ->label('Attendance')
                                            ->state(fn ($record) => ucfirst(optional($record->interviewAssignments()->latest()->first())->attendance ?? 'Pending'))
                                            ->badge()
                                            ->color(fn ($state) => match(strtolower($state)) {
                                                'present' => 'success',
                                                'absent' => 'danger',
                                                default => 'gray',
                                            }),

                                        Components\TextEntry::make('latest_result')
                                            ->label('Result')
                                            ->state(fn ($record) => ucfirst(optional($record->interviewAssignments()->latest()->first())->result ?? 'Pending'))
                                            ->badge()
                                            ->color(fn ($state) => match(strtolower($state)) {
                                                'selected' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning',
                                            }),

                                        Components\TextEntry::make('latest_problem_solving')
                                            ->label('Problem Solving')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first())->problem_solving !== null
                                                ? optional($record->interviewAssignments()->latest()->first())->problem_solving . ' / 25'
                                                : '-'
                                            ),

                                        Components\TextEntry::make('latest_communication')
                                            ->label('Communication')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first())->communication !== null
                                                ? optional($record->interviewAssignments()->latest()->first())->communication . ' / 25'
                                                : '-'
                                            ),

                                        Components\TextEntry::make('latest_total_score')
                                            ->label('Total Score')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first())->overall_score !== null
                                                ? optional($record->interviewAssignments()->latest()->first())->overall_score . ' / 50'
                                                : 'Not Evaluated'
                                            )
                                            ->weight('bold')
                                            ->color(fn ($state) => str_contains($state, '/') ? 'success' : 'gray')
                                            ->columnSpan(2),

                                        Components\TextEntry::make('latest_remarks')
                                            ->label('Evaluator Remarks')
                                            ->state(fn ($record) => optional($record->interviewAssignments()->latest()->first())->remarks ?? 'No remarks added')
                                            ->columnSpan(2)
                                            ->color('gray'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('scheduleInterview')
                ->label('Schedule Interview')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['applied']))
                ->form([
                    Forms\Components\Select::make('interview_batch_id')
                        ->label('Select Interview Batch')
                        ->options(
                            InterviewBatch::where('capacity_status', 'open')
                                ->pluck('interview_batch_name', 'id')
                        )
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $batch = InterviewBatch::find($data['interview_batch_id']);
                    if (!$batch) return;

                    $remainingSlots = $batch->batch_size - $batch->assignments()->count();
                    if ($remainingSlots <= 0) {
                        Notification::make()->title('Batch is already FULL')->danger()->send();
                        return;
                    }

                    $exists = InterviewAssignment::where('application_id', $this->record->id)
                        ->where('interview_batch_id', $batch->id)
                        ->exists();

                    if ($exists) {
                        Notification::make()->title('Already scheduled in this batch')->warning()->send();
                        return;
                    }

                    InterviewAssignment::create([
                        'application_id' => $this->record->id,
                        'interview_batch_id' => $batch->id,
                    ]);

                    $this->record->update(['status' => 'interview_scheduled']);
                    Mail::to($this->record->email)->send(new InterviewScheduledMail($batch, $this->record));

                    if ($batch->assignments()->count() >= $batch->batch_size) {
                        $batch->update(['capacity_status' => 'full']);
                    }

                    Notification::make()
                        ->title('Interview Scheduled Successfully')
                        ->body("Scheduled for batch: {$batch->interview_batch_name}")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('evaluate')
                ->label(fn () => $this->record->interviewAssignments()->latest()->first()?->overall_score !== null ? 'Edit Evaluation' : 'Evaluate Candidate')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->visible(fn () => $this->record->interviewAssignments()->exists())
                ->modalHeading(fn () => $this->record->interviewAssignments()->latest()->first()?->overall_score !== null ? 'Edit Interview Evaluation' : 'Evaluate Interview')
                ->fillForm(function () {
                    $assignment = $this->record->interviewAssignments()->latest()->first();
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
                ->action(function (array $data) {
                    $assignment = $this->record->interviewAssignments()->latest()->first();
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
                        if (!in_array($this->record->status, ['shortlisted', 'rejected'])) {
                            $this->record->update(['status' => 'interviewed']);
                        }
                    }

                    Notification::make()
                        ->title('Evaluation Saved Successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('rescheduleInterview')
                ->label('Reschedule Interview')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => in_array($this->record->status, ['interview_scheduled']) && $this->record->interviewAssignments()->exists())
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
                        ->placeholder('e.g. Candidate was absent / requested date change')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $batch = InterviewBatch::find($data['interview_batch_id']);
                    if (!$batch) return;

                    $remainingSlots = $batch->batch_size - $batch->assignments()->count();
                    if ($remainingSlots <= 0) {
                        Notification::make()->title('Selected batch is already full')->danger()->send();
                        return;
                    }

                    $latestAssignment = $this->record->interviewAssignments()->latest()->first();
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
                    }

                    if ($oldBatch && $oldBatch->id !== $batch->id && $oldBatch->capacity_status === 'full') {
                        if ($oldBatch->assignments()->count() < $oldBatch->batch_size) {
                            $oldBatch->update(['capacity_status' => 'open']);
                        }
                    }

                    if ($batch->assignments()->count() >= $batch->batch_size) {
                        $batch->update(['capacity_status' => 'full']);
                    }

                    $this->record->update(['status' => 'interview_scheduled']);

                    try {
                        Mail::to($this->record->email)->send(new InterviewScheduledMail($batch, $this->record, true));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed sending reschedule interview email in View: " . $e->getMessage());
                    }

                    Notification::make()
                        ->title('Interview Rescheduled Successfully')
                        ->body("Rescheduled to batch {$batch->interview_batch_name}. New schedule email sent.")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('selectCandidate')
                ->label('Select Candidate')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Select Candidate')
                ->modalDescription('Are you sure you want to select/shortlist this candidate? A selection email will be sent.')
                ->visible(fn () => in_array($this->record->status, ['interview_scheduled', 'interviewed']))
                ->action(function () {
                    $this->record->update(['status' => 'shortlisted']);
                    $assignment = $this->record->interviewAssignments()->latest()->first();
                    if ($assignment) {
                        $assignment->update(['result' => 'selected']);
                    }
                    
                    try {
                        Mail::to($this->record->email)->send(new \App\Mail\CandidateSelectedMail($assignment ?? $this->record));
                        Notification::make()->title('Candidate Shortlisted & Email Sent')->success()->send();
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed sending candidate selected mail to {$this->record->email}: " . $e->getMessage());
                        Notification::make()
                            ->title('Shortlisted, but Email Failed')
                            ->body('Status updated. Error: ' . $e->getMessage())
                            ->warning()
                            ->send();
                    }
                }),

            Actions\Action::make('resendSelectionEmail')
                ->label('Resend Selection Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Resend Selection Email')
                ->modalDescription(fn () => "Resend congratulations & selection email to {$this->record->email}?")
                ->visible(fn () => $this->record->status === 'shortlisted')
                ->action(function () {
                    $assignment = $this->record->interviewAssignments()->latest()->first();
                    try {
                        Mail::to($this->record->email)->send(new \App\Mail\CandidateSelectedMail($assignment ?? $this->record));
                        Notification::make()->title('Selection Email Sent Successfully')->success()->send();
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed resending candidate selected mail to {$this->record->email}: " . $e->getMessage());
                        Notification::make()
                            ->title('Failed to Send Email')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('rejectCandidate')
                ->label('Reject Candidate')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Candidate')
                ->modalDescription('Are you sure you want to reject this candidate? A rejection email will be sent.')
                ->visible(fn () => in_array($this->record->status, ['interview_scheduled', 'interviewed']))
                ->action(function () {
                    $this->record->update(['status' => 'rejected']);
                    $assignment = $this->record->interviewAssignments()->latest()->first();
                    if ($assignment) {
                        $assignment->update(['result' => 'rejected']);
                    }
                    
                    try {
                        Mail::to($this->record->email)->send(new \App\Mail\CandidateRejectedMail($this->record));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed sending candidate rejected mail: " . $e->getMessage());
                    }

                    Notification::make()->title('Candidate Rejected & Email Sent')->danger()->send();
                }),

            Actions\Action::make('viewResume')
                ->label('View Resume')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => $this->record->resume_path ? asset('storage/' . $this->record->resume_path) : null, shouldOpenInNewTab: true)
                ->visible(fn () => !empty($this->record->resume_path)),

            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
