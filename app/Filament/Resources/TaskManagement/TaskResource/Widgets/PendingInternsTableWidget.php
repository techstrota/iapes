<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Widgets;

use App\Models\InternManagement\Intern;
use App\Models\TaskManagement\Task;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PendingInternsTableWidget extends BaseWidget
{
    public ?Model $record = null;

    protected static ?string $heading = 'Pending Submissions';
    
    // Put it full width
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        if (!$this->record instanceof Task) {
            return Intern::query()->where('id', 0); // empty
        }

        // Get all intern IDs who have submitted
        $submittedInternIds = $this->record->submissions()->pluck('intern_id');

        // We need all assigned intern IDs
        $assignedInterns = $this->record->assigned_interns;
        
        $pendingInternIds = $assignedInterns->pluck('id')->diff($submittedInternIds)->values();

        return Intern::query()->whereIn('id', $pendingInternIds);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Intern Name')
                    ->weight('bold')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->description(fn (Intern $record): ?string => $record->email ?: ($record->intern_id ?? null))
                    ->searchable(),

                Tables\Columns\TextColumn::make('team.team_name')
                    ->label('Project Team')
                    ->badge()
                    ->color('info')
                    ->placeholder('Unassigned'),

                Tables\Columns\TextColumn::make('batch.batch_name')
                    ->label('Batch')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Unassigned'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Submission Deadline')
                    ->badge()
                    ->state(function () {
                        if (!$this->record?->due_date) {
                            return 'No Deadline';
                        }
                        $due = $this->record->due_date;
                        if ($this->record->is_overdue) {
                            return 'Overdue • ' . $due->format('M d, Y');
                        }
                        if ($due->isToday()) {
                            return 'Due Today • ' . $due->format('M d, Y');
                        }
                        return 'Due • ' . $due->format('M d, Y');
                    })
                    ->color(function () {
                        if (!$this->record?->due_date) return 'gray';
                        if ($this->record->is_overdue) return 'danger';
                        if ($this->record->due_date->isToday()) return 'warning';
                        return 'info';
                    }),
            ])
            ->emptyStateHeading('All Assigned Interns Have Submitted')
            ->emptyStateDescription('There are currently no pending submissions for this task. Every assigned intern has submitted their deliverable.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
