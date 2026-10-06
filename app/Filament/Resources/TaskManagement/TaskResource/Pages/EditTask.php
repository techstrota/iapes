<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Pages;

use App\Filament\Resources\TaskManagement\TaskResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.task-management.edit-task';

    public function getTitle(): string
    {
        return 'Edit Task: ' . ($this->record->title ?: 'Task');
    }

    public function getSubheading(): ?string
    {
        return 'Update task deliverables, due dates, priority SLA, and audience target scope.';
    }

    // After save → go back to the task detail page
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record->task_id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Delete Task')
                ->color('danger'),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Save Changes & Update Assignment')
            ->icon('heroicon-m-check')
            ->color('primary');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Discard Changes');
    }

    // Fill the form with existing assignment data
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $assignedInternIds = $this->record->assigned_interns->pluck('id')->values()->toArray();

        $data['assigned_type'] = 'intern';
        $data['intern_ids']    = $assignedInternIds;
        $data['team_ids']      = [];
        $data['batch_ids']     = [];

        return $data;
    }

    // Update the assignment records after the task is saved
    protected function afterSave(): void
    {
        $data = $this->form->getRawState();
        $type = $data['assigned_type'] ?? 'intern';

        $resolvedInternIds = collect();

        if ($type === 'intern') {
            $resolvedInternIds = collect(array_filter((array) ($data['intern_ids'] ?? [])));
        } elseif ($type === 'team') {
            $teamIds = array_filter((array) ($data['team_ids'] ?? []));
            $resolvedInternIds = \App\Models\InternManagement\Intern::whereIn('intern_team_id', $teamIds)
                ->where('is_active', true)
                ->pluck('id');
        } elseif ($type === 'batch') {
            $batchIds = array_filter((array) ($data['batch_ids'] ?? []));
            $resolvedInternIds = \App\Models\InternManagement\Intern::whereIn('internship_batch_id', $batchIds)
                ->where('is_active', true)
                ->pluck('id');
        }

        $uniqueInternIds = $resolvedInternIds->map(fn ($id) => (int) $id)->filter()->unique()->values();

        // Synchronize assignments: delete existing assignments and create individual intern records
        $this->record->assignments()->delete();

        foreach ($uniqueInternIds as $internId) {
            $this->record->assignments()->create([
                'assigned_type' => 'intern',
                'intern_id'     => $internId,
            ]);
        }

        $count = $uniqueInternIds->count();

        Notification::make()
            ->title('Task updated & assignments synchronized! 🚀')
            ->body("Synchronized individual assignments for {$count} " . ($count === 1 ? 'intern' : 'interns') . '.')
            ->success()
            ->send();
    }
}
