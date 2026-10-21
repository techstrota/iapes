<?php

namespace App\Filament\Resources\ProgressReportResource\Pages;

use App\Filament\Resources\ProgressReportResource;
use App\Models\ReportManagement\ProgressReportIntern;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use App\Models\ReportManagement\ProgressReport;

class CreateProgressReport extends CreateRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected static string $view = 'filament.progress-report.create-progress-report';

    protected static ?string $title = 'Create New Progress Report';

    public function mount(): void
    {
        parent::mount();

        if (request()->query('type') === 'monthly') {
            $this->form->fill([
                'report_type'         => 'monthly',
                'title'               => now()->format('F Y') . ' Monthly / Final Internship Review',
                'week_start_date'     => now()->startOfMonth()->toDateString(),
                'week_end_date'       => now()->endOfMonth()->toDateString(),
                'selection_mode'      => 'intern',
                'evaluation_criteria' => ProgressReport::getDefaultEvaluationCriteria(),
            ]);
        }
    }

    public function getTitle(): string
    {
        return request()->query('type') === 'monthly' || ($this->data['report_type'] ?? '') === 'monthly'
            ? 'Create Monthly / Final Review Report'
            : 'Create New Progress Report';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'in_progress';

        if (($data['report_type'] ?? '') === 'monthly' && empty($data['evaluation_criteria'])) {
            $data['evaluation_criteria'] = ProgressReport::getDefaultEvaluationCriteria();
        }

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
            $rawIds = array_map('intval', $data['intern_ids'] ?? []);
            $internIds = Intern::active()
                ->whereIn('id', $rawIds)
                ->pluck('id')
                ->toArray();
        } elseif ($mode === 'batch' && !empty($data['batch_id'])) {
            $internIds = Intern::active()
                ->where('internship_batch_id', $data['batch_id'])
                ->pluck('id')
                ->toArray();
        } elseif ($mode === 'team' && !empty($data['team_id'])) {
            $internIds = Intern::active()
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
