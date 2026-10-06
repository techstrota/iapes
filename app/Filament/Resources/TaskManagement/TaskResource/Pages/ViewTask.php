<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Pages;

use App\Filament\Resources\TaskManagement\TaskResource;
use App\Models\TaskManagement\Task;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.task-management.view-task';

    public function markTaskCompleted(): void
    {
        $this->record->update(['status' => 'completed']);
        Notification::make()
            ->title('Task marked as completed.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TaskResource\Widgets\PendingInternsTableWidget::class,
        ];
    }
}
