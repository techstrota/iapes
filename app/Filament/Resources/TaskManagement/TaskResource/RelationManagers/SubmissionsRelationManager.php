<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Illuminate\Support\Collection;

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
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Not evaluated')
                    ->placeholder('Not evaluated'),
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
                        'eval_status'      => $record->status,
                        'eval_marks'       => $record->marks,
                        'eval_feedback'    => $record->admin_feedback,
                        // Read-only display fields
                        '_submission_text' => $record->submission_text,
                        '_submission_file' => $record->submission_file,
                        '_submission_id'   => $record->submission_id,
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
                                            ->label('Submitted File Deliverable')
                                            ->content(function (Forms\Get $get, $record): \Illuminate\Support\HtmlString {
                                                $filePath = $get('_submission_file');
                                                $submissionId = $record?->submission_id ?? $get('_submission_id');

                                                if (!filled($filePath)) {
                                                    return new \Illuminate\Support\HtmlString(
                                                        '<div style="padding: 12px 14px; background-color: #090e1c; border: 1px dashed #222a3d; border-radius: 8px; color: #8e909f; font-size: 12.5px;">No deliverable file was uploaded with this submission.</div>'
                                                    );
                                                }

                                                $downloadUrl = $submissionId ? route('task.submission.download', ['id' => $submissionId]) : asset('storage/' . $filePath);
                                                $previewUrl  = asset('storage/' . $filePath);
                                                $fileName    = basename($filePath);

                                                return new \Illuminate\Support\HtmlString('
                                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                                                                                        <a href="' . $downloadUrl . '" 
                                                               download
                                                               style="display: inline-flex; align-items: center; gap: 8px; color: #ffffff; font-weight: 700; font-size: 12.5px; text-decoration: none; padding: 9px 18px; background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); border: 1px solid #3b82f6; border-radius: 9px; box-shadow: 0 2px 10px rgba(37, 99, 235, 0.4); cursor: pointer;"
                                                               onmouseover="this.style.background=\'#1d4ed8\';"
                                                               onmouseout="this.style.background=\'linear-gradient(135deg, #1e40af 0%, #2563eb 100%)\';">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:16px;height:16px;">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                                </svg>
                                                                <span>Download Deliverable File</span>
                                                            </a>

                                                                                                                        <a href="' . $previewUrl . '" 
                                                               target="_blank" 
                                                               style="display: inline-flex; align-items: center; gap: 6px; color: #93c5fd; font-weight: 600; font-size: 12px; text-decoration: none; padding: 8px 14px; background-color: #0e172a; border: 1px solid #222a3d; border-radius: 9px;"
                                                               onmouseover="this.style.borderColor=\'#3b82f6\'; this.style.color=\'#ffffff\';"
                                                               onmouseout="this.style.borderColor=\'#222a3d\'; this.style.color=\'#93c5fd\';">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                </svg>
                                                                <span>Preview in Tab</span>
                                                            </a>
                                                        </div>
                                                        <div style="font-size: 11px; color: #8e909f;">
                                                            📁 File: ' . e($fileName) . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->columnSpanFull(),
                                    ]),

                                // ── RIGHT COLUMN: Evaluation Form ────────────────────────
                                Forms\Components\Section::make('Evaluation & Assessment')
                                    ->description('Assign status, score, and feedback for the intern.')
                                    ->columnSpan(['default' => 12, 'md' => 7])
                                    ->schema([
                                        Forms\Components\Select::make('eval_status')
                                            ->label('Evaluation Decision')
                                            ->options([
                                                'reviewed' => 'Mark as Reviewed',
                                                'approved' => 'Approve Deliverable',
                                                'rejected' => 'Reject / Revision Required',
                                            ])
                                            ->required()
                                            ->native(false),

                                        Forms\Components\TextInput::make('eval_marks')
                                            ->label('Score (0–100)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('/ 100'),

                                        Forms\Components\Textarea::make('eval_feedback')
                                            ->label('Feedback & Remarks')
                                            ->placeholder('Explain your decision — what was done well, what needs revision...')
                                            ->rows(4),
                                    ]),
                            ]),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'         => $data['eval_status'],
                            'marks'          => $data['eval_marks'] ?? null,
                            'admin_feedback' => $data['eval_feedback'] ?? null,
                            'evaluated_at'   => now(),
                        ]);

                        // Notify intern via database notification
                        if ($record->intern) {
                            Notification::make()
                                ->title('Your submission has been ' . ucfirst($data['eval_status']) . '.')
                                ->body($data['eval_feedback'] ?? '')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->iconColor($data['eval_status'] === 'approved' ? 'success' : ($data['eval_status'] === 'rejected' ? 'danger' : 'info'))
                                ->sendToDatabase($record->intern);
                        }

                        // Auto-complete the task if all interns are approved
                        $task = $record->task;
                        if ($task && method_exists($task, 'checkAndAutoComplete')) {
                            $task->checkAndAutoComplete();
                        }
                    })
                    ->successNotificationTitle('Evaluation saved successfully.'),

                Tables\Actions\Action::make('download_deliverable')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn ($record) => filled($record->submission_file))
                    ->url(fn ($record) => route('task.submission.download', ['id' => $record->submission_id]))
                    ->openUrlInNewTab(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_evaluate')
                    ->label('Bulk Evaluate')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->extraAttributes([
                        'style' => 'background-color: #1e40af !important; border: 1px solid #3b82f6 !important; color: #ffffff !important; border-radius: 8px !important; font-weight: 600 !important;',
                    ])
                    ->modalHeading('Bulk Evaluate Selected Submissions')
                    ->modalDescription('Update evaluation status and timestamps for all selected submissions. Marks are not required for bulk evaluation.')
                    ->modalSubmitActionLabel('Save Bulk Evaluation')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Evaluation Status')
                            ->options([
                                'reviewed' => 'Mark as Reviewed',
                                'approved' => 'Approve Deliverables',
                                'rejected' => 'Reject / Revision Required',
                            ])
                            ->default('reviewed')
                            ->required()
                            ->native(false)
                            ->helperText('Select the status to apply to all selected intern deliverables.'),

                        Forms\Components\Textarea::make('admin_feedback')
                            ->label('Evaluation Remarks / Feedback (Optional)')
                            ->placeholder('Optional comment to record on all selected submissions...')
                            ->rows(3)
                            ->helperText('Leave empty to preserve existing feedback or provide general remarks.'),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $count = 0;
                        $status = $data['status'];
                        $feedback = filled($data['admin_feedback'] ?? null) ? $data['admin_feedback'] : null;

                        foreach ($records as $record) {
                            $updateData = [
                                'status'       => $status,
                                'evaluated_at' => now(),
                            ];

                            if ($feedback !== null) {
                                $updateData['admin_feedback'] = $feedback;
                            }

                            $record->update($updateData);

                            // Send notification to the intern
                            if ($record->intern) {
                                Notification::make()
                                    ->title('Your task submission has been ' . ($status === 'reviewed' ? 'reviewed' : ucfirst($status)) . '.')
                                    ->body($feedback ?? 'Your deliverable status has been updated by the admin team.')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->iconColor($status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'info'))
                                    ->sendToDatabase($record->intern);
                            }

                            $count++;
                        }

                        // Auto-complete the task if all assigned interns are approved
                        $firstRecord = $records->first();
                        $task = $firstRecord?->task ?? ($this->getOwnerRecord() instanceof \App\Models\TaskManagement\Task ? $this->getOwnerRecord() : null);
                        if ($task && method_exists($task, 'checkAndAutoComplete')) {
                            $task->checkAndAutoComplete();
                        }

                        Notification::make()
                            ->title('Bulk Evaluation Complete')
                            ->body("Successfully updated {$count} " . ($count === 1 ? 'submission' : 'submissions') . ' to ' . ucfirst($status) . '.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
}
