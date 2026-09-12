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
    protected static ?string $heading = 'My Recent Submissions';

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
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->date('d M, Y')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'submitted' => 'Under Review',
                        'approved'  => '✅ Approved',
                        'rejected'  => '❌ Rejected',
                        'reviewed'  => 'Reviewed',
                        default     => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'reviewed' => 'info',
                        'submitted' => 'warning',
                        default    => 'gray',
                    })
                    ->description(fn ($record) =>
                        $record->admin_feedback
                            ? \Illuminate\Support\Str::limit($record->admin_feedback, 80)
                            : null
                    ),

                // Grade — NEW
                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->formatStateUsing(fn ($state) => $state ?? '—')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->weight('bold'),

                // Score — NEW
                Tables\Columns\TextColumn::make('marks')
                    ->label('Score')
                    ->formatStateUsing(fn ($state) => $state !== null ? $state . ' / 100' : '—')
                    ->color(fn ($state) => $state !== null
                        ? ($state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                        : 'gray'
                    ),

                Tables\Columns\IconColumn::make('submission_file')
                    ->label('File')
                    ->icon(fn ($state) => $state ? 'heroicon-m-document-text' : 'heroicon-m-minus')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->url(fn ($record) => $record->submission_file
                        ? asset('storage/' . $record->submission_file)
                        : null
                    )
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_feedback')
                    ->label('Feedback')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->size(\Filament\Support\Enums\ActionSize::Small)
                    ->color('info')
                    ->visible(fn ($record) => filled($record->admin_feedback))
                    ->modalHeading(fn ($record) => 'Feedback: ' . ($record->task?->title ?? 'Task'))
                    ->modalContent(fn ($record) => new \Illuminate\Support\HtmlString(
                        '<div style="padding:16px;font-size:14px;line-height:1.7;">'
                        . '<div style="margin-bottom:12px;">'
                        .   ($record->grade || $record->marks !== null
                                ? '<strong style="font-size:16px;color:#10b981;">'
                                  . implode('  ·  ', array_filter([
                                      $record->grade ? 'Grade: ' . $record->grade : null,
                                      $record->marks !== null ? 'Score: ' . $record->marks . '/100' : null,
                                  ]))
                                  . '</strong><br>'
                                : '')
                        . '</div>'
                        . '<p>' . nl2br(e($record->admin_feedback)) . '</p>'
                        . '</div>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->striped(false)
            ->paginated([5, 10, 25]);
    }
}