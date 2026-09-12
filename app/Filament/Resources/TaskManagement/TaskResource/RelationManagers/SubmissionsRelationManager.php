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
    protected static ?string $title = 'Submitted Interns';
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
                    ->color('primary'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder(fn ($record) => $record->updated_at->format('M d, Y H:i')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'gray',
                        'reviewed'  => 'warning',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('marks')
                    ->formatStateUsing(fn ($state) => $state . ' / 100')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('grade')
                    ->placeholder('—'),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('evaluate')
                    ->label('Evaluate')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->modalHeading(fn ($record) => 'Evaluate: ' . ($record->intern?->name ?? 'Unknown'))
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
                                Forms\Components\Section::make('📄 Intern Submission')
                                    ->columnSpan(['default' => 12, 'md' => 5])
                                    ->schema([
                                        Forms\Components\Placeholder::make('_submission_text')
                                            ->label('Submission Notes / Link')
                                            ->content(fn (Forms\Get $get): \Illuminate\Support\HtmlString => 
                                                new \Illuminate\Support\HtmlString('<div class="prose dark:prose-invert text-sm" style="max-height: 250px; overflow-y: auto; padding: 12px; background-color: rgba(128,128,128,0.05); border-radius: 6px; border: 1px solid rgba(128,128,128,0.1);">' . nl2br(e($get('_submission_text') ?: 'No text was submitted.')) . '</div>')
                                            )
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('_file_download')
                                            ->label('Submitted File')
                                            ->content(fn (Forms\Get $get): \Illuminate\Support\HtmlString => filled($get('_submission_file'))
                                                ? new \Illuminate\Support\HtmlString(
                                                    '<a href="' . asset('storage/' . $get('_submission_file')) . '" 
                                                        target="_blank" 
                                                        style="display:inline-flex;align-items:center;gap:6px;color:#1F6AAE;font-weight:600;text-decoration:none; padding: 8px 12px; background-color: rgba(31,106,174,0.1); border-radius: 6px;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                        </svg>
                                                        Download Attachment
                                                    </a>'
                                                )
                                                : new \Illuminate\Support\HtmlString('<span style="color:#9ca3af;font-style:italic;">No file uploaded.</span>')
                                            )
                                            ->columnSpanFull(),
                                    ]),

                                // ── RIGHT COLUMN: Evaluation Form ────────────────────────
                                Forms\Components\Section::make('✏️ Grading & Assessment')
                                    ->columnSpan(['default' => 12, 'md' => 7])
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('eval_status')
                                                    ->label('Decision')
                                                    ->options([
                                                        'reviewed' => 'Mark as Reviewed',
                                                        'approved' => 'Approve ✅',
                                                        'rejected' => 'Reject / Revision Required ❌',
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
                                                    ->label('Grade')
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
                                                    ->label('Feedback / Remarks')
                                                    ->placeholder('Explain your decision — what was good, what needs improvement...')
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
