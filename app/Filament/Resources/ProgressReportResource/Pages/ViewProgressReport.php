<?php

namespace App\Filament\Resources\ProgressReportResource\Pages;

use App\Filament\Resources\ProgressReportResource;
use App\Models\ReportManagement\ProgressReport;
use App\Models\ReportManagement\ProgressReportIntern;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\DailyLogbook;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProgressReport extends ViewRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected static string $view = 'filament.progress-report.view-progress-report';

    public ?int $selectedInternId = null;
    public string $weeklySummary = '';
    public string $supervisorRemarks = '';
    public string $searchQuery = '';
    public string $statusFilter = 'all'; // 'all', 'evaluated', 'pending'
    public ?string $lastSavedAt = null;

    // PDF Export Modal State
    public bool $showPdfModal = false;
    public string $pdfExportMode = 'single_pdf';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $firstReportIntern = $this->record->reportInterns()->first();
        if ($firstReportIntern) {
            $this->selectIntern($firstReportIntern->intern_id);
        }
    }

    public function selectIntern(int $internId): void
    {
        $this->selectedInternId = $internId;

        $repIntern = ProgressReportIntern::where('progress_report_id', $this->record->id)
            ->where('intern_id', $internId)
            ->first();

        $this->weeklySummary = $repIntern?->weekly_summary ?? '';
        $this->supervisorRemarks = $repIntern?->supervisor_remarks ?? '';
        $this->lastSavedAt = $repIntern?->evaluated_at ? $repIntern->evaluated_at->format('d M Y, h:i A') : null;
    }

    public function saveRemarks(): void
    {
        if (!$this->selectedInternId) {
            return;
        }

        $repIntern = ProgressReportIntern::where('progress_report_id', $this->record->id)
            ->where('intern_id', $this->selectedInternId)
            ->first();

        if ($repIntern) {
            $isEval = filled(trim($this->weeklySummary)) || filled(trim($this->supervisorRemarks));

            $repIntern->update([
                'weekly_summary'     => $this->weeklySummary,
                'supervisor_remarks' => $this->supervisorRemarks,
                'is_evaluated'       => $isEval,
                'evaluated_at'       => now(),
                'evaluated_by'       => auth()->id(),
            ]);

            $this->lastSavedAt = now()->format('d M Y, h:i A');

            // Check if all interns are evaluated to update overall report status
            $pendingCount = $this->record->reportInterns()->where('is_evaluated', false)->count();
            if ($pendingCount === 0) {
                $this->record->update(['status' => 'completed']);
            } else {
                $this->record->update(['status' => 'in_progress']);
            }

            Notification::make()
                ->title('Evaluation Saved Successfully')
                ->body("Weekly summary and remarks saved for {$repIntern->intern?->name}.")
                ->success()
                ->send();
        }
    }

    public function autoDraftSummary(): void
    {
        if (!$this->selectedInternId) {
            return;
        }

        $logbooks = DailyLogbook::where('intern_id', $this->selectedInternId)
            ->whereBetween('date', [
                $this->record->week_start_date->toDateString(),
                $this->record->week_end_date->toDateString()
            ])
            ->orderBy('date', 'asc')
            ->get();

        if ($logbooks->isEmpty()) {
            Notification::make()
                ->title('No Daily Work Logs Found')
                ->body('The intern has not submitted any daily logbooks for this week yet.')
                ->warning()
                ->send();
            return;
        }

        $summaryParts = [];
        $totalHours = 0;

        foreach ($logbooks as $log) {
            $dateFormatted = Carbon::parse($log->date)->format('D, d M');
            $tasks = trim((string) $log->tasks_completed);
            $totalHours += (float) ($log->hours_worked ?? 8.0);

            if (filled($tasks)) {
                $summaryParts[] = "• [{$dateFormatted}] " . strip_tags($tasks);
            }
        }

        $attSummaryParts = [];
        $attCounts = Attendance::where('intern_id', $this->selectedInternId)
            ->whereBetween('date', [
                $this->record->week_start_date->toDateString(),
                $this->record->week_end_date->toDateString()
            ])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        foreach ($attCounts as $st => $cnt) {
            $attSummaryParts[] = ucfirst($st) . ": {$cnt}";
        }

        $attText = !empty($attSummaryParts) ? " | Attendance: " . implode(', ', $attSummaryParts) : "";
        $draft = "Weekly Work Summary (Total Logged Hours: {$totalHours} hrs{$attText}):\n" . implode("\n", $summaryParts);

        $this->weeklySummary = $draft;

        Notification::make()
            ->title('Summary Drafted from Tasks & Attendance')
            ->body('Weekly summary generated from intern\'s logged tasks and attendance. You can edit and save.')
            ->info()
            ->send();
    }

    public function openPdfModal(): void
    {
        $this->pdfExportMode = 'single_pdf';
        $this->showPdfModal = true;
    }

    public function closePdfModal(): void
    {
        $this->showPdfModal = false;
    }

    public function executePdfDownload()
    {
        $mode = $this->pdfExportMode ?: 'single_pdf';
        $this->showPdfModal = false;

        return ProgressReportResource::processPdfDownload($this->record, $mode, $this->selectedInternId);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_list')
                ->label('Back to Directory')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(ProgressReportResource::getUrl('index')),
        ];
    }
}
