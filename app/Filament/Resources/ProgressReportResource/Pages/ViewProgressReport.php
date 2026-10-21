<?php

namespace App\Filament\Resources\ProgressReportResource\Pages;

use App\Filament\Resources\ProgressReportResource;
use App\Models\ReportManagement\ProgressReport;
use App\Models\ReportManagement\ProgressReportIntern;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\DailyLogbook;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\TaskManagement\TaskAssignment;
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
    public array $criteriaRatings = [];
    public array $calculatedMetrics = [];
    public string $mentorFinalComments = '';
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

    // Pop-up Modal State for Individual Criterion Edit
    public bool $showCriterionModal = false;
    public ?string $modalCritKey = null;
    public string $modalCritName = '';
    public string $modalCritRating = 'Good';
    public string $modalCritRemarks = '';
    public ?array $modalCritMetric = null;

    // Pop-up Modal State for Final Mentor Comments Edit
    public bool $showMentorCommentsModal = false;
    public string $modalMentorComments = '';

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

            if ($this->record->isMonthly()) {
                // Calculate metrics for this intern
                $this->calculatedMetrics = $this->calculateMetricsForIntern($internId);

                $existingRatings = $repIntern->criteria_ratings ?? [];
                $criteriaList = $this->record->evaluation_criteria ?: ProgressReport::getDefaultEvaluationCriteria();
                $ratings = [];
                $isAlreadyEvaluated = (bool) $repIntern->is_evaluated && !empty($existingRatings);

                foreach ($criteriaList as $idx => $crit) {
                    $key = $crit['id'] ?? ('crit_' . $idx);
                    $metric = $this->resolveMetricForCriterion($key, $crit['name'] ?? '');

                    if ($isAlreadyEvaluated && isset($existingRatings[$key])) {
                        // Keep previously saved evaluations
                        $ratings[$key] = [
                            'name'        => $crit['name'] ?? 'Criterion',
                            'description' => $crit['description'] ?? '',
                            'rating'      => $existingRatings[$key]['rating'] ?? ($metric['rating'] ?? 'Good'),
                            'remarks'     => $existingRatings[$key]['remarks'] ?? ($metric['remarks'] ?? ''),
                        ];
                    } else {
                        // Pre-populate with auto-calculated ratings and smart remarks
                        $ratings[$key] = [
                            'name'        => $crit['name'] ?? 'Criterion',
                            'description' => $crit['description'] ?? '',
                            'rating'      => $metric['rating'] ?? 'Good',
                            'remarks'     => $metric['remarks'] ?? '',
                        ];
                    }
                }
                $this->criteriaRatings = $ratings;
                $this->mentorFinalComments = $repIntern->mentor_final_comments ?? '';
            }
        }
    }

    public function setRating(string $critKey, string $rating): void
    {
        if (isset($this->criteriaRatings[$critKey])) {
            $this->criteriaRatings[$critKey]['rating'] = $rating;
        }
    }

    public function setAllRatings(string $rating): void
    {
        foreach ($this->criteriaRatings as $key => $data) {
            $this->criteriaRatings[$key]['rating'] = $rating;
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
            if ($this->record->isMonthly()) {
                $isEval = !empty($this->criteriaRatings) || filled(trim($this->mentorFinalComments));

                $repIntern->update([
                    'criteria_ratings'      => $this->criteriaRatings,
                    'mentor_final_comments' => $this->mentorFinalComments,
                    'is_evaluated'          => $isEval,
                    'evaluated_at'          => now(),
                    'evaluated_by'          => auth()->id(),
                ]);

                $this->lastSavedAt = now()->format('d M Y, h:i A');

                $pendingCount = $this->record->reportInterns()->where('is_evaluated', false)->count();
                if ($pendingCount === 0) {
                    $this->record->update(['status' => 'completed']);
                } else {
                    $this->record->update(['status' => 'in_progress']);
                }

                Notification::make()
                    ->title('Monthly Evaluation Saved Successfully')
                    ->body("Criteria evaluation and final mentor comments saved for {$repIntern->intern?->name}.")
                    ->success()
                    ->send();
            } else {
                $isEval = filled(trim($this->weeklySummary)) || filled(trim($this->supervisorRemarks));

                $repIntern->update([
                    'weekly_summary'     => $this->weeklySummary,
                    'supervisor_remarks' => $this->supervisorRemarks,
                    'is_evaluated'       => $isEval,
                    'evaluated_at'       => now(),
                    'evaluated_by'       => auth()->id(),
                ]);

                $this->lastSavedAt = now()->format('d M Y, h:i A');

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
    }

    public function applyCriterionSuggested(string $critKey): void
    {
        if (!$this->selectedInternId || !isset($this->criteriaRatings[$critKey])) {
            return;
        }

        if (empty($this->calculatedMetrics)) {
            $this->calculatedMetrics = $this->calculateMetricsForIntern($this->selectedInternId);
        }

        $critName = $this->criteriaRatings[$critKey]['name'] ?? '';
        $metric = $this->resolveMetricForCriterion($critKey, $critName);

        $this->criteriaRatings[$critKey]['rating'] = $metric['rating'] ?? 'Good';
        $this->criteriaRatings[$critKey]['remarks'] = $metric['remarks'] ?? '';

        Notification::make()
            ->title("Suggested Rating Applied")
            ->body("Applied suggested rating ({$metric['rating']}) and feedback for {$critName}.")
            ->info()
            ->send();
    }

    public function autoCalculateAllCriteria(): void
    {
        if (!$this->selectedInternId || !$this->record->isMonthly()) {
            return;
        }

        $intern = Intern::find($this->selectedInternId);
        $this->calculatedMetrics = $this->calculateMetricsForIntern($this->selectedInternId);

        $criteriaList = $this->record->evaluation_criteria ?: ProgressReport::getDefaultEvaluationCriteria();
        foreach ($criteriaList as $idx => $crit) {
            $key = $crit['id'] ?? ('crit_' . $idx);
            $metric = $this->resolveMetricForCriterion($key, $crit['name'] ?? '');

            if (isset($this->criteriaRatings[$key])) {
                $this->criteriaRatings[$key]['rating'] = $metric['rating'] ?? 'Good';
                $this->criteriaRatings[$key]['remarks'] = $metric['remarks'] ?? '';
            } else {
                $this->criteriaRatings[$key] = [
                    'name'        => $crit['name'] ?? 'Criterion',
                    'description' => $crit['description'] ?? '',
                    'rating'      => $metric['rating'] ?? 'Good',
                    'remarks'     => $metric['remarks'] ?? '',
                ];
            }
        }

        Notification::make()
            ->title('All Criteria Auto-Calculated & Applied')
            ->body("Ratings and remarks dynamically generated for " . ($intern?->name ?? 'intern') . " based on monthly attendance, task marks, and daily logbook entries.")
            ->success()
            ->send();
    }

    public function openCriterionModal(string $critKey): void
    {
        if (!isset($this->criteriaRatings[$critKey])) {
            return;
        }

        $this->modalCritKey = $critKey;
        $this->modalCritName = $this->criteriaRatings[$critKey]['name'] ?? ucfirst(str_replace('_', ' ', $critKey));
        $this->modalCritRating = $this->criteriaRatings[$critKey]['rating'] ?? 'Good';
        $this->modalCritRemarks = $this->criteriaRatings[$critKey]['remarks'] ?? '';
        $this->modalCritMetric = $this->resolveMetricForCriterion($critKey, $this->modalCritName);
        $this->showCriterionModal = true;
    }

    public function closeCriterionModal(): void
    {
        $this->showCriterionModal = false;
        $this->modalCritKey = null;
        $this->modalCritName = '';
        $this->modalCritRating = 'Good';
        $this->modalCritRemarks = '';
        $this->modalCritMetric = null;
    }

    public function setModalCriterionRating(string $rating): void
    {
        $this->modalCritRating = $rating;
    }

    public function refreshModalCriterionRemarks(): void
    {
        if (!$this->modalCritKey) {
            return;
        }

        $metric = $this->resolveMetricForCriterion($this->modalCritKey, $this->modalCritName);
        $this->modalCritRemarks = $metric['remarks'] ?? $this->modalCritRemarks;
        if (!empty($metric['rating'])) {
            $this->modalCritRating = $metric['rating'];
        }

        Notification::make()
            ->title('Remarks Refreshed')
            ->body("Loaded auto-calculated evaluation for '{$this->modalCritName}'.")
            ->info()
            ->send();
    }

    public function saveCriterionModal(): void
    {
        if (!$this->modalCritKey || !isset($this->criteriaRatings[$this->modalCritKey])) {
            $this->closeCriterionModal();
            return;
        }

        $this->criteriaRatings[$this->modalCritKey]['rating'] = $this->modalCritRating;
        $this->criteriaRatings[$this->modalCritKey]['remarks'] = $this->modalCritRemarks;

        // Auto-persist changes for the intern
        if ($this->selectedInternId) {
            $repIntern = ProgressReportIntern::where('progress_report_id', $this->record->id)
                ->where('intern_id', $this->selectedInternId)
                ->first();

            if ($repIntern) {
                $repIntern->update([
                    'criteria_ratings' => $this->criteriaRatings,
                    'is_evaluated'     => true,
                    'evaluated_at'     => now(),
                    'evaluated_by'     => auth()->id(),
                ]);
                $this->lastSavedAt = now()->format('d M Y, h:i A');
            }
        }

        $critTitle = $this->modalCritName;
        $this->closeCriterionModal();

        Notification::make()
            ->title('Criterion Updated')
            ->body("Evaluation for '{$critTitle}' saved successfully.")
            ->success()
            ->send();
    }

    public function openMentorCommentsModal(): void
    {
        $this->modalMentorComments = $this->mentorFinalComments;
        $this->showMentorCommentsModal = true;
    }

    public function closeMentorCommentsModal(): void
    {
        $this->showMentorCommentsModal = false;
        $this->modalMentorComments = '';
    }

    public function saveMentorCommentsModal(): void
    {
        $this->mentorFinalComments = $this->modalMentorComments;

        if ($this->selectedInternId) {
            $repIntern = ProgressReportIntern::where('progress_report_id', $this->record->id)
                ->where('intern_id', $this->selectedInternId)
                ->first();

            if ($repIntern) {
                $repIntern->update([
                    'mentor_final_comments' => $this->mentorFinalComments,
                    'is_evaluated'          => true,
                    'evaluated_at'          => now(),
                    'evaluated_by'          => auth()->id(),
                ]);
                $this->lastSavedAt = now()->format('d M Y, h:i A');
            }
        }

        $this->closeMentorCommentsModal();

        Notification::make()
            ->title('Final Comments Saved')
            ->body('Final comments by mentor updated and saved.')
            ->success()
            ->send();
    }

    protected function resolveMetricForCriterion(string $critKey, string $critName = ''): array
    {
        if (isset($this->calculatedMetrics[$critKey])) {
            return $this->calculatedMetrics[$critKey];
        }

        $cName = strtolower($critName ?: $critKey);
        if (str_contains($cName, 'attend')) {
            return $this->calculatedMetrics['attendance'] ?? $this->calculatedMetrics['_composite'] ?? [];
        }
        if (str_contains($cName, 'disciplin') || str_contains($cName, 'punctual') || str_contains($cName, 'conduct')) {
            return $this->calculatedMetrics['discipline'] ?? $this->calculatedMetrics['_composite'] ?? [];
        }
        if (str_contains($cName, 'qualit') || str_contains($cName, 'deliverable')) {
            return $this->calculatedMetrics['work_quality'] ?? $this->calculatedMetrics['_composite'] ?? [];
        }
        if (str_contains($cName, 'technic') || str_contains($cName, 'skill') || str_contains($cName, 'coding')) {
            return $this->calculatedMetrics['technical_skills'] ?? $this->calculatedMetrics['_composite'] ?? [];
        }
        if (str_contains($cName, 'communicat') || str_contains($cName, 'report')) {
            return $this->calculatedMetrics['communication'] ?? $this->calculatedMetrics['_composite'] ?? [];
        }
        if (str_contains($cName, 'team') || str_contains($cName, 'collaborat')) {
            return $this->calculatedMetrics['teamwork'] ?? $this->calculatedMetrics['_composite'] ?? [];
        }

        return $this->calculatedMetrics['_composite'] ?? [
            'rating'     => 'Good',
            'remarks'    => 'Meets expectations for this evaluation criterion.',
            'stat_badge' => 'Standard',
            'detail'     => 'General evaluation',
        ];
    }

    public function calculateMetricsForIntern(?int $internId = null): array
    {
        $id = $internId ?: $this->selectedInternId;
        if (!$id) {
            return [];
        }

        $intern = Intern::with(['batch', 'team'])->find($id);
        if (!$intern) {
            return [];
        }

        $startDate = $this->record->week_start_date->copy();
        $endDate = $this->record->week_end_date->copy();
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();
        $today = Carbon::today();
        $effectiveEndDate = $endDate->isFuture() ? $today->copy()->min($endDate) : $endDate;
        $effEndStr = $effectiveEndDate->toDateString();

        // ── 1. Attendance Metrics ──
        $attRecords = Attendance::where('intern_id', $id)
            ->whereBetween('date', [$startStr, $endStr])
            ->get();

        $presentDays = $attRecords->where('status', 'present')->count();
        $lateDays = $attRecords->where('status', 'late')->count();
        $wfhDays = $attRecords->where('status', 'wfh')->count();
        $leaveDays = $attRecords->where('status', 'leave')->count();
        $absentDays = $attRecords->where('status', 'absent')->count();
        $attendedDays = $presentDays + $lateDays + $wfhDays;
        $lateTotalMins = (int) $attRecords->where('status', 'late')->sum('late_minutes');

        $recordedWorkingDays = $attendedDays + $absentDays + $leaveDays;
        if ($recordedWorkingDays > 0) {
            $workingDays = $recordedWorkingDays;
        } else {
            $calDays = 0;
            $holidays = Attendance::whereBetween('date', [$startStr, $effEndStr])
                ->where('status', 'holiday')
                ->where(fn ($q) => $q->where('intern_id', $id)->orWhereNull('intern_id'))
                ->pluck('date')
                ->map(fn ($d) => Carbon::parse($d)->toDateString())
                ->flip()
                ->toArray();

            $cur = $startDate->copy();
            while ($cur->lte($effectiveEndDate)) {
                $cStr = $cur->toDateString();
                if (!$cur->isSunday() && !isset($holidays[$cStr])) {
                    $calDays++;
                }
                $cur->addDay();
            }
            $workingDays = max($calDays, 1);
        }

        $attPct = $workingDays > 0 ? min(100.0, round(($attendedDays / $workingDays) * 100, 1)) : 0.0;
        $attRating = match (true) {
            $attPct >= 80 => 'Excellent',
            $attPct >= 75 => 'Good',
            $attPct >= 60 => 'Average',
            default       => 'Needs Improvement',
        };

        $attRemark = match ($attRating) {
            'Excellent' => "{$attPct}% attendance ({$attendedDays}/{$workingDays} working days). Excellent punctuality and regular presence.",
            'Good'      => "{$attPct}% attendance ({$attendedDays}/{$workingDays} working days). Meets attendance requirements with consistent presence.",
            'Average'   => "{$attPct}% attendance ({$attendedDays}/{$workingDays} working days). Satisfactory attendance, occasional absences noted.",
            default     => "{$attPct}% attendance ({$attendedDays}/{$workingDays} working days). Low attendance rate; regular presence required.",
        };

        // ── 2. Discipline Metrics ──
        $onTimeDays = $presentDays + $wfhDays;
        $punctualityPct = $attendedDays > 0 ? round(($onTimeDays / $attendedDays) * 100, 1) : 100.0;

        $subs = TaskSubmission::with('task')
            ->where('intern_id', $id)
            ->whereBetween('submitted_at', [$startStr . ' 00:00:00', $endStr . ' 23:59:59'])
            ->get();

        $taskDeadlineScore = null;
        if ($subs->isNotEmpty()) {
            $onTimeCount = $subs->filter(function ($s) {
                if (!$s->task || !$s->task->due_date) return true;
                return Carbon::parse($s->submitted_at)->lte(Carbon::parse($s->task->due_date)->endOfDay());
            })->count();
            $taskDeadlineScore = round(($onTimeCount / $subs->count()) * 100, 1);
        }

        $disciplineScore = $taskDeadlineScore !== null
            ? round(($punctualityPct * 0.6) + ($taskDeadlineScore * 0.4), 1)
            : $punctualityPct;

        $disciplineRating = match (true) {
            $disciplineScore >= 80 => 'Excellent',
            $disciplineScore >= 75 => 'Good',
            $disciplineScore >= 60 => 'Average',
            default                => 'Needs Improvement',
        };

        $disciplineRemark = match ($disciplineRating) {
            'Excellent' => "Excellent discipline and conduct ({$disciplineScore}%). Punctual ({$lateDays} late) and adheres strictly to guidelines.",
            'Good'      => "Good workplace discipline ({$disciplineScore}%). Follows instructions well with " . ($lateDays > 0 ? "{$lateDays} late arrival(s)." : "good punctuality."),
            'Average'   => "Acceptable conduct ({$disciplineScore}%). " . ($lateDays > 0 ? "Recorded {$lateDays} late arrival(s) ({$lateTotalMins}m total delay); schedule adherence encouraged." : "Adherence to schedule and deadlines encouraged."),
            default     => "Discipline ({$disciplineScore}%) needs attention. " . ($lateDays > 0 ? "{$lateDays} late arrival(s) recorded." : "") . ($taskDeadlineScore !== null && $taskDeadlineScore < 60 ? " Task deadlines missed." : ""),
        };

        // ── 3. Work Quality Metrics ──
        $taskScores = [];
        foreach ($subs as $s) {
            if ($s->marks !== null && is_numeric($s->marks)) {
                $taskScores[] = (float) $s->marks;
            } elseif ($s->grade) {
                $g = strtoupper(trim($s->grade));
                $gradeMap = ['O' => 95, 'A+' => 90, 'A' => 82, 'B' => 75, 'C' => 65, 'F' => 45];
                $taskScores[] = $gradeMap[$g] ?? 75;
            }
        }
        $avgTaskScore = !empty($taskScores) ? round(array_sum($taskScores) / count($taskScores), 1) : null;

        $logsWithRemarks = DailyLogbook::where('intern_id', $id)
            ->whereBetween('date', [$startStr, $endStr])
            ->whereNotNull('supervisor_remarks')
            ->where('supervisor_remarks', '!=', '')
            ->get();

        $remarkScores = [];
        foreach ($logsWithRemarks as $l) {
            $rem = strtolower(trim($l->supervisor_remarks));
            if (str_contains($rem, 'excellent') || str_contains($rem, 'outstanding')) {
                $remarkScores[] = 95;
            } elseif (str_contains($rem, 'good') || str_contains($rem, 'satisfactory') || str_contains($rem, 'approved') || str_contains($rem, 'great')) {
                $remarkScores[] = 80;
            } elseif (str_contains($rem, 'average') || str_contains($rem, 'fair')) {
                $remarkScores[] = 68;
            } elseif (str_contains($rem, 'poor') || str_contains($rem, 'improve') || str_contains($rem, 'rework')) {
                $remarkScores[] = 50;
            } else {
                $remarkScores[] = 78;
            }
        }
        $avgRemarkScore = !empty($remarkScores) ? round(array_sum($remarkScores) / count($remarkScores), 1) : null;

        if ($avgTaskScore !== null && $avgRemarkScore !== null) {
            $workQualityScore = round(($avgTaskScore * 0.6) + ($avgRemarkScore * 0.4), 1);
        } elseif ($avgTaskScore !== null) {
            $workQualityScore = $avgTaskScore;
        } elseif ($avgRemarkScore !== null) {
            $workQualityScore = $avgRemarkScore;
        } else {
            $workQualityScore = $attPct >= 80 ? 82.0 : 75.0;
        }

        $workQualityRating = match (true) {
            $workQualityScore >= 80 => 'Excellent',
            $workQualityScore >= 75 => 'Good',
            $workQualityScore >= 60 => 'Average',
            default                 => 'Needs Improvement',
        };

        $workQualityRemark = match ($workQualityRating) {
            'Excellent' => "High quality output ({$workQualityScore}% score across tasks & logs). Consistently delivers clean, accurate results.",
            'Good'      => "Good work quality ({$workQualityScore}%). Delivers reliable deliverables with minor revisions required.",
            'Average'   => "Average work quality ({$workQualityScore}%). Completes assigned tasks but requires closer supervision.",
            default     => "Work quality ({$workQualityScore}%) requires significant improvement in accuracy and meeting deliverable expectations.",
        };

        // ── 4. Technical Skills Metrics ──
        $techScore = $avgTaskScore !== null ? $avgTaskScore : ($workQualityScore ?: 80.0);
        $techRating = match (true) {
            $techScore >= 80 => 'Excellent',
            $techScore >= 75 => 'Good',
            $techScore >= 60 => 'Average',
            default          => 'Needs Improvement',
        };

        $techRemark = match ($techRating) {
            'Excellent' => "Strong domain expertise and technical execution ({$techScore}%). Quickly grasps concepts and produces robust solutions.",
            'Good'      => "Good technical competency ({$techScore}%). Competent in core internship stack and completes assigned technical deliverables.",
            'Average'   => "Satisfactory technical skills ({$techScore}%). Demonstrates fundamental knowledge; needs help on complex modules.",
            default     => "Technical proficiency ({$techScore}%) is below expected benchmark; extra mentoring recommended.",
        };

        // ── 5. Communication Metrics ──
        $distinctLogsCount = DailyLogbook::where('intern_id', $id)
            ->whereBetween('date', [$startStr, $endStr])
            ->distinct('date')
            ->count('date');

        $logRate = $attendedDays > 0 ? min(100.0, round(($distinctLogsCount / $attendedDays) * 100, 1)) : ($distinctLogsCount > 0 ? 100.0 : 0.0);
        $commRating = match (true) {
            $logRate >= 80 => 'Excellent',
            $logRate >= 75 => 'Good',
            $logRate >= 60 => 'Average',
            default        => 'Needs Improvement',
        };

        $commRemark = match ($commRating) {
            'Excellent' => "Proactive communication with {$logRate}% daily log submission rate ({$distinctLogsCount}/{$attendedDays} days). Clearly reports progress.",
            'Good'      => "Good communication habits ({$logRate}% daily logs submitted). Keeps supervisors updated on progress.",
            'Average'   => "Average communication ({$logRate}% log compliance). Occasional missed reports; prompt updates encouraged.",
            default     => "Communication needs improvement ({$logRate}% log compliance). Frequently misses required daily logs.",
        };

        // ── 6. Teamwork Metrics ──
        $hasTeam = (bool) ($intern->intern_team_id && $intern->team);
        $teamName = $intern->team?->team_name;
        $teamScore = 75.0;
        if ($hasTeam) {
            $teamScore += 10.0;
            $teamTasksCount = TaskAssignment::where('team_id', $intern->intern_team_id)->count();
            if ($teamTasksCount > 0) {
                $teamScore += 5.0;
            }
        } else {
            $teamScore = min(85.0, $attPct >= 75 ? 80.0 : 70.0);
        }
        $teamScore = min(100.0, $teamScore);

        $teamRating = match (true) {
            $teamScore >= 80 => 'Excellent',
            $teamScore >= 75 => 'Good',
            $teamScore >= 60 => 'Average',
            default          => 'Needs Improvement',
        };

        $teamRemark = match ($teamRating) {
            'Excellent' => $hasTeam
                ? "Collaborative team player in squad '{$teamName}'. Contributes positively to squad tasks and supports fellow interns."
                : "Demonstrates positive workplace attitude, willingness to help, and cooperative teamwork ethics.",
            'Good'      => $hasTeam
                ? "Works well with team members in squad '{$teamName}'. Coordinates adequately on group deliverables."
                : "Maintains good working rapport with peers and supervisors.",
            'Average'   => "Encouraged to be more proactive in collaborating and coordinating with peers.",
            default     => "Team collaboration and proactive participation needs improvement.",
        };

        // Overall / Composite for custom criteria
        $compositeScore = round(($attPct + $disciplineScore + $workQualityScore + $techScore + $logRate + $teamScore) / 6, 1);
        $compositeRating = match (true) {
            $compositeScore >= 80 => 'Excellent',
            $compositeScore >= 75 => 'Good',
            $compositeScore >= 60 => 'Average',
            default               => 'Needs Improvement',
        };

        return [
            'attendance' => [
                'score'       => $attPct,
                'rating'      => $attRating,
                'remarks'     => $attRemark,
                'stat_badge'  => "{$attPct}% ({$attendedDays}/{$workingDays} Days)",
                'detail'      => "{$attendedDays} Attended, {$presentDays} Present, {$lateDays} Late, {$absentDays} Absent",
            ],
            'discipline' => [
                'score'       => $disciplineScore,
                'rating'      => $disciplineRating,
                'remarks'     => $disciplineRemark,
                'stat_badge'  => "{$disciplineScore}% ({$onTimeDays}/{$attendedDays} On-Time)",
                'detail'      => "{$punctualityPct}% Punctual ({$lateDays} Late arrivals" . ($taskDeadlineScore !== null ? ", {$taskDeadlineScore}% Deadline score" : "") . ")",
            ],
            'work_quality' => [
                'score'       => $workQualityScore,
                'rating'      => $workQualityRating,
                'remarks'     => $workQualityRemark,
                'stat_badge'  => "{$workQualityScore}% Score",
                'detail'      => ($avgTaskScore !== null ? "Task Avg: {$avgTaskScore}%" : "") . ($avgRemarkScore !== null ? ($avgTaskScore !== null ? " · " : "") . "Logbook Remarks: {$avgRemarkScore}%" : ""),
            ],
            'technical_skills' => [
                'score'       => $techScore,
                'rating'      => $techRating,
                'remarks'     => $techRemark,
                'stat_badge'  => "{$techScore}% Technical Avg",
                'detail'      => ($avgTaskScore !== null ? "Evaluated Tasks Avg: {$avgTaskScore}%" : "Assessed from completed deliverables"),
            ],
            'communication' => [
                'score'       => $logRate,
                'rating'      => $commRating,
                'remarks'     => $commRemark,
                'stat_badge'  => "{$logRate}% ({$distinctLogsCount}/{$attendedDays} Logs)",
                'detail'      => "{$distinctLogsCount} daily logs submitted across {$attendedDays} attended days",
            ],
            'teamwork' => [
                'score'       => $teamScore,
                'rating'      => $teamRating,
                'remarks'     => $teamRemark,
                'stat_badge'  => "{$teamScore}% Squad Score",
                'detail'      => $hasTeam ? "Squad: {$teamName}" : "Independent Contributor",
            ],
            '_composite' => [
                'score'       => $compositeScore,
                'rating'      => $compositeRating,
                'remarks'     => "Overall monthly performance rating based on attendance, task submissions, and daily reporting.",
                'stat_badge'  => "{$compositeScore}% Composite",
                'detail'      => "Weighted average across all criteria",
            ]
        ];
    }

    public function autoDraftMentorComments(): void
    {
        if (!$this->selectedInternId) {
            return;
        }

        $intern = Intern::with(['batch', 'team'])->find($this->selectedInternId);
        if (!$intern) {
            return;
        }

        if (empty($this->calculatedMetrics)) {
            $this->calculatedMetrics = $this->calculateMetricsForIntern($this->selectedInternId);
        }

        $attMetric = $this->calculatedMetrics['attendance'] ?? [];
        $compMetric = $this->calculatedMetrics['_composite'] ?? [];

        $attScore = $attMetric['score'] ?? 0;
        $overallRating = $compMetric['rating'] ?? 'Good';
        $squadName = $intern->team?->team_name;

        $praise = match ($overallRating) {
            'Excellent' => "consistently delivered outstanding performance with high quality deliverables and dependable teamwork",
            'Good'      => "demonstrated solid technical competence, dependable conduct, and steady progress",
            'Average'   => "maintained satisfactory participation while showing potential for greater technical ownership",
            default     => "demonstrated effort but requires closer guidance and consistency to meet core expectations",
        };

        $teamText = $squadName ? " As an active contributor in squad '{$squadName}', the intern collaborated well on group milestones." : "";

        $draft = "{$intern->name} has {$praise} throughout this monthly evaluation period. Recorded {$attScore}% attendance consistency alongside positive adherence to workspace guidelines.{$teamText} Demonstrates a constructive learning attitude and is positively recommended for ongoing project assignments and subsequent milestone progression.";

        $this->mentorFinalComments = $draft;
        $this->modalMentorComments = $draft;

        Notification::make()
            ->title('Mentor Comments Auto-Drafted')
            ->body('Synthesized comprehensive final feedback based on calculated monthly metrics.')
            ->info()
            ->send();
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
