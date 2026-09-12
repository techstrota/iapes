<?php

namespace App\Filament\Intern\Resources\TaskManagement;

use App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource\Pages;
use App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource\RelationManagers;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Textarea, FileUpload};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action};
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

class AssignedTaskResource extends Resource
{
    protected static ?string $model = TaskAssignment::class;
    // This changes the text in the Sidebar
    protected static ?string $navigationLabel = 'Assigned Task';
    // This changes the Heading on the List page
    public static function getPluralLabel(): ?string
    {
        return 'Assigned Tasks';
    }

    protected static ?string $navigationIcon = 'heroicon-s-list-bullet';
    protected static ?string $navigationGroup = 'Task Management';
    protected static ?int $navigationSort = 6;

    // 1. IMPORTANT: Filter the query so interns only see their own tasks
    public static function getEloquentQuery(): Builder
    {
        $userId = Auth::id();

        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($userId) {
                // 1. Tasks assigned directly to this Intern
                $query->where('intern_id', $userId)
                
                // 2. OR Tasks assigned to a Team that this Intern is a member of
                ->orWhereHas('team.interns', function ($q) use ($userId) {
                    $q->where('interns.id', $userId);
                })
                
                // 3. OR Tasks assigned to the whole Batch this Intern belongs to
                ->orWhereExists(function ($q) use ($userId) {
                    $q->select(\DB::raw(1))
                        ->from('interns')
                        ->whereColumn('interns.internship_batch_id', 'task_assignments.batch_id')
                        ->where('interns.id', $userId);
                });
            })
            // ADD 'team.interns', 'batch', and 'intern' here to eager load names
            ->with(['task', 'task_submission', 'team.interns', 'batch', 'intern']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(12)
                    ->schema([

                        // ── TOP LEFT: Task Info (8/12 cols) ─────────────
                        Section::make('Task Details')
                            ->columnSpan(['default' => 12, 'md' => 8])
                            ->schema([
                                // Large task title
                                TextEntry::make('task.title')
                                    ->hiddenLabel()
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold')
                                    ->color('primary')
                                    ->columnSpanFull(),

                                // Deadline urgency notice (red/orange/green alert)
                                \Filament\Infolists\Components\TextEntry::make('deadline_notice')
                                    ->hiddenLabel()
                                    ->getStateUsing(function (TaskAssignment $record): string {
                                        $due = $record->task?->due_date;
                                        if (!$due) return '';
                                        $diff = now()->startOfDay()->diffInDays(
                                            \Carbon\Carbon::parse($due)->startOfDay(), false
                                        );
                                        if ($diff < 0)   return '⚠ This task is overdue by ' . abs((int)$diff) . ' days.';
                                        if ($diff === 0) return '🔥 This task is due TODAY! Submit as soon as possible.';
                                        if ($diff <= 2)  return '⏰ Only ' . (int)$diff . ' day(s) remaining. Don\'t miss the deadline!';
                                        return '';
                                    })
                                    ->color(function (TaskAssignment $record): string {
                                        $due = $record->task?->due_date;
                                        if (!$due) return 'gray';
                                        $diff = now()->startOfDay()->diffInDays(
                                            \Carbon\Carbon::parse($due)->startOfDay(), false
                                        );
                                        return $diff < 0 ? 'danger' : ($diff <= 2 ? 'warning' : 'gray');
                                    })
                                    ->visible(fn (TaskAssignment $record): bool => (function () use ($record) {
                                        $due = $record->task?->due_date;
                                        if (!$due) return false;
                                        $diff = now()->startOfDay()->diffInDays(
                                            \Carbon\Carbon::parse($due)->startOfDay(), false
                                        );
                                        return $diff <= 2; // Only show if urgent or overdue
                                    })())
                                    ->columnSpanFull(),

                                // Priority / Deadline / Status / Attachment row
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('task.priority')
                                            ->label('Priority')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => strtoupper($state ?? '')),
                                        TextEntry::make('task.due_date')
                                            ->label('Deadline')
                                            ->date('M d, Y')
                                            ->icon('heroicon-o-calendar')
                                            ->placeholder('No deadline'),
                                        TextEntry::make('task_submission.status')
                                            ->label('My Status')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'submitted' => 'Under Review',
                                                'approved'  => '✅ Approved',
                                                'rejected'  => '❌ Rejected',
                                                'reviewed'  => 'Reviewed',
                                                default     => ucfirst($state ?? 'Not Submitted'),
                                            })
                                            ->color(fn ($state): string => match ($state) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'reviewed' => 'info',
                                                'submitted' => 'warning',
                                                default    => 'gray',
                                            }),
                                        TextEntry::make('task.attachment')
                                            ->label('Task File')
                                            ->formatStateUsing(fn ($state) => $state ? '📎 View File' : 'None')
                                            ->url(fn ($record) => $record->task->attachment
                                                ? asset('storage/' . $record->task->attachment)
                                                : null, true)
                                            ->color('warning'),
                                    ]),

                                // Description
                                TextEntry::make('task.description')
                                    ->label('Description')
                                    ->markdown()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),

                        // ── TOP RIGHT: Assignment Info (4/12 cols) ───────
                        Section::make('Assignment Info')
                            ->columnSpan(['default' => 12, 'md' => 4])
                            ->schema([
                                TextEntry::make('assigned_type')
                                    ->label('Assigned To')
                                    ->getStateUsing(function (TaskAssignment $record) {
                                        return match ($record->assigned_type) {
                                            'intern' => '👤 You (Individual)',
                                            'team'   => '👥 Team: ' . ($record->team?->team_name ?? 'N/A'),
                                            'batch'  => '🎓 Batch: ' . ($record->batch?->batch_name ?? 'N/A'),
                                            default  => 'Unassigned',
                                        };
                                    })
                                    ->badge()
                                    ->color(fn ($state) =>
                                        str_contains($state, 'Team') ? 'warning' :
                                        (str_contains($state, 'Batch') ? 'info' : 'gray')
                                    ),

                                TextEntry::make('task.created_at')
                                    ->label('Assigned On')
                                    ->date('M d, Y')
                                    ->icon('heroicon-o-calendar-days'),
                            ]),

                        // ── BOTTOM: My Submission — Full Width ───────────
                        Section::make('My Submission')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->task_submission !== null)
                            ->schema([

                                // Grade/Score block — shown FIRST, most important
                                \Filament\Infolists\Components\TextEntry::make('grade_block')
                                    ->hiddenLabel()
                                    ->visible(fn (TaskAssignment $record): bool =>
                                        $record->task_submission?->marks !== null ||
                                        $record->task_submission?->grade !== null
                                    )
                                    ->getStateUsing(fn (TaskAssignment $record) =>
                                        implode('   |   ', array_filter([
                                            $record->task_submission?->grade ? 'Grade: ' . $record->task_submission->grade : null,
                                            $record->task_submission?->marks !== null ? 'Score: ' . $record->task_submission->marks . ' / 100' : null,
                                            $record->task_submission?->evaluated_at ? 'Evaluated: ' . \Carbon\Carbon::parse($record->task_submission->evaluated_at)->format('M d, Y') : null,
                                        ]))
                                    )
                                    ->color('success')
                                    ->weight('bold')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->columnSpanFull(),

                                // Admin Feedback — shown second, prominent
                                TextEntry::make('task_submission.admin_feedback')
                                    ->label('Admin Feedback')
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->color('info')
                                    ->placeholder('No feedback provided yet.')
                                    ->prose()
                                    ->columnSpanFull(),

                                // Submitted file + link side by side
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('task_submission.submission_file')
                                            ->label('Uploaded File')
                                            ->formatStateUsing(fn ($state) => $state ? '⬇ Download File' : 'No file uploaded')
                                            ->url(fn ($record) => $record->task_submission?->submission_file
                                                ? asset('storage/' . $record->task_submission->submission_file)
                                                : null, true)
                                            ->color('success')
                                            ->icon('heroicon-o-arrow-down-tray'),

                                        TextEntry::make('task_submission.submission_text')
                                            ->label('Submitted Link')
                                            ->url(fn ($record) => filter_var(
                                                $record->task_submission?->submission_text,
                                                FILTER_VALIDATE_URL
                                            ) ? $record->task_submission->submission_text : null, true)
                                            ->color(fn ($record) => filter_var(
                                                $record->task_submission?->submission_text,
                                                FILTER_VALIDATE_URL
                                            ) ? 'primary' : 'gray')
                                            ->placeholder('None'),
                                    ]),
                            ]),

                        // ── Not Submitted CTA ────────────────────────────
                        \Filament\Infolists\Components\TextEntry::make('not_submitted_notice')
                            ->hiddenLabel()
                            ->visible(fn ($record) => $record->task_submission === null)
                            ->getStateUsing(fn () => '📭  You haven\'t submitted this task yet. Use the "Submit" button in the task list to submit your work.')
                            ->columnSpanFull()
                            ->color('warning'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s') // Reduced from 3s — less aggressive, better performance
            ->defaultSort('task.due_date', 'asc') // Show most urgent first
            ->columns([
                // ── 1. Task Title + description preview ─────────────────
                TextColumn::make('task.title')
                    ->label('Task')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (TaskAssignment $record): string =>
                        \Illuminate\Support\Str::limit($record->task?->description ?? '', 70)
                    ),

                // ── 2. Priority with color coding ───────────────────────
                TextColumn::make('task.priority')
                    ->label('Priority')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? ''))
                    ->color(fn ($state) => match ($state) {
                        'high'   => 'danger',
                        'medium' => 'warning',
                        'low'    => 'success',
                        default  => 'gray',
                    }),

                // ── 3. Smart Deadline — shows urgency ───────────────────
                TextColumn::make('task.due_date')
                    ->label('Deadline')
                    ->sortable()
                    ->getStateUsing(function (TaskAssignment $record): string {
                        $due = $record->task?->due_date;
                        if (!$due) return 'No deadline';
                        $now  = now()->startOfDay();
                        $due  = \Carbon\Carbon::parse($due)->startOfDay();
                        $diff = $now->diffInDays($due, false); // negative = past

                        if ($diff < 0)    return '⚠ Overdue by ' . abs((int)$diff) . 'd';
                        if ($diff === 0)  return '🔥 Due Today!';
                        if ($diff <= 2)   return '⏰ ' . (int)$diff . ' days left';
                        return \Carbon\Carbon::parse($record->task->due_date)->format('M d, Y');
                    })
                    ->color(function (TaskAssignment $record): string {
                        $due = $record->task?->due_date;
                        if (!$due) return 'gray';
                        $diff = now()->startOfDay()->diffInDays(
                            \Carbon\Carbon::parse($due)->startOfDay(), false
                        );
                        if ($diff < 0)   return 'danger';
                        if ($diff <= 2)  return 'warning';
                        return 'gray';
                    }),

                // ── 4. Submission Status ─────────────────────────────────
                TextColumn::make('submission_status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) =>
                        $record->task_submission?->status ?? 'not submitted'
                    )
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'not submitted' => 'Not Submitted',
                        'submitted'     => 'Under Review',
                        'reviewed'      => 'Reviewed',
                        'approved'      => '✅ Approved',
                        'rejected'      => '❌ Rejected',
                        default         => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'approved'      => 'success',
                        'rejected'      => 'danger',
                        'reviewed' => 'info',
                        'submitted'     => 'warning',
                        'not submitted' => 'gray',
                        default         => 'gray',
                    }),

                // ── 5. Grade & Score — NEW ───────────────────────────────
                TextColumn::make('grade_summary')
                    ->label('Grade / Score')
                    ->getStateUsing(function (TaskAssignment $record): string {
                        $sub = $record->task_submission;
                        if (!$sub) return '—';
                        if (!$sub->marks && !$sub->grade) return '—';
                        $parts = [];
                        if ($sub->grade) $parts[] = $sub->grade;
                        if ($sub->marks !== null) $parts[] = $sub->marks . '/100';
                        return implode(' · ', $parts);
                    })
                    ->color(fn ($state) => $state === '—' ? 'gray' : 'success')
                    ->weight(fn ($state) => $state !== '—'
                        ? 'bold'
                        : 'normal'
                    ),
            ])
            ->filters([
                // Tabs replace the filter — no filter needed here
            ])
            ->actions([
                // ── View & Feedback ─────────────────────────────────────
                Tables\Actions\ViewAction::make()
                    ->label('View & Feedback')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Task Details')
                    ->modalWidth('5xl'),

                // ── Submit Task ──────────────────────────────────────────
                Action::make('submit_task')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (TaskAssignment $record) =>
                        !$record->task_submission ||
                        $record->task_submission?->status === 'rejected'
                    )
                    ->modalHeading(fn (TaskAssignment $record) =>
                        'Submit: ' . ($record->task?->title ?? 'Task')
                    )
                    ->modalWidth('2xl')
                    ->form([
                        // Deadline reminder at top of form
                        Forms\Components\Placeholder::make('deadline_notice')
                            ->label('')
                            ->content(function (TaskAssignment $record) {
                                $due = $record->task?->due_date;
                                if (!$due) return new \Illuminate\Support\HtmlString('');
                                $diff = now()->startOfDay()->diffInDays(
                                    \Carbon\Carbon::parse($due)->startOfDay(), false
                                );
                                $color = $diff < 0 ? '#ef4444' : ($diff <= 2 ? '#f59e0b' : '#10b981');
                                $msg   = $diff < 0
                                    ? '⚠ This task is overdue by ' . abs((int)$diff) . ' days.'
                                    : ($diff === 0 ? '🔥 This task is due today!' : '📅 Due: ' . \Carbon\Carbon::parse($due)->format('M d, Y'));
                                return new \Illuminate\Support\HtmlString(
                                    "<div style='padding:10px 14px;border-radius:6px;border-left:4px solid {$color};background:rgba(0,0,0,0.05);color:{$color};font-size:13px;font-weight:600;'>{$msg}</div>"
                                );
                            }),

                        FileUpload::make('attachment')
                            ->label('Upload Your Work')
                            ->helperText('Accepted: PDF, ZIP, images, DOCX, XLSX')
                            ->directory('submissions')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/zip',
                                'application/x-zip-compressed',
                                'image/*',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]),

                        TextInput::make('link')
                            ->label('Submission Link')
                            ->url()
                            ->placeholder('https://github.com/...')
                            ->helperText('Paste your GitHub repo, Figma file, Google Drive link, etc.'),

                        Forms\Components\Placeholder::make('submission_notice')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                "<div style='padding:10px 14px;border-radius:6px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);color:#60a5fa;font-size:12px;'>
                                    ℹ️ Once submitted, the admin will review your work. If rejected, you will be able to resubmit.
                                </div>"
                            )),
                    ])
                    ->action(function (TaskAssignment $record, array $data): void {
                        TaskSubmission::updateOrCreate(
                            ['task_id' => $record->task_id, 'intern_id' => Auth::id()],
                            [
                                'submission_file' => $data['attachment'] ?? null,
                                'submission_text' => $data['link']       ?? null,
                                'status'          => 'submitted',
                                'submitted_at'    => now(),
                            ]
                        );
                        $record->update(['status' => 'submitted']);
                        \Filament\Notifications\Notification::make()
                            ->title('Work submitted successfully! 🎉')
                            ->body('The admin has been notified. You\'ll be updated once it\'s reviewed.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignedTasks::route('/'),
        ];
    }
}
