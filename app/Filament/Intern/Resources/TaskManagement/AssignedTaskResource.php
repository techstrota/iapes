<?php

namespace App\Filament\Intern\Resources\TaskManagement;

use App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource\Pages;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, FileUpload, Placeholder};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, ViewAction};
use Filament\Tables\Columns\{TextColumn};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;

class AssignedTaskResource extends Resource
{
    protected static ?string $model = TaskAssignment::class;
    protected static ?string $navigationLabel = 'Assigned Tasks';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Work & Tasks';
    protected static ?int $navigationSort = 1;

    public static function getPluralLabel(): ?string
    {
        return 'Assigned Tasks';
    }

    // Scoped query: Only return tasks assigned directly, through team, or through batch
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
            ->with(['task', 'task_submission', 'team.interns', 'batch', 'intern']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(12)
                    ->schema([

                        // ── TOP LEFT: Task Details (8 cols) ─────────────
                        Section::make('Task Details')
                            ->icon('heroicon-o-clipboard-document')
                            ->columnSpan(['default' => 12, 'md' => 8])
                            ->schema([
                                TextEntry::make('task.title')
                                    ->hiddenLabel()
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->columnSpanFull(),

                                // Deadline urgency notice
                                TextEntry::make('deadline_notice')
                                    ->hiddenLabel()
                                    ->getStateUsing(function (TaskAssignment $record): string {
                                        $due = $record->task?->due_date;
                                        if (!$due) return '';
                                        $diff = now()->startOfDay()->diffInDays(
                                            \Carbon\Carbon::parse($due)->startOfDay(), false
                                        );
                                        if ($diff < 0)   return '⚠ This task is overdue by ' . abs((int)$diff) . ' days.';
                                        if ($diff === 0) return '🔥 This task is due TODAY! Please submit your deliverable.';
                                        if ($diff <= 2)  return '⏰ Only ' . (int)$diff . ' day(s) remaining until the deadline.';
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
                                        return $diff <= 2;
                                    })())
                                    ->columnSpanFull(),

                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('task.priority')
                                            ->label('Priority')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => strtoupper($state ?? ''))
                                            ->color(fn ($state) => match ($state) {
                                                'high'   => 'danger',
                                                'medium' => 'warning',
                                                'low'    => 'success',
                                                default  => 'gray',
                                            }),

                                        TextEntry::make('task.due_date')
                                            ->label('Due Date')
                                            ->date('M d, Y')
                                            ->icon('heroicon-o-calendar')
                                            ->placeholder('No deadline'),

                                        TextEntry::make('task_submission.status')
                                            ->label('My Status')
                                            ->badge()
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'submitted' => 'Under Review',
                                                'approved'  => 'Approved',
                                                'rejected'  => 'Needs Revision',
                                                'reviewed'  => 'Reviewed',
                                                default     => ucfirst($state ?? 'Not Submitted'),
                                            })
                                            ->color(fn ($state): string => match ($state) {
                                                'approved'  => 'success',
                                                'rejected'  => 'danger',
                                                'reviewed'  => 'info',
                                                'submitted' => 'warning',
                                                default     => 'gray',
                                            }),

                                        TextEntry::make('task.attachment')
                                            ->label('Task Brief')
                                            ->formatStateUsing(fn ($state) => $state ? '📎 Download Brief' : 'No attachment')
                                            ->url(fn ($record) => $record->task->attachment
                                                ? asset('storage/' . $record->task->attachment)
                                                : null, true)
                                            ->color('primary'),
                                    ]),

                                TextEntry::make('task.description')
                                    ->label('Description & Instructions')
                                    ->markdown()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),

                        // ── TOP RIGHT: Assignment Info (4 cols) ───────
                        Section::make('Assignment Info')
                            ->icon('heroicon-o-information-circle')
                            ->columnSpan(['default' => 12, 'md' => 4])
                            ->schema([
                                TextEntry::make('assigned_type')
                                    ->label('Assigned Scope')
                                    ->getStateUsing(function (TaskAssignment $record) {
                                        return match ($record->assigned_type) {
                                            'intern' => '👤 Individual Task',
                                            'team'   => '👥 Team: ' . ($record->team?->team_name ?? 'N/A'),
                                            'batch'  => '🎓 Batch: ' . ($record->batch?->batch_name ?? 'N/A'),
                                            default  => 'General Assignment',
                                        };
                                    })
                                    ->badge()
                                    ->color(fn ($state) =>
                                        str_contains($state, 'Team') ? 'warning' :
                                        (str_contains($state, 'Batch') ? 'info' : 'primary')
                                    ),

                                TextEntry::make('task.created_at')
                                    ->label('Issued On')
                                    ->date('M d, Y')
                                    ->icon('heroicon-o-calendar-days'),
                            ]),

                        // ── BOTTOM: My Submission ───────────
                        Section::make('My Work & Evaluation')
                            ->icon('heroicon-o-check-badge')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->task_submission !== null)
                            ->schema([
                                TextEntry::make('grade_block')
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
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->columnSpanFull(),

                                TextEntry::make('task_submission.admin_feedback')
                                    ->label('Admin Evaluation Feedback')
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->color('info')
                                    ->placeholder('No feedback comments recorded yet.')
                                    ->prose()
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('task_submission.submission_file')
                                            ->label('Uploaded Deliverable')
                                            ->formatStateUsing(fn ($state) => $state ? '⬇ Download Deliverable' : 'No file uploaded')
                                            ->url(fn ($record) => $record->task_submission?->submission_file
                                                ? asset('storage/' . $record->task_submission->submission_file)
                                                : null, true)
                                            ->color('success')
                                            ->icon('heroicon-o-arrow-down-tray'),

                                        TextEntry::make('task_submission.submission_text')
                                            ->label('Work URL / Repository')
                                            ->url(fn ($record) => filter_var(
                                                $record->task_submission?->submission_text,
                                                FILTER_VALIDATE_URL
                                            ) ? $record->task_submission->submission_text : null, true)
                                            ->color('primary')
                                            ->placeholder('None provided'),
                                    ]),
                            ]),

                        // ── Not Submitted CTA ────────────────────────────
                        TextEntry::make('not_submitted_notice')
                            ->hiddenLabel()
                            ->visible(fn ($record) => $record->task_submission === null)
                            ->getStateUsing(fn () => '📭 You have not submitted this task yet. Click the "Submit Work" button in the table to upload your deliverables.')
                            ->columnSpanFull()
                            ->color('warning'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('15s')
            ->defaultSort('task_assignment_id', 'desc')
            ->contentGrid([
                'sm'  => 1,
                'md'  => 1,
                'lg'  => 2,
                'xl'  => 2,
                '2xl' => 3,
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                Tables\Columns\Layout\View::make('filament.intern.task-management.intern-task-card'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priority Level')
                    ->options([
                        'high'   => '🔥 High Priority',
                        'medium' => '⚡ Medium Priority',
                        'low'    => '🟢 Low Priority',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('task', fn ($q) => $q->where('priority', $data['value']));
                        }
                    }),

                Tables\Filters\SelectFilter::make('assigned_type')
                    ->label('Scope')
                    ->options([
                        'intern' => '👤 Solo Tasks',
                        'team'   => '👥 Team Tasks',
                        'batch'  => '🎓 Batch Tasks',
                    ]),
            ])
            ->actions([
                ViewAction::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Task Overview & Instructions')
                    ->modalWidth('5xl'),

                Action::make('submit_task')
                    ->label('Submit Work')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (TaskAssignment $record) =>
                        !$record->task_submission ||
                        in_array($record->task_submission?->status, ['rejected', 'submitted', 'reviewed'])
                    )
                    ->modalHeading(fn (TaskAssignment $record) =>
                        ($record->task_submission ? 'Update Submission: ' : 'Submit Deliverable: ') . ($record->task?->title ?? 'Task')
                    )
                    ->modalWidth('2xl')
                    ->fillForm(fn (TaskAssignment $record): array => [
                        'attachment' => $record->task_submission?->submission_file,
                        'link'       => $record->task_submission?->submission_text,
                    ])
                    ->form([
                        Placeholder::make('deadline_notice')
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
                                    : ($diff === 0 ? '🔥 This task is due today!' : '📅 Due Date: ' . \Carbon\Carbon::parse($due)->format('M d, Y'));
                                return new \Illuminate\Support\HtmlString(
                                    "<div style='padding:12px 16px; border-radius:10px; border-left:4px solid {$color}; background:rgba(0,0,0,0.25); color:{$color}; font-size:13px; font-weight:600;'>{$msg}</div>"
                                );
                            }),

                        FileUpload::make('attachment')
                            ->label('Upload File Deliverable')
                            ->helperText('Accepted file formats: PDF, ZIP, images, DOCX, XLSX')
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
                            ->label('Project / Repository URL')
                            ->url()
                            ->placeholder('https://github.com/...')
                            ->helperText('Paste your GitHub repository, live demo, Figma design, or Google Drive link.'),

                        Placeholder::make('submission_notice')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                "<div style='padding:12px 16px; border-radius:10px; background:#090e1c; border:1px solid #222a3d; color:#93c5fd; font-size:12px; line-height:1.5;'>
                                    ℹ️ Once submitted, the administration team will review and evaluate your deliverables. You can review feedback or resubmit if requested.
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
                            ->title('Deliverables Submitted Successfully! 🎉')
                            ->body('Your work has been submitted for evaluation.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No Tasks Found')
            ->emptyStateDescription('There are currently no tasks matching the selected filter or tab. Switch tabs above or check back later for newly assigned deliverables.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
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
