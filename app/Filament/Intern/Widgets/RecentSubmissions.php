<?php

namespace App\Filament\Intern\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\TaskManagement\TaskSubmission;
use Illuminate\Support\Facades\Auth;

class RecentSubmissions extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Recent Task Submissions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TaskSubmission::query()
                    ->where('intern_id', Auth::id())
                    ->with('task')
                    ->latest('submitted_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('task.title')
                    ->label('Task')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->description(fn ($record) => $record->task?->due_date ? 'Deadline: ' . \Carbon\Carbon::parse($record->task->due_date)->format('M d, Y') : null),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->date('M d, Y · h:i A')
                    ->sortable()
                    ->color('gray')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Review Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'submitted' => 'Under Review',
                        'approved'  => 'Approved',
                        'rejected'  => 'Needs Revision',
                        'reviewed'  => 'Reviewed',
                        default     => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        'reviewed'  => 'info',
                        'submitted' => 'warning',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ?? '—')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('marks')
                    ->label('Score')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . ' / 100' : '—')
                    ->color(fn ($state) => $state !== null
                        ? ($state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                        : 'gray'
                    )
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('submission_file')
                    ->label('Deliverable')
                    ->icon(fn ($state) => $state ? 'heroicon-m-arrow-down-tray' : 'heroicon-m-minus')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->url(fn ($record) => $record->submission_file
                        ? asset('storage/' . $record->submission_file)
                        : null
                    )
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_feedback')
                    ->label('Evaluation & Feedback')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->size(\Filament\Support\Enums\ActionSize::Small)
                    ->color('info')
                    ->visible(fn ($record) => filled($record->admin_feedback) || $record->marks !== null || filled($record->grade))
                    ->modalHeading(fn ($record) => 'Evaluation: ' . ($record->task?->title ?? 'Task'))
                    ->modalWidth('lg')
                    ->modalContent(fn ($record) => new \Illuminate\Support\HtmlString(
                        '<div style="padding: 6px; display: flex; flex-direction: column; gap: 16px;">'
                        . '<div style="display: flex; gap: 12px; flex-wrap: wrap;">'
                        .   ($record->marks !== null
                                ? '<div style="background:#090e1c; border:1px solid #222a3d; border-radius:10px; padding:8px 16px;">'
                                  . '<span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#8e909f; letter-spacing:0.05em;">Score</span><br>'
                                  . '<strong style="font-size:18px; color:#ffffff;">' . e($record->marks) . ' <span style="font-size:13px; color:#8e909f;">/ 100</span></strong>'
                                  . '</div>'
                                : '')
                        .   ($record->grade
                                ? '<div style="background:#090e1c; border:1px solid #222a3d; border-radius:10px; padding:8px 16px;">'
                                  . '<span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#8e909f; letter-spacing:0.05em;">Grade</span><br>'
                                  . '<strong style="font-size:18px; color:#10b981;">' . e($record->grade) . '</strong>'
                                  . '</div>'
                                : '')
                        . '</div>'
                        . ($record->admin_feedback
                            ? '<div style="background:#090e1c; border:1px solid #222a3d; border-radius:12px; padding:16px;">'
                              . '<span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#8e909f; letter-spacing:0.05em; display:block; margin-bottom:8px;">Admin Notes</span>'
                              . '<p style="color:#dae2fd; font-size:14px; line-height:1.6; margin:0;">' . nl2br(e($record->admin_feedback)) . '</p>'
                              . '</div>'
                            : '<p style="color:#8e909f; font-size:13px; margin:0;">No written feedback provided yet.</p>')
                        . '</div>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->striped(false)
            ->paginated([5, 10, 25]);
    }
}