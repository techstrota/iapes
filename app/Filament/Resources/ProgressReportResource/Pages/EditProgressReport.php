<?php

namespace App\Filament\Resources\ProgressReportResource\Pages;

use App\Filament\Resources\ProgressReportResource;
use App\Models\ReportManagement\ProgressReportIntern;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgressReport extends EditRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected static string $view = 'filament.progress-report.edit-progress-report';

    protected static ?string $title = 'Edit Progress Report Details';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['selection_mode'] ?? '') === 'intern') {
            $data['intern_ids'] = $this->record->reportInterns()->pluck('intern_id')->toArray();
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_report')
                ->label('View & Evaluate Workspace')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn () => ProgressReportResource::getUrl('view', ['record' => $this->record])),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Save Changes & Continue Evaluation')
            ->icon('heroicon-m-check-circle')
            ->color('primary');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Discard Changes');
    }

    protected function afterSave(): void
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

        // Add any newly selected interns to reportInterns
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
