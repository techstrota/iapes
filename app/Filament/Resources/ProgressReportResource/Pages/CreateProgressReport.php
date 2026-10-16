<?php

namespace App\Filament\Resources\ProgressReportResource\Pages;

use App\Filament\Resources\ProgressReportResource;
use App\Models\ReportManagement\ProgressReportIntern;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProgressReport extends CreateRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected static string $view = 'filament.progress-report.create-progress-report';

    protected static ?string $title = 'Create New Progress Report';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'in_progress';
        return $data;
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Generate Progress Report & Begin Evaluation')
            ->icon('heroicon-m-check-badge')
            ->color('primary');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Discard Changes');
    }

    protected function afterCreate(): void
    {
        $report = $this->record;
        $data = $this->form->getRawState();
        $mode = $data['selection_mode'] ?? 'intern';
        $internIds = [];

        if ($mode === 'intern') {
            $internIds = array_map('intval', $data['intern_ids'] ?? []);
        } elseif ($mode === 'batch' && !empty($data['batch_id'])) {
            $internIds = Intern::where('is_active', true)
                ->where('internship_batch_id', $data['batch_id'])
                ->pluck('id')
                ->toArray();
        } elseif ($mode === 'team' && !empty($data['team_id'])) {
            $internIds = Intern::where('is_active', true)
                ->where('intern_team_id', $data['team_id'])
                ->pluck('id')
                ->toArray();
        }

        foreach (array_unique($internIds) as $internId) {
            ProgressReportIntern::firstOrCreate([
                'progress_report_id' => $report->id,
                'intern_id'          => $internId,
            ], [
                'is_evaluated'       => false,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
