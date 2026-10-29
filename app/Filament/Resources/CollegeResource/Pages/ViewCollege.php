<?php

namespace App\Filament\Resources\CollegeResource\Pages;

use App\Filament\Resources\CollegeResource;
use App\Filament\Resources\CollegeEvaluationResource;
use App\Models\ReportManagement\CollegeEvaluation;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCollege extends ViewRecord
{
    protected static string $resource = CollegeResource::class;

    protected static string $view = 'filament.college.view-college';

    public string $activeBatchTab = 'active';
    public bool $showDeleteBatchModal = false;
    public ?int $batchIdToDelete = null;
    public ?string $batchTitleToDelete = null;

    public bool $showArchiveBatchModal = false;
    public ?int $batchIdToArchive = null;
    public ?string $batchTitleToArchive = null;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function setBatchTab(string $tab): void
    {
        $this->activeBatchTab = in_array($tab, ['active', 'archived']) ? $tab : 'active';
    }

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    public function downloadBatchPdf(int $batchId)
    {
        $evaluation = CollegeEvaluation::find($batchId);
        if (!$evaluation) {
            Notification::make()->title('Batch not found')->danger()->send();
            return null;
        }

        return CollegeEvaluationResource::downloadPdf($evaluation);
    }

    public function confirmArchiveBatch(int $batchId, string $title): void
    {
        $this->batchIdToArchive = $batchId;
        $this->batchTitleToArchive = $title;
        $this->showArchiveBatchModal = true;
    }

    public function closeArchiveBatchModal(): void
    {
        $this->showArchiveBatchModal = false;
        $this->batchIdToArchive = null;
        $this->batchTitleToArchive = null;
    }

    public function executeArchiveBatch(): void
    {
        if (!$this->batchIdToArchive) {
            return;
        }

        $batch = CollegeEvaluation::find($this->batchIdToArchive);
        if ($batch) {
            $title = $batch->batch_title ?: 'Evaluation Batch';
            $batch->archive();

            Notification::make()
                ->title('Batch Archived')
                ->body("'{$title}' has been moved to Archive.")
                ->info()
                ->send();
        }

        $this->closeArchiveBatchModal();
    }

    public function archiveBatch(int $batchId): void
    {
        $batch = CollegeEvaluation::find($batchId);
        if ($batch) {
            $title = $batch->batch_title ?: 'Evaluation Batch';
            $batch->archive();

            Notification::make()
                ->title('Batch Archived')
                ->body("'{$title}' has been moved to Archive.")
                ->info()
                ->send();
        }
    }

    public function unarchiveBatch(int $batchId): void
    {
        $batch = CollegeEvaluation::find($batchId);
        if ($batch) {
            $title = $batch->batch_title ?: 'Evaluation Batch';
            $batch->unarchive();

            Notification::make()
                ->title('Batch Restored')
                ->body("'{$title}' has been restored to Active batches.")
                ->success()
                ->send();
        }
    }

    public function confirmDeleteBatch(int $batchId, string $title): void
    {
        $this->batchIdToDelete = $batchId;
        $this->batchTitleToDelete = $title;
        $this->showDeleteBatchModal = true;
    }

    public function closeDeleteBatchModal(): void
    {
        $this->showDeleteBatchModal = false;
        $this->batchIdToDelete = null;
        $this->batchTitleToDelete = null;
    }

    public function executeDeleteBatch(): void
    {
        if (!$this->batchIdToDelete) {
            return;
        }

        $batch = CollegeEvaluation::find($this->batchIdToDelete);
        if ($batch) {
            $title = $batch->batch_title ?: 'Evaluation Batch';
            $batch->delete();

            Notification::make()
                ->title('Batch Removed')
                ->body("'{$title}' has been deleted successfully.")
                ->success()
                ->send();
        }

        $this->closeDeleteBatchModal();
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'activeBatchTab'        => $this->activeBatchTab,
            'showDeleteBatchModal'  => $this->showDeleteBatchModal,
            'batchIdToDelete'       => $this->batchIdToDelete,
            'batchTitleToDelete'    => $this->batchTitleToDelete,
            'showArchiveBatchModal' => $this->showArchiveBatchModal,
            'batchIdToArchive'      => $this->batchIdToArchive,
            'batchTitleToArchive'   => $this->batchTitleToArchive,
        ]);
    }
}
