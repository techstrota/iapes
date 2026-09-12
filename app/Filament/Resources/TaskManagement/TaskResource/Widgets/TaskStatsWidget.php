<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Widgets;

use App\Models\TaskManagement\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class TaskStatsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record instanceof Task) {
            return [];
        }

        return [
            Stat::make('Total Assigned', $this->record->total_assigned_count)
                ->description('Total interns assigned')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
            Stat::make('Submitted', $this->record->submitted_count)
                ->description('Submissions received')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('primary'),
            Stat::make('Pending', $this->record->pending_count)
                ->description('Interns yet to submit')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Evaluated', $this->record->evaluated_count)
                ->description('Submissions graded')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
