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

    // Dropdown / Collapsible Daily Tasks & Attendance Log Section
    public bool $showDailyTasksSection = false; // By default displayed OFF

    // PDF Export Modal State
    public bool $showPdfModal = false;
    public string $pdfExportMode = 'single_pdf';

    // Daily Timing, Task & Attendance Quick-Edit Modal State
    public bool $showTimingModal = false;
    public ?string $timingDate = null;
    public ?string $timingDateFormatted = null;
    public ?string $timingStatus = 'present';
    public ?string $timingStartTime = null;
    public ?string $timingEndTime = null;
    public ?string $timingNote = null;
    public ?int $timingLateMinutes = null;
    public ?string $timingScheduledStart = null;
    public ?string $timingScheduledEnd = null;
    public ?string $timingTasksCompleted = null;
    public ?string $timingSupervisorRemarks = null;
    public ?float $timingHoursWorked = 8.0;

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
        $repIntern = ProgressReportIntern::where('progress_report_id', $this->record->id)
            ->where('intern_id', $internId)
            ->whereHas('intern', function ($q) {
                $q->where('is_active', true)->whereNull('cohort_archive_name');
            })
            ->first();

        if ($repIntern) {
            $this->selectedInternId = $internId;
            $this->weeklySummary = $repIntern->weekly_summary ?? '';
            $this->supervisorRemarks = $repIntern->supervisor_remarks ?? '';
            $this->lastSavedAt = $repIntern->evaluated_at ? $repIntern->evaluated_at->format('d M Y, h:i A') : null;
        }
    }

    public function saveRemarks(): void
    {
        if (!$this->selectedInternId) {
            return;
        }

        $repIntern = ProgressReportIntern::where('progress_report_id', $this->record->id)
            ->where('intern_id', $this->selectedInternId)
            ->whereHas('intern', function ($q) {
                $q->where('is_active', true)->whereNull('cohort_archive_name');
            })
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

            // Check if all active interns are evaluated to update overall report status
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

        $lateTotalMinutes = (int) Attendance::where('intern_id', $this->selectedInternId)
            ->whereBetween('date', [
                $this->record->week_start_date->toDateString(),
                $this->record->week_end_date->toDateString()
            ])
            ->where('status', 'late')
            ->sum('late_minutes');

        foreach ($attCounts as $st => $cnt) {
            if ($st === 'late' && $lateTotalMinutes > 0) {
                $hours = floor($lateTotalMinutes / 60);
                $mins = $lateTotalMinutes % 60;
                $durStr = ($hours > 0 ? "{$hours}h " : "") . "{$mins}m";
                $attSummaryParts[] = "Late: {$cnt} ({$durStr} total delay)";
            } else {
                $attSummaryParts[] = ucfirst($st) . ": {$cnt}";
            }
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

    public function updatedShowDailyTasksSection($val): void
    {
        $this->showDailyTasksSection = filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    public function toggleDailyTasksSection(): void
    {
        $this->showDailyTasksSection = !$this->showDailyTasksSection;
    }

    public function openTimingModal(string $date): void
    {
        if (!$this->selectedInternId) {
            return;
        }

        $this->timingDate = $date;
        $this->timingDateFormatted = Carbon::parse($date)->format('D, d M Y');

        $intern = Intern::with('batch')->find($this->selectedInternId);
        $batch = $intern?->batch ?? $this->record->batch;
        $schedStart = '11:00 AM';
        $schedEnd = '04:00 PM';

        if ($batch && filled($batch->batch_timing)) {
            $parts = preg_split('/\s+(?:to|-)\s+/i', trim($batch->batch_timing));
            if (count($parts) >= 2) {
                try {
                    $schedStart = Carbon::parse(trim($parts[0]))->format('g:i A');
                    $schedEnd = Carbon::parse(trim($parts[1]))->format('g:i A');
                } catch (\Throwable $e) {
                    $schedStart = trim($parts[0]);
                    $schedEnd = trim($parts[1]);
                }
            }
        }

        $this->timingScheduledStart = $schedStart;
        $this->timingScheduledEnd = $schedEnd;

        // 1. Load Attendance Record
        $att = Attendance::where('intern_id', $this->selectedInternId)
            ->whereDate('date', $date)
            ->first();

        if ($att) {
            $this->timingStatus = $att->status ?: 'present';
            $this->timingStartTime = $att->start_time ?: $schedStart;
            $this->timingEndTime = $att->end_time ?: $schedEnd;
            $this->timingNote = $att->note;
            $this->timingLateMinutes = $att->late_minutes;
        } else {
            $this->timingStatus = 'present';
            $this->timingStartTime = $schedStart;
            $this->timingEndTime = $schedEnd;
            $this->timingNote = null;
            $this->timingLateMinutes = null;
        }

        // 2. Load DailyLogbook Record (Tasks, Deliverables & Supervisor Remarks)
        $log = DailyLogbook::where('intern_id', $this->selectedInternId)
            ->whereDate('date', $date)
            ->first();

        if ($log) {
            $this->timingTasksCompleted = $log->tasks_completed;
            $this->timingSupervisorRemarks = $log->supervisor_remarks;
            $this->timingHoursWorked = (float) ($log->hours_worked ?? 8.0);
        } else {
            $this->timingTasksCompleted = '';
            $this->timingSupervisorRemarks = '';
            $this->timingHoursWorked = 8.0;
        }

        $this->calculateTimingDelay();
        $this->showTimingModal = true;
    }

    public function updatedTimingStartTime(): void
    {
        $this->calculateTimingDelay();
    }

    public function setTimingStatus(string $status): void
    {
        $this->timingStatus = $status;
        $this->calculateTimingDelay();
    }

    protected function calculateTimingDelay(): void
    {
        if ($this->timingStatus !== 'late') {
            $this->timingLateMinutes = null;
            return;
        }

        if (filled($this->timingStartTime) && filled($this->timingScheduledStart)) {
            try {
                $sched = Carbon::parse($this->timingScheduledStart);
                $actual = Carbon::parse($this->timingStartTime);
                $diff = $sched->diffInMinutes($actual, false);
                $this->timingLateMinutes = $diff > 0 ? (int) $diff : 0;
            } catch (\Throwable $e) {
                $this->timingLateMinutes = null;
            }
        } else {
            $this->timingLateMinutes = null;
        }
    }

    public function closeTimingModal(): void
    {
        $this->showTimingModal = false;
        $this->timingDate = null;
        $this->timingTasksCompleted = null;
        $this->timingSupervisorRemarks = null;
        $this->timingHoursWorked = 8.0;
    }

    public function saveTiming(): void
    {
        if (!$this->selectedInternId || !$this->timingDate) {
            return;
        }

        // 1. Save or Update Attendance Record (Directly reflected in Attendance Module)
        $att = Attendance::firstOrNew([
            'intern_id' => $this->selectedInternId,
            'date'      => $this->timingDate,
        ]);

        $status = $this->timingStatus ?: 'present';
        $att->status = $status;
        $att->start_time = filled($this->timingStartTime) ? trim($this->timingStartTime) : null;
        $att->end_time = filled($this->timingEndTime) ? trim($this->timingEndTime) : null;
        $att->note = filled($this->timingNote) ? trim($this->timingNote) : null;

        if ($status === 'late') {
            $this->calculateTimingDelay();
            $att->late_minutes = $this->timingLateMinutes ?? 0;
        } else {
            $att->late_minutes = null;
        }

        $att->save();

        // 2. Save or Update DailyLogbook Record (Directly reflected in Intern Logbook Module)
        $log = DailyLogbook::firstOrNew([
            'intern_id' => $this->selectedInternId,
            'date'      => $this->timingDate,
        ]);

        $hasTasks = filled(trim($this->timingTasksCompleted ?? ''));
        $hasRemarks = filled(trim($this->timingSupervisorRemarks ?? ''));

        if ($hasTasks || $hasRemarks) {
            $log->tasks_completed = trim($this->timingTasksCompleted ?? '');
            $log->supervisor_remarks = $hasRemarks ? trim($this->timingSupervisorRemarks) : null;
            $log->hours_worked = filled($this->timingHoursWorked) ? (float) $this->timingHoursWorked : ($log->hours_worked ?? 8.0);

            if (!$log->exists) {
                $log->title = 'Daily Work Note';
                $log->status = 'approved';
            }
            $log->save();
        } elseif ($log->exists) {
            $log->tasks_completed = '';
            $log->supervisor_remarks = null;
            $log->save();
        }

        $this->showTimingModal = false;

        Notification::make()
            ->title('Daily Record Synchronized')
            ->body('Attendance, timings, tasks & deliverables, and supervisor remarks updated across Attendance & Daily Logbook modules for ' . Carbon::parse($this->timingDate)->format('d M Y') . '.')
            ->success()
            ->send();
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
