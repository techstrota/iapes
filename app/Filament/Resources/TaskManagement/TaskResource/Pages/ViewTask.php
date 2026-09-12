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

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('markCompleted')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->hidden(fn (Task $record) => $record->status === 'completed')
                ->action(function (Task $record) {
                    $record->update(['status' => 'completed']);
                    Notification::make()
                        ->title('Task marked as completed.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TaskResource\Widgets\TaskStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TaskResource\Widgets\PendingInternsTableWidget::class,
        ];
    }
}
