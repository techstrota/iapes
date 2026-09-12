<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use App\Mail\CandidateSelectedMail;

class InterviewHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'interviewAssignments';

    protected static ?string $title = 'Interview History';
    protected static ?string $icon = 'heroicon-o-calendar-days';

    // Prevent creating assignments directly — must use "Schedule Interview" action
    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Interviews Yet')
            ->emptyStateDescription('Use the "Schedule Interview" button above to assign this candidate to an interview batch.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->columns([
                Tables\Columns\TextColumn::make('assignment_code')
                    ->label('Assignment')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('batch.interview_batch_name')
                    ->label('Batch Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('batch.interview_date')
                    ->label('Interview Date')
                    ->date('d M, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('batch.interview_location')
                    ->label('Location')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('attendance')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'present' => 'Present',
                        'absent' => 'Absent',
                        default => 'Not Marked',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (?string $state) => match ($state) {
                        'present' => 'heroicon-m-check-circle',
                        'absent' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-minus-circle',
                    }),

                Tables\Columns\TextColumn::make('problem_solving')
                    ->label('Problem Solving')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '/25' : '—')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('communication')
                    ->label('Communication')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '/25' : '—')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('overall_score')
                    ->label('Total Score')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . '/50' : '—')
                    ->weight('bold')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 40 => 'success',
                        $state >= 25 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('result')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'selected' => 'Selected',
                        'rejected' => 'Rejected',
                        'pending' => 'Pending',
                        default => 'Pending',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'selected' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->icon(fn (?string $state) => match ($state) {
                        'selected' => 'heroicon-m-star',
                        'rejected' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-clock',
                    }),
            ])

            ->actions([
                // ── Score / Evaluate Action ──
                Action::make('score')
                    ->label('Score')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading(fn ($record) => 'Score Interview — ' . ($record->batch?->interview_batch_name ?? 'Unknown Batch'))
                    ->fillForm(fn ($record) => [
                        'attendance' => $record->attendance,
                        'problem_solving' => $record->problem_solving,
                        'communication' => $record->communication,
                        'remarks' => $record->remarks,
                    ])
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
                                ->disabled(fn (Forms\Get $get) => $get('attendance') !== 'present')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $set('overall_score', ($state ?? 0) + ($get('communication') ?? 0));
                                }),

                            Forms\Components\TextInput::make('communication')
                                ->label('Communication (Max 25)')
                                ->disabled(fn (Forms\Get $get) => $get('attendance') !== 'present')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $set('overall_score', ($state ?? 0) + ($get('problem_solving') ?? 0));
                                }),
                        ]),

                        Forms\Components\TextInput::make('overall_score')
                            ->label('Total Score (Auto-calculated)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('remarks')
                            ->label('Remarks / Notes')
                            ->rows(3)
                            ->placeholder('Optional notes about the interview performance...'),
                    ])
                    ->action(function ($record, array $data) {
                        $updateData = [
                            'attendance' => $data['attendance'],
                            'remarks' => $data['remarks'] ?? null,
                        ];

                        if ($data['attendance'] === 'present') {
                            $updateData['problem_solving'] = $data['problem_solving'];
                            $updateData['communication'] = $data['communication'];
                            $updateData['overall_score'] = ($data['problem_solving'] ?? 0) + ($data['communication'] ?? 0);
                        } else {
                            $updateData['problem_solving'] = null;
                            $updateData['communication'] = null;
                            $updateData['overall_score'] = null;
                        }

                        $record->update($updateData);

                        Notification::make()
                            ->title('Score Saved Successfully')
                            ->success()
                            ->send();
                    }),

                // ── Mark as Selected ──
                Action::make('markSelected')
                    ->label('Select')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Selection')
                    ->modalDescription('Mark this candidate as SELECTED? An email notification will be sent.')
                    ->visible(fn ($record) =>
                        $record->attendance === 'present' &&
                        $record->result !== 'selected' &&
                        $record->problem_solving !== null &&
                        $record->communication !== null
                    )
                    ->action(function ($record) {
                        $record->update(['result' => 'selected']);

                        Mail::to($record->application->email)
                            ->send(new CandidateSelectedMail($record));

                        Notification::make()
                            ->title('Candidate Selected')
                            ->body('Email notification sent.')
                            ->success()
                            ->send();
                    }),

                // ── Mark as Rejected ──
                Action::make('markRejected')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Rejection')
                    ->modalDescription('Mark this candidate as REJECTED?')
                    ->visible(fn ($record) =>
                        $record->result !== 'rejected' &&
                        $record->result !== 'selected'
                    )
                    ->action(function ($record) {
                        $record->update(['result' => 'rejected']);

                        Notification::make()
                            ->title('Candidate Rejected')
                            ->warning()
                            ->send();
                    }),
            ])

            ->bulkActions([
                BulkAction::make('bulkSelect')
                    ->label('Mark Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;
                        $index = 0;
                        foreach ($records as $record) {
                            if ($record->attendance === 'present' &&
                                $record->problem_solving !== null &&
                                $record->communication !== null) {
                                $record->update(['result' => 'selected']);
                                try {
                                    if ($index > 0) {
                                        sleep(2);
                                    }
                                    Mail::to($record->application->email)
                                        ->send(new CandidateSelectedMail($record));
                                    $count++;
                                } catch (\Throwable $e) {
                                    \Illuminate\Support\Facades\Log::error("Bulk select email failed for {$record->application->email}: " . $e->getMessage());
                                }
                                $index++;
                            }
                        }

                        Notification::make()
                            ->title("{$count} candidates marked as Selected")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('bulkReject')
                    ->label('Mark Rejected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(fn ($record) => $record->update(['result' => 'rejected']));

                        Notification::make()
                            ->title($records->count() . ' candidates marked as Rejected')
                            ->warning()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
