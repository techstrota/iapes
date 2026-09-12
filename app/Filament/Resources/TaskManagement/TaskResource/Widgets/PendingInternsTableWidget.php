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

    protected static ?string $heading = '⏳ Pending Interns';
    
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('team.team_name')
                    ->label('Team')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('batch.batch_name')
                    ->label('Batch')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->state(fn () => $this->record?->due_date?->format('M d, Y') ?? 'No deadline')
                    ->color(fn () => $this->record?->is_overdue ? 'danger' : 'gray')
                    ->weight(fn () => $this->record?->is_overdue ? 'bold' : 'regular'),
            ])
            ->emptyStateHeading('🎉 All assigned interns have submitted!')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
