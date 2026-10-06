<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';
    protected static ?string $title = 'Submitted Deliverables';
    protected static ?string $icon = 'heroicon-o-check-circle';

    public function form(Form $form): Form
    {
        // This is only used if needed by other Filament internals; actual form is inline on the action.
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('submission_id')
            ->recordAction('evaluate')
            ->columns([
                Tables\Columns\TextColumn::make('intern.name')
                    ->label('Intern')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->intern?->email ?? null)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime('M d, Y • H:i')
                    ->sortable()
                    ->placeholder(fn ($record) => $record->updated_at->format('M d, Y • H:i')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'info',
                        'reviewed'  => 'warning',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'submitted' => 'Submitted',
                        'reviewed'  => 'Reviewed',
                        'approved'  => 'Approved',
                        'rejected'  => 'Needs Revision',
                        default     => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('marks')
                    ->label('Score')
                    ->badge()
                    ->color(fn ($state) => $state !== null ? ($state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')) : 'gray')
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Not graded')
                    ->placeholder('Not graded'),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'O', 'A+' => 'success',
                        'A', 'B'  => 'info',
                        'C'       => 'warning',
                        'F'       => 'danger',
                        default   => 'gray',
                    })
                    ->placeholder('—'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->extraAttributes([
                        'style' => 'background-color: #1e40af !important; border: 1px solid #3b82f6 !important; color: #ffffff !important; border-radius: 8px !important; font-weight: 600 !important;',
                    ])
                    ->modalHeading(fn ($record) => 'Evaluate Submission: ' . ($record->intern?->name ?? 'Unknown'))
                    ->modalSubmitActionLabel('Save Evaluation')
                    ->modalWidth('6xl')
                    ->fillForm(fn ($record): array => [
                        // Pre-fill evaluation fields with existing data
                        'eval_status'   => $record->status,
                        'eval_marks'    => $record->marks,
                        'eval_grade'    => $record->grade,
                        'eval_feedback' => $record->admin_feedback,
                        // Read-only display fields
                        '_submission_text' => $record->submission_text,
                        '_submission_file' => $record->submission_file,
                    ])
                    ->form([
                        Forms\Components\Grid::make(12)
                            ->schema([
                                // ── LEFT COLUMN: Intern Submission (read-only) ───────────
                                Forms\Components\Section::make('Intern Deliverable Details')
                                    ->description('Review the submitted content and attachments.')
                                    ->columnSpan(['default' => 12, 'md' => 5])
                                    ->schema([
                                        Forms\Components\Placeholder::make('_submission_text')
                                            ->label('Submission Content & Links')
                                            ->content(fn (Forms\Get $get): \Illuminate\Support\HtmlString => 
                                                new \Illuminate\Support\HtmlString('<div style="max-height: 250px; overflow-y: auto; padding: 14px 16px; background-color: #090e1c; border-radius: 10px; border: 1px solid #222a3d; color: #dae2fd; font-size: 13px; line-height: 1.6; word-break: break-word;">' . nl2br(e($get('_submission_text') ?: 'No submission notes or links were provided.')) . '</div>')
                                            )
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('_file_download')
                                            ->label('Submitted File Attachment')
                                            ->content(fn (Forms\Get $get): \Illuminate\Support\HtmlString => filled($get('_submission_file'))
                                                ? new \Illuminate\Support\HtmlString(
                                                    '<a href="' . asset('storage/' . $get('_submission_file')) . '" 
                                                        target="_blank" 
                                                        style="display:inline-flex;align-items:center;gap:8px;color:#ffffff;font-weight:600;font-size:12.5px;text-decoration:none; padding: 9px 16px; background-color: #1e40af; border: 1px solid #3b82f6; border-radius: 9px; box-shadow: 0 2px 8px rgba(30, 64, 175, 0.4);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                        </svg>
                                                        Download Attachment
                                                    </a>'
                                                )
                                                : new \Illuminate\Support\HtmlString('<div style="padding: 10px 14px; background-color: #090e1c; border: 1px dashed #222a3d; border-radius: 8px; color: #8e909f; font-size: 12.5px;">No file was uploaded with this submission.</div>')
                                            )
                                            ->columnSpanFull(),
                                    ]),

                                // ── RIGHT COLUMN: Evaluation Form ────────────────────────
                                Forms\Components\Section::make('Grading & Assessment')
                                    ->description('Assign status, marks, and feedback for the intern.')
                                    ->columnSpan(['default' => 12, 'md' => 7])
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('eval_status')
                                                    ->label('Evaluation Decision')
                                                    ->options([
                                                        'reviewed' => 'Mark as Reviewed',
                                                        'approved' => 'Approve Deliverable',
                                                        'rejected' => 'Reject / Revision Required',
                                                    ])
                                                    ->required()
                                                    ->native(false)
                                                    ->columnSpanFull(),

                                                Forms\Components\TextInput::make('eval_marks')
                                                    ->label('Score (0–100)')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(100)
                                                    ->suffix('/ 100'),

                                                Forms\Components\Select::make('eval_grade')
                                                    ->label('Letter Grade')
                                                    ->options([
                                                        'O'  => 'O – Outstanding',
                                                        'A+' => 'A+ – Excellent',
                                                        'A'  => 'A – Very Good',
                                                        'B'  => 'B – Good',
                                                        'C'  => 'C – Average',
                                                        'F'  => 'F – Fail',
                                                    ])
                                                    ->native(false),

                                                Forms\Components\Textarea::make('eval_feedback')
                                                    ->label('Feedback & Remarks')
                                                    ->placeholder('Explain your decision — what was done well, what needs revision...')
                                                    ->rows(4)
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'         => $data['eval_status'],
                            'marks'          => $data['eval_marks'] ?? null,
                            'grade'          => $data['eval_grade'] ?? null,
                            'admin_feedback' => $data['eval_feedback'] ?? null,
                            'evaluated_at'   => now(),
                        ]);

                        // Notify intern via database notification
                        Notification::make()
                            ->title('Your submission has been ' . ucfirst($data['eval_status']) . '.')
                            ->body($data['eval_feedback'] ?? '')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->iconColor($data['eval_status'] === 'approved' ? 'success' : ($data['eval_status'] === 'rejected' ? 'danger' : 'info'))
                            ->sendToDatabase($record->intern);

                        // Auto-complete the task if all interns are approved
                        $task = $record->task;
                        if ($task) {
                            $task->checkAndAutoComplete();
                        }
                    })
                    ->successNotificationTitle('Evaluation saved successfully.'),
            ])
            ->bulkActions([]);
    }
}
