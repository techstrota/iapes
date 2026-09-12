<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Pages;

use App\Filament\Resources\TaskManagement\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    // After create → go to the task's detail (ViewTask) page
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record->task_id]);
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();

        // Create the assignment record
        $this->record->assignments()->create([
            'assigned_type' => $data['assigned_type'],
            'intern_id'     => $data['assigned_type'] === 'intern' ? ($data['intern_id'] ?? null) : null,
            'team_id'       => $data['assigned_type'] === 'team'   ? ($data['team_id']   ?? null) : null,
            'batch_id'      => $data['assigned_type'] === 'batch'  ? ($data['batch_id']  ?? null) : null,
        ]);
    }
}
