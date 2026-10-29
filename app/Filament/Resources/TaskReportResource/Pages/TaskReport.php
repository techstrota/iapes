<?php

namespace App\Filament\Resources\TaskReportResource\Pages;

use App\Filament\Resources\TaskReportResource;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaskReport extends Page
{
    use WithPagination;

    protected static string $resource = TaskReportResource::class;

    protected static string $view = 'filament.resources.task-report-resource.pages.task-report';

    protected static ?string $title = 'Task Performance & Deadline Intelligence Report';

    // ── Scope: 'all' | 'active' | 'archived' ───────────────────────────────
    public string $activeScope = 'all';

    // ── Perspective View Mode: 'intern' | 'task' | 'matrix' ───────────────
    public string $viewMode = 'intern'; // 'intern', 'task', 'matrix'

    // ── Search & Filter Controls ──────────────────────────────────────────
    public string $search = '';
    public ?int $selectedBatchId = null;
    public ?int $selectedTeamId = null;
    public string $selectedCycle = '';
    public string $timelinessFilter = 'all'; // 'all', 'on_time', 'late', 'overdue', 'pending'
    public string $priorityFilter = 'all';   // 'all', 'urgent', 'high', 'medium', 'low'
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
    public string $dateField = 'due_date';   // 'due_date', 'submitted_at', 'created_at'
    public string $sortBy = 'name';
    public string $sortDir = 'asc';
    public int $perPage = 11;

    // ── Pop-up Modal State for Export ─────────────────────────────────────
    public bool $showExportModal = false;

    // ── Pop-up Modal State for Intern Task Dossier ─────────────────────────
    public bool $showDetailModal = false;
    public ?int $selectedDetailInternId = null;
    public ?int $selectedDetailTaskId = null;

    // ── Export Column Definitions ─────────────────────────────────────────
    public array $visibleColumns = [
        'sr_no',
        'task_title',
        'priority',
        'due_date',
        'intern_code',
        'intern_name',
        'intern_status',
        'batch_name',
        'team_name',
        'mentor_name',
        'timeliness_status',
        'delay_info',
        'submitted_at',
        'marks',
        'grade',
    ];

    public function updatedActiveScope(): void { $this->resetPage(); }
    public function updatedViewMode(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSelectedBatchId(): void { $this->resetPage(); }
    public function updatedSelectedTeamId(): void { $this->resetPage(); }
    public function updatedSelectedCycle(): void { $this->resetPage(); }
    public function updatedTimelinessFilter(): void { $this->resetPage(); }
    public function updatedPriorityFilter(): void { $this->resetPage(); }
    public function updatedCustomStartDate(): void { $this->resetPage(); }
    public function updatedCustomEndDate(): void { $this->resetPage(); }
    public function updatedDateField(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedBatchId = null;
        $this->selectedTeamId = null;
        $this->selectedCycle = '';
        $this->timelinessFilter = 'all';
        $this->priorityFilter = 'all';
        $this->customStartDate = null;
        $this->customEndDate = null;
        $this->dateField = 'due_date';
        $this->sortBy = 'name';
        $this->sortDir = 'asc';
        $this->resetPage();
    }

    public function setPerspective(string $mode): void
    {
        if (in_array($mode, ['intern', 'task', 'matrix'])) {
            $this->viewMode = $mode;
            $this->resetPage();
        }
    }

    public function toggleSort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    public function openExportModal(): void
    {
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
    }

    public function selectAllExportColumns(): void
    {
        $this->visibleColumns = array_keys($this->getColumnDefinitions());
    }

    public function deselectAllExportColumns(): void
    {
        $this->visibleColumns = ['sr_no', 'task_title', 'intern_name', 'timeliness_status', 'submitted_at'];
    }

    public function openInternDossier(int $internId): void
    {
        $this->selectedDetailInternId = $internId;
        $this->selectedDetailTaskId = null;
        $this->showDetailModal = true;
    }

    public function openTaskDossier(int $taskId): void
    {
        $this->selectedDetailTaskId = $taskId;
        $this->selectedDetailInternId = null;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->selectedDetailInternId = null;
        $this->selectedDetailTaskId = null;
        $this->showDetailModal = false;
    }

    public function getColumnDefinitions(): array
    {
        return [
            'sr_no'             => 'Sr. No.',
            'task_title'        => 'Task Title',
            'priority'          => 'Priority',
            'due_date'          => 'Due Date / Deadline',
            'intern_code'       => 'Intern ID / Code',
            'intern_name'       => 'Intern Full Name',
            'intern_status'     => 'Intern Status (Active / Archived)',
            'cohort_cycle'      => 'Archive Cycle / Cohort',
            'batch_name'        => 'Internship Batch',
            'team_name'         => 'Project Squad',
            'mentor_name'       => 'Assigned Mentor',
            'timeliness_status' => 'Timeliness Status',
            'delay_info'        => 'Delay / Timeliness Detail',
            'submitted_at'      => 'Submitted At',
            'marks'             => 'Marks Awarded',
            'grade'             => 'Evaluation Grade',
            'admin_feedback'    => 'Mentor / Admin Feedback',
        ];
    }

    /**
     * Build the raw assignment matrix across tasks and interns.
     */
    protected function buildRawAssignmentMatrix(Collection $allTasks, Collection $allAssignments, Collection $allInterns): Collection
    {
        $matrix = collect();

        foreach ($allAssignments as $assignment) {
            $task = $allTasks->firstWhere('task_id', $assignment->task_id);
            if (!$task) {
                continue;
            }

            // Resolve assigned interns for this specific assignment
            $targetInterns = collect();
            if ($assignment->assigned_type === 'intern' && $assignment->intern_id) {
                $intern = $allInterns->firstWhere('id', $assignment->intern_id);
                if ($intern) $targetInterns->push($intern);
            } elseif ($assignment->assigned_type === 'team' && $assignment->team_id) {
                $teamInterns = $allInterns->where('intern_team_id', $assignment->team_id);
                $targetInterns = $targetInterns->concat($teamInterns);
            } elseif ($assignment->assigned_type === 'batch' && $assignment->batch_id) {
                $batchInterns = $allInterns->where('internship_batch_id', $assignment->batch_id);
                $targetInterns = $targetInterns->concat($batchInterns);
            }

            foreach ($targetInterns->unique('id') as $intern) {
                // Find submission
                $submission = $intern->submissions->firstWhere('task_id', $task->task_id);
                $dueDate = $task->due_date ? Carbon::parse($task->due_date)->endOfDay() : null;

                $timeliness = 'pending';
                $delayDays = 0;
                $delayText = 'In progress';

                if ($submission && $submission->submitted_at) {
                    $submittedAt = Carbon::parse($submission->submitted_at);
                    if ($dueDate && $submittedAt->gt($dueDate)) {
                        $timeliness = 'late';
                        $delayDays = max(1, (int) round($dueDate->diffInHours($submittedAt) / 24));
                        $delayText = "Late by {$delayDays} " . ($delayDays === 1 ? 'day' : 'days');
                    } else {
                        $timeliness = 'on_time';
                        $delayText = 'On time';
                    }
                } else {
                    if ($dueDate && now()->gt($dueDate)) {
                        $timeliness = 'overdue';
                        $delayDays = max(1, (int) round($dueDate->diffInHours(now()) / 24));
                        $delayText = "Overdue by {$delayDays} " . ($delayDays === 1 ? 'day' : 'days');
                    } else {
                        $timeliness = 'pending';
                        $delayText = $dueDate ? 'Due ' . $dueDate->diffForHumans() : 'No deadline';
                    }
                }

                $matrix->push([
                    'assignment_id'     => $assignment->task_assignment_id,
                    'task'              => $task,
                    'intern'            => $intern,
                    'submission'        => $submission,
                    'timeliness'        => $timeliness,
                    'delay_days'        => $delayDays,
                    'delay_text'        => $delayText,
                    'due_date'          => $dueDate,
                    'submitted_at'      => $submission ? $submission->submitted_at : null,
                    'marks'             => $submission ? $submission->marks : null,
                    'grade'             => $submission ? $submission->grade : null,
                    'admin_feedback'    => $submission ? $submission->admin_feedback : null,
                ]);
            }
        }

        // Deduplicate matrix by task_id + intern_id in case assigned via multiple pathways
        return $matrix->unique(fn ($item) => $item['task']->task_id . '_' . $item['intern']->id)->values();
    }

    /**
     * Applies all active filters including Scope, Batch, Team, Timeliness, Priority, Search, and Date Range.
     */
    protected function filterAssignmentMatrix(Collection $matrix): Collection
    {
        // Apply Scope Filter
        if ($this->activeScope === 'active') {
            $matrix = $matrix->filter(fn ($item) => $item['intern']->is_active && blank($item['intern']->cohort_archive_name));
        } elseif ($this->activeScope === 'archived') {
            $matrix = $matrix->filter(fn ($item) => !$item['intern']->is_active || filled($item['intern']->cohort_archive_name));
        }

        // Apply Archive Cycle
        if (filled($this->selectedCycle)) {
            $matrix = $matrix->filter(fn ($item) => $item['intern']->cohort_archive_name === $this->selectedCycle);
        }

        // Apply Batch
        if ($this->selectedBatchId) {
            $matrix = $matrix->filter(fn ($item) => $item['intern']->internship_batch_id == $this->selectedBatchId);
        }

        // Apply Team
        if ($this->selectedTeamId) {
            $matrix = $matrix->filter(fn ($item) => $item['intern']->intern_team_id == $this->selectedTeamId);
        }

        // Apply Timeliness Filter
        if ($this->timelinessFilter !== 'all') {
            $matrix = $matrix->filter(fn ($item) => $item['timeliness'] === $this->timelinessFilter);
        }

        // Apply Priority Filter
        if ($this->priorityFilter !== 'all') {
            $matrix = $matrix->filter(fn ($item) => strtolower($item['task']->priority ?? '') === $this->priorityFilter);
        }

        // Apply Search
        if (filled($this->search)) {
            $term = strtolower(trim($this->search));
            $matrix = $matrix->filter(function ($item) use ($term) {
                return str_contains(strtolower($item['task']->title ?? ''), $term)
                    || str_contains(strtolower($item['intern']->name ?? ''), $term)
                    || str_contains(strtolower($item['intern']->intern_code ?? ''), $term)
                    || str_contains(strtolower($item['intern']->college ?? ''), $term)
                    || str_contains(strtolower($item['intern']->team?->mentor_name ?? ''), $term);
            });
        }

        // Apply Custom Date Range
        if ($this->customStartDate || $this->customEndDate) {
            $start = $this->customStartDate ? Carbon::parse($this->customStartDate)->startOfDay() : null;
            $end = $this->customEndDate ? Carbon::parse($this->customEndDate)->endOfDay() : null;
            $field = in_array($this->dateField, ['due_date', 'submitted_at', 'created_at']) ? $this->dateField : 'due_date';

            $matrix = $matrix->filter(function ($item) use ($start, $end, $field) {
                $dateVal = null;
                if ($field === 'due_date') {
                    $dateVal = $item['task']->due_date ? Carbon::parse($item['task']->due_date) : null;
                } elseif ($field === 'submitted_at') {
                    $dateVal = $item['submission'] && $item['submission']->submitted_at ? Carbon::parse($item['submission']->submitted_at) : null;
                } elseif ($field === 'created_at') {
                    $dateVal = $item['task']->created_at ? Carbon::parse($item['task']->created_at) : null;
                }

                if (!$dateVal) {
                    return false;
                }

                if ($start && $end) {
                    return $dateVal->between($start, $end);
                } elseif ($start) {
                    return $dateVal->greaterThanOrEqualTo($start);
                } elseif ($end) {
                    return $dateVal->lessThanOrEqualTo($end);
                }

                return true;
            });
        }

        return $matrix;
    }

    /**
     * CSV Stream Export
     */
    public function exportCsv(): StreamedResponse
    {
        $allTasks = Task::all();
        $allAssignments = TaskAssignment::all();
        $allInterns = Intern::with(['batch', 'team', 'submissions'])->get();

        $matrix = $this->buildRawAssignmentMatrix($allTasks, $allAssignments, $allInterns);
        $matrix = $this->filterAssignmentMatrix($matrix);

        $allColDefs = $this->getColumnDefinitions();
        $cols = !empty($this->visibleColumns) ? $this->visibleColumns : array_keys($allColDefs);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="task_performance_report_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($matrix, $cols, $allColDefs) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            $headerRow = [];
            foreach ($cols as $col) {
                $headerRow[] = $allColDefs[$col] ?? ucfirst(str_replace('_', ' ', $col));
            }
            fputcsv($output, $headerRow);

            $srNo = 1;
            foreach ($matrix as $item) {
                $task = $item['task'];
                $intern = $item['intern'];
                $sub = $item['submission'];
                $isArchived = !$intern->is_active || filled($intern->cohort_archive_name);

                $row = [];
                foreach ($cols as $col) {
                    $row[] = match ($col) {
                        'sr_no'             => $srNo,
                        'task_title'        => $task->title,
                        'priority'          => ucfirst($task->priority ?: 'Normal'),
                        'due_date'          => $task->due_date ? Carbon::parse($task->due_date)->format('d/m/Y') : '—',
                        'intern_code'       => $intern->intern_code ?: '—',
                        'intern_name'       => $intern->name,
                        'intern_status'     => $isArchived ? 'Completed / Archived' : 'Active (In Progress)',
                        'cohort_cycle'      => $intern->cohort_archive_name ?: 'Current Active',
                        'batch_name'        => $intern->batch?->batch_name ?: 'Unassigned',
                        'team_name'         => $intern->team?->team_name ?: 'Unassigned',
                        'mentor_name'       => $intern->team?->mentor_name ?: 'Not Assigned',
                        'timeliness_status' => match ($item['timeliness']) {
                            'on_time' => 'Completed On-Time',
                            'late'    => 'Completed Late',
                            'overdue' => 'Overdue (Missed)',
                            'pending' => 'Pending / In Progress',
                            default   => ucfirst($item['timeliness']),
                        },
                        'delay_info'        => $item['delay_text'],
                        'submitted_at'      => $sub && $sub->submitted_at ? Carbon::parse($sub->submitted_at)->format('d/m/Y H:i') : '—',
                        'marks'             => $sub && $sub->marks !== null ? $sub->marks : '—',
                        'grade'             => $sub && $sub->grade ? $sub->grade : '—',
                        'admin_feedback'    => $sub && $sub->admin_feedback ? $sub->admin_feedback : '—',
                        default             => '—',
                    };
                }
                fputcsv($output, $row);
                $srNo++;
            }
            fclose($output);
        }, 200, $headers);
    }

    public function getViewData(): array
    {
        // ── 1. Fetch Repository Foundations ──
        $allTasks = Task::with(['submissions', 'assignments'])->get();
        $allAssignments = TaskAssignment::all();
        $allInterns = Intern::with(['batch', 'team', 'submissions.task'])->get();

        $batches = InternshipBatch::where('is_archived', false)->get();
        $teams = InternTeam::where('is_archived', false)->get();
        $archiveCycles = Intern::whereNotNull('cohort_archive_name')
            ->where('cohort_archive_name', '!=', '')
            ->distinct()
            ->pluck('cohort_archive_name')
            ->values()
            ->toArray();

        // ── 2. Build Raw Assignment Matrix & Apply All Scopes/Filters ──
        $rawMatrix = $this->buildRawAssignmentMatrix($allTasks, $allAssignments, $allInterns);
        $scopedMatrix = $this->filterAssignmentMatrix($rawMatrix);

        // ── 4. Global KPIs (from current scoped matrix) ──
        $totalAssignmentsCount = $scopedMatrix->count();
        $onTimeCount = $scopedMatrix->where('timeliness', 'on_time')->count();
        $lateCount = $scopedMatrix->where('timeliness', 'late')->count();
        $overdueCount = $scopedMatrix->where('timeliness', 'overdue')->count();
        $pendingCount = $scopedMatrix->where('timeliness', 'pending')->count();
        $totalSubmittedCount = $onTimeCount + $lateCount;

        $overallOnTimeRate = $totalSubmittedCount > 0
            ? round(($onTimeCount / $totalSubmittedCount) * 100, 1)
            : 0;

        $overallCompletionRate = $totalAssignmentsCount > 0
            ? round(($totalSubmittedCount / $totalAssignmentsCount) * 100, 1)
            : 0;

        // ── 5. Perspective 1: Group By Intern ──
        $internPerspective = $scopedMatrix->groupBy(fn ($item) => $item['intern']->id)->map(function ($items) {
            $intern = $items->first()['intern'];
            $totalAssigned = $items->count();
            $onTime = $items->where('timeliness', 'on_time')->count();
            $late = $items->where('timeliness', 'late')->count();
            $overdue = $items->where('timeliness', 'overdue')->count();
            $pending = $items->where('timeliness', 'pending')->count();
            $submitted = $onTime + $late;
            $onTimePct = $submitted > 0 ? round(($onTime / $submitted) * 100, 1) : 0;
            $completionPct = $totalAssigned > 0 ? round(($submitted / $totalAssigned) * 100, 1) : 0;

            // Average marks if evaluated
            $graded = $items->filter(fn ($i) => $i['marks'] !== null);
            $avgMarks = $graded->count() > 0 ? round($graded->avg('marks'), 1) : null;

            return [
                'intern'          => $intern,
                'total_assigned'  => $totalAssigned,
                'on_time'         => $onTime,
                'late'            => $late,
                'overdue'         => $overdue,
                'pending'         => $pending,
                'submitted'       => $submitted,
                'on_time_pct'     => $onTimePct,
                'completion_pct'  => $completionPct,
                'avg_marks'       => $avgMarks,
                'is_archived'     => !$intern->is_active || filled($intern->cohort_archive_name),
            ];
        })->values();

        // ── 6. Perspective 2: Group By Task ──
        $taskPerspective = $scopedMatrix->groupBy(fn ($item) => $item['task']->task_id)->map(function ($items) {
            $task = $items->first()['task'];
            $totalAssigned = $items->count();
            $onTime = $items->where('timeliness', 'on_time')->count();
            $late = $items->where('timeliness', 'late')->count();
            $overdue = $items->where('timeliness', 'overdue')->count();
            $pending = $items->where('timeliness', 'pending')->count();
            $submitted = $onTime + $late;
            $completionPct = $totalAssigned > 0 ? round(($submitted / $totalAssigned) * 100, 1) : 0;
            $onTimePct = $submitted > 0 ? round(($onTime / $submitted) * 100, 1) : 0;

            return [
                'task'            => $task,
                'total_assigned'  => $totalAssigned,
                'on_time'         => $onTime,
                'late'            => $late,
                'overdue'         => $overdue,
                'pending'         => $pending,
                'submitted'       => $submitted,
                'completion_pct'  => $completionPct,
                'on_time_pct'     => $onTimePct,
            ];
        })->values();

        // ── 7. Perspective 3: Granular Submissions Matrix ──
        $matrixPerspective = $scopedMatrix->values();

        // ── 8. Sorting & Pagination for Current View Mode ──
        $currentPage = $this->getPage();
        $perPageVal = $this->perPage > 0 ? $this->perPage : 9999;

        if ($this->viewMode === 'intern') {
            if ($this->sortBy === 'on_time_pct') {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('on_time_pct')->values()
                    : $internPerspective->sortByDesc('on_time_pct')->values();
            } elseif ($this->sortBy === 'total_assigned') {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('total_assigned')->values()
                    : $internPerspective->sortByDesc('total_assigned')->values();
            } else {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('intern.name', SORT_NATURAL | SORT_FLAG_CASE)->values()
                    : $internPerspective->sortByDesc('intern.name', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }

            $totalItems = $internPerspective->count();
            $paginatedItems = $internPerspective->forPage($currentPage, $perPageVal)->values();
        } elseif ($this->viewMode === 'task') {
            if ($this->sortBy === 'due_date') {
                $taskPerspective = $this->sortDir === 'asc'
                    ? $taskPerspective->sortBy('task.due_date')->values()
                    : $taskPerspective->sortByDesc('task.due_date')->values();
            } elseif ($this->sortBy === 'completion_pct') {
                $taskPerspective = $this->sortDir === 'asc'
                    ? $taskPerspective->sortBy('completion_pct')->values()
                    : $taskPerspective->sortByDesc('completion_pct')->values();
            } else {
                $taskPerspective = $this->sortDir === 'asc'
                    ? $taskPerspective->sortBy('task.title', SORT_NATURAL | SORT_FLAG_CASE)->values()
                    : $taskPerspective->sortByDesc('task.title', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }

            $totalItems = $taskPerspective->count();
            $paginatedItems = $taskPerspective->forPage($currentPage, $perPageVal)->values();
        } else {
            // Matrix perspective
            if ($this->sortBy === 'due_date') {
                $matrixPerspective = $this->sortDir === 'asc'
                    ? $matrixPerspective->sortBy('due_date')->values()
                    : $matrixPerspective->sortByDesc('due_date')->values();
            } elseif ($this->sortBy === 'submitted_at') {
                $matrixPerspective = $this->sortDir === 'asc'
                    ? $matrixPerspective->sortBy('submitted_at')->values()
                    : $matrixPerspective->sortByDesc('submitted_at')->values();
            } else {
                $matrixPerspective = $this->sortDir === 'asc'
                    ? $matrixPerspective->sortBy('task.title', SORT_NATURAL | SORT_FLAG_CASE)->values()
                    : $matrixPerspective->sortByDesc('task.title', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }

            $totalItems = $matrixPerspective->count();
            $paginatedItems = $matrixPerspective->forPage($currentPage, $perPageVal)->values();
        }

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $totalItems,
            $perPageVal,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // ── 9. Fetch Detail Modals Data ──
        $detailInternData = null;
        if ($this->showDetailModal && $this->selectedDetailInternId) {
            $intern = Intern::with(['batch', 'team', 'submissions.task'])->find($this->selectedDetailInternId);
            if ($intern) {
                $internAssignments = $rawMatrix->where('intern.id', $intern->id)->values();
                $detailInternData = [
                    'intern'      => $intern,
                    'assignments' => $internAssignments,
                    'on_time'     => $internAssignments->where('timeliness', 'on_time')->count(),
                    'late'        => $internAssignments->where('timeliness', 'late')->count(),
                    'overdue'     => $internAssignments->where('timeliness', 'overdue')->count(),
                    'pending'     => $internAssignments->where('timeliness', 'pending')->count(),
                    'total'       => $internAssignments->count(),
                ];
            }
        }

        $detailTaskData = null;
        if ($this->showDetailModal && $this->selectedDetailTaskId) {
            $task = Task::find($this->selectedDetailTaskId);
            if ($task) {
                $taskAssignments = $rawMatrix->where('task.task_id', $task->task_id)->values();
                $detailTaskData = [
                    'task'        => $task,
                    'assignments' => $taskAssignments,
                    'on_time'     => $taskAssignments->where('timeliness', 'on_time')->count(),
                    'late'        => $taskAssignments->where('timeliness', 'late')->count(),
                    'overdue'     => $taskAssignments->where('timeliness', 'overdue')->count(),
                    'pending'     => $taskAssignments->where('timeliness', 'pending')->count(),
                    'total'       => $taskAssignments->count(),
                ];
            }
        }

        return [
            'paginator'              => $paginator,
            'totalAssignmentsCount'  => $totalAssignmentsCount,
            'onTimeCount'            => $onTimeCount,
            'lateCount'              => $lateCount,
            'overdueCount'           => $overdueCount,
            'pendingCount'           => $pendingCount,
            'totalSubmittedCount'    => $totalSubmittedCount,
            'overallOnTimeRate'      => $overallOnTimeRate,
            'overallCompletionRate'  => $overallCompletionRate,
            'batches'                => $batches,
            'teams'                  => $teams,
            'archiveCycles'          => $archiveCycles,
            'columnDefinitions'      => $this->getColumnDefinitions(),
            'detailInternData'       => $detailInternData,
            'detailTaskData'         => $detailTaskData,
        ];
    }
}
