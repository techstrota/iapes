<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Pages;

use App\Filament\Resources\TaskManagement\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    // After save → go back to the task detail page
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record->task_id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Fill the form with existing assignment + status data
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $assignment = $this->record->assignments()->first();

        if ($assignment) {
            $data['assigned_type'] = $assignment->assigned_type;
            $data['intern_id']     = $assignment->intern_id;
            $data['team_id']       = $assignment->team_id;
            $data['batch_id']      = $assignment->batch_id;
        }

        // 'status' is already on the Task model, no extra mapping needed

        return $data;
    }

    // Update the assignment record after the task is saved
    protected function afterSave(): void
    {
        $data = $this->form->getRawState();

        $this->record->assignments()->updateOrCreate(
            ['task_id' => $this->record->task_id],
            [
                'assigned_type' => $data['assigned_type'],
                'intern_id'     => $data['assigned_type'] === 'intern' ? ($data['intern_id'] ?? null) : null,
                'team_id'       => $data['assigned_type'] === 'team'   ? ($data['team_id']   ?? null) : null,
                'batch_id'      => $data['assigned_type'] === 'batch'  ? ($data['batch_id']  ?? null) : null,
            ]
        );
    }
}
