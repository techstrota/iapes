<?php

namespace App\Filament\Resources\ProgressReportResource\Pages;

use App\Filament\Resources\ProgressReportResource;
use App\Models\ReportManagement\ProgressReport;
use App\Models\ReportManagement\ProgressReportIntern;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListProgressReports extends Page
{
    use WithPagination;

    protected static string $resource = ProgressReportResource::class;

    protected static string $view = 'filament.progress-report.list-progress-reports';

    protected static ?string $title = 'Progress Reports';

    public string $search = '';
    public string $statusFilter = 'all'; // 'all', 'in_progress', 'completed'
    public string $selectionModeFilter = 'all'; // 'all', 'intern', 'batch', 'team'
    public int $perPage = 12;

    // PDF Export Modal State
    public bool $showPdfModal = false;
    public ?int $selectedReportIdForPdf = null;
    public string $pdfExportMode = 'single_pdf';

    // Delete Modal State
    public bool $showDeleteModal = false;
    public ?int $reportIdToDelete = null;
    public ?string $reportTitleToDelete = null;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectionModeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openPdfModal(int $reportId): void
    {
        $this->selectedReportIdForPdf = $reportId;
        $this->pdfExportMode = 'single_pdf';
        $this->showPdfModal = true;
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
        $this->selectedReportIdForPdf = null;
    }

    public function executePdfDownload()
    {
        if (!$this->selectedReportIdForPdf) {
            return null;
        }

        $report = ProgressReport::find($this->selectedReportIdForPdf);
        if (!$report) {
            Notification::make()->title('Report not found')->danger()->send();
            $this->closePdfModal();
            return null;
        }

        $mode = $this->pdfExportMode ?: 'single_pdf';
        $this->showPdfModal = false;

        return ProgressReportResource::processPdfDownload($report, $mode);
    }

    public function confirmDelete(int $reportId, string $title): void
    {
        $this->reportIdToDelete = $reportId;
        $this->reportTitleToDelete = $title;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->reportIdToDelete = null;
        $this->reportTitleToDelete = null;
    }

    public function executeDelete(): void
    {
        if (!$this->reportIdToDelete) {
            return;
        }

        $report = ProgressReport::find($this->reportIdToDelete);
        if ($report) {
            $reportTitle = $report->title;
            // Delete child pivot records
            ProgressReportIntern::where('progress_report_id', $report->id)->delete();
            $report->delete();

            Notification::make()
                ->title('Progress Report Deleted')
                ->body("'{$reportTitle}' has been removed successfully.")
                ->success()
                ->send();
        }

        $this->closeDeleteModal();
        $this->resetPage();
    }

    public function getStats(): array
    {
        $totalReports = ProgressReport::count();
        $completedReports = ProgressReport::where('status', 'completed')->count();
        $inProgressReports = ProgressReport::where('status', '!=', 'completed')->count();
        $totalEnrolled = ProgressReportIntern::whereHas('intern', function ($q) {
            $q->where('is_active', true)->whereNull('cohort_archive_name');
        })->count();
        $totalEvaluated = ProgressReportIntern::where('is_evaluated', true)
            ->whereHas('intern', function ($q) {
                $q->where('is_active', true)->whereNull('cohort_archive_name');
            })->count();
        $evalPct = $totalEnrolled > 0 ? round(($totalEvaluated / $totalEnrolled) * 100) : 0;

        return [
            'total_reports'        => $totalReports,
            'completed_reports'    => $completedReports,
            'in_progress_reports'  => $inProgressReports,
            'total_enrolled'       => $totalEnrolled,
            'total_evaluated'      => $totalEvaluated,
            'eval_pct'             => $evalPct,
        ];
    }

    public function getReportsQuery()
    {
        $query = ProgressReport::with(['batch', 'team', 'creator', 'reportInterns']);

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('selection_mode', 'like', $term)
                  ->orWhereHas('batch', fn ($b) => $b->where('batch_name', 'like', $term))
                  ->orWhereHas('team', fn ($t) => $t->where('team_name', 'like', $term));
            });
        }

        if ($this->statusFilter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($this->statusFilter === 'in_progress') {
            $query->where('status', '!=', 'completed');
        }

        if ($this->selectionModeFilter !== 'all') {
            $query->where('selection_mode', $this->selectionModeFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function getViewData(): array
    {
        $reports = $this->getReportsQuery()->paginate($this->perPage);
        $stats = $this->getStats();

        return [
            'reports' => $reports,
            'stats'   => $stats,
        ];
    }
}
