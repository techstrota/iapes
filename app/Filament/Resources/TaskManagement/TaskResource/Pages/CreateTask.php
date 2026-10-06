<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Pages;

use App\Filament\Resources\TaskManagement\TaskResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.task-management.create-task';

    protected static ?string $title = 'Create New Task & Assignment';

    public function getSubheading(): ?string
    {
        return 'Define deliverable criteria, attach resources, and target specific cohorts, project teams, or multiple interns.';
    }

    public function mount(): void
    {
        parent::mount();

        $param = request()->query('intern_ids');
        if ($param) {
            $ids = is_array($param) ? $param : explode(',', (string) $param);
            $internIds = array_values(array_filter(array_map('intval', $ids)));

            if (!empty($internIds)) {
                $this->form->fill(array_merge($this->form->getRawState(), [
                    'assigned_type' => 'intern',
                    'intern_ids'    => $internIds,
                ]));

                Notification::make()
                    ->title(count($internIds) . ' candidate(s) preselected from Interns Directory')
                    ->body('Fill in the task details. Deliverables will be assigned automatically to these preselected candidates upon publishing.')
                    ->info()
                    ->send();
            }
        }
    }

    // After create → go to the task's detail (ViewTask) page
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record->task_id]);
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Publish & Assign Task')
            ->icon('heroicon-m-paper-airplane')
            ->color('primary');
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Publish & Create Another');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Discard Changes');
    }

    protected function afterCreate(): void
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

        foreach ($uniqueInternIds as $internId) {
            $this->record->assignments()->create([
                'assigned_type' => 'intern',
                'intern_id'     => $internId,
            ]);
        }

        $count = $uniqueInternIds->count();

        Notification::make()
            ->title('Task published & assigned successfully! 🚀')
            ->body("Individually assigned to {$count} " . ($count === 1 ? 'intern' : 'interns') . '.')
            ->success()
            ->send();
    }
}
