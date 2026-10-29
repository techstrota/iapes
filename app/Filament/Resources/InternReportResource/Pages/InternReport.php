<?php

namespace App\Filament\Resources\InternReportResource\Pages;

use App\Filament\Resources\InternReportResource;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskAssignment;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InternReport extends Page
{
    use WithPagination;

    protected static string $resource = InternReportResource::class;

    protected static string $view = 'filament.resources.intern-report-resource.pages.intern-report';

    protected static ?string $title = 'Comprehensive Interns Intelligence & Analytics Report';

    // ── Tab Navigation: 'all' | 'active' | 'archived' ──────────────────────
    public string $activeTab = 'all';

    // ── Search & Filter Controls ──────────────────────────────────────────
    public string $search = '';
    public ?int $selectedBatchId = null;
    public ?int $selectedTeamId = null;
    public string $selectedCycle = '';
    public string $attendanceFilter = 'all'; // 'all', 'high' (>=90%), 'medium' (75-89%), 'low' (<75%)
    public string $taskFilter = 'all'; // 'all', 'completed' (100%), 'in_progress', 'no_tasks'
    public string $domainFilter = '';
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
    public string $dateField = 'joining_date'; // 'joining_date', 'completion_date', 'created_at'
    public string $sortBy = 'name';
    public string $sortDir = 'asc';
    public int $perPage = 11;

    // ── Pop-up Modal State for Export ─────────────────────────────────────
    public bool $showExportModal = false;

    // ── Pop-up Modal State for Intern Detail Dossier ──────────────────────
    public bool $showDetailModal = false;
    public ?int $selectedDetailInternId = null;

    // ── Export Column Definitions ─────────────────────────────────────────
    public array $visibleColumns = [
        'sr_no',
        'intern_code',
        'name',
        'status',
        'cohort_archive_name',
        'email',
        'phone',
        'college',
        'degree',
        'domain',
        'batch_name',
        'team_name',
        'mentor_name',
        'joining_date',
        'completion_date',
        'attendance_pct',
        'present_days',
        'wfh_days',
        'late_days',
        'absent_days',
        'leave_days',
        'total_working_days',
        'tasks_assigned',
        'tasks_completed',
        'tasks_pending',
        'task_completion_pct',
        'logbooks_count',
        'cgpa',
    ];

    public function updatedActiveTab(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSelectedBatchId(): void { $this->resetPage(); }
    public function updatedSelectedTeamId(): void { $this->resetPage(); }
    public function updatedSelectedCycle(): void { $this->resetPage(); }
    public function updatedAttendanceFilter(): void { $this->resetPage(); }
    public function updatedTaskFilter(): void { $this->resetPage(); }
    public function updatedDomainFilter(): void { $this->resetPage(); }
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
        $this->attendanceFilter = 'all';
        $this->taskFilter = 'all';
        $this->domainFilter = '';
        $this->customStartDate = null;
        $this->customEndDate = null;
        $this->dateField = 'joining_date';
        $this->sortBy = 'name';
        $this->sortDir = 'asc';
        $this->resetPage();
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
        $this->visibleColumns = ['sr_no', 'intern_code', 'name', 'status'];
    }

    public function openDetailModal(int $internId): void
    {
        $this->selectedDetailInternId = $internId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->selectedDetailInternId = null;
        $this->showDetailModal = false;
    }

    public function getColumnDefinitions(): array
    {
        return [
            'sr_no'               => 'Sr. No.',
            'intern_code'         => 'Intern ID / Code',
            'name'                => 'Full Name',
            'status'              => 'Current Status (Active / Completed)',
            'cohort_archive_name' => 'Archive Cycle / Cohort',
            'email'               => 'Email Address',
            'phone'               => 'Phone Number',
            'college'             => 'College / Institution',
            'degree'              => 'Degree / Program',
            'academic_year'       => 'Academic Year',
            'domain'              => 'Domain / Track',
            'internship_role'     => 'Internship Role',
            'batch_name'          => 'Batch Name',
            'batch_timing'        => 'Batch Timing',
            'team_name'           => 'Project Squad',
            'mentor_name'         => 'Assigned Mentor',
            'joining_date'        => 'Joining Date',
            'completion_date'     => 'Completion Date',
            'attendance_pct'      => 'Attendance Rate (%)',
            'present_days'        => 'Present Days (Onsite)',
            'wfh_days'            => 'WFH Days (Remote)',
            'late_days'           => 'Late Arrivals',
            'absent_days'         => 'Absent Days',
            'leave_days'          => 'Approved Leaves',
            'total_working_days'  => 'Total Evaluated Days',
            'tasks_assigned'      => 'Total Assigned Tasks',
            'tasks_completed'     => 'Completed / Submitted Tasks',
            'tasks_pending'       => 'Pending Tasks',
            'task_completion_pct' => 'Task Completion Rate (%)',
            'logbooks_count'      => 'Total Daily Logbook Submissions',
            'cgpa'                => 'CGPA / Score',
            'archive_note'        => 'Archival / Completion Note',
        ];
    }

    /**
     * Compute and decorate an intern's analytics payload.
     */
    protected function computeInternTelemetry(Intern $intern, Collection $allTasks, Collection $allAssignments): array
    {
        // ── 1. Attendance Telemetry ──
        $attendances = $intern->attendances ?? collect();
        $present = $attendances->where('status', 'present')->count();
        $wfh = $attendances->where('status', 'wfh')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $leave = $attendances->where('status', 'leave')->count();
        $holiday = $attendances->where('status', 'holiday')->count();
        $totalMarked = $attendances->count();

        $workingDays = $present + $wfh + $late + $absent + $leave;
        $attendedDays = $present + $wfh + $late;
        $attendancePct = $workingDays > 0 ? round(($attendedDays / $workingDays) * 100, 1) : ($totalMarked > 0 ? 100.0 : 0.0);

        // ── 2. Task Telemetry ──
        $assignedTaskIds = $allAssignments->filter(function ($a) use ($intern) {
            if ($a->assigned_type === 'intern' && $a->intern_id == $intern->id) {
                return true;
            }
            if ($a->assigned_type === 'team' && $intern->intern_team_id && $a->team_id == $intern->intern_team_id) {
                return true;
            }
            if ($a->assigned_type === 'batch' && $intern->internship_batch_id && $a->batch_id == $intern->internship_batch_id) {
                return true;
            }
            return false;
        })->pluck('task_id')->unique();

        $assignedTasks = $allTasks->whereIn('task_id', $assignedTaskIds);
        $totalTasks = $assignedTasks->count();

        $submissions = $intern->submissions ?? collect();
        $completedTaskIds = $submissions->whereIn('task_id', $assignedTaskIds)
            ->whereIn('status', ['approved', 'completed', 'submitted', 'reviewed'])
            ->pluck('task_id')
            ->unique();

        $completedTasks = $completedTaskIds->count();
        $pendingTasks = max(0, $totalTasks - $completedTasks);
        $taskPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0.0;

        // ── 3. Logbook Telemetry ──
        $logbooksCount = $intern->logbooks ? $intern->logbooks->count() : 0;

        // ── 4. Status Interpretation ──
        $isArchived = !$intern->is_active || filled($intern->cohort_archive_name);

        return [
            'present'         => $present,
            'wfh'             => $wfh,
            'late'            => $late,
            'absent'          => $absent,
            'leave'           => $leave,
            'holiday'         => $holiday,
            'total_marked'    => $totalMarked,
            'working_days'    => $workingDays,
            'attended_days'   => $attendedDays,
            'attendance_pct'  => $attendancePct,
            'total_tasks'     => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks'   => $pendingTasks,
            'task_pct'        => $taskPct,
            'logbooks_count'  => $logbooksCount,
            'is_archived'     => $isArchived,
        ];
    }

    /**
     * Resolves the primary filtered query of interns.
     */
    protected function getFilteredInternsQuery()
    {
        $query = Intern::with([
            'batch',
            'team',
            'attendances',
            'submissions',
            'logbooks',
            'offerletter',
            'completionCertificate',
            'completionLetter',
        ]);

        // Tab filter
        if ($this->activeTab === 'active') {
            $query->where('is_active', true)->whereNull('cohort_archive_name');
        } elseif ($this->activeTab === 'archived') {
            $query->where(function ($q) {
                $q->where('is_active', false)
                    ->orWhereNotNull('cohort_archive_name');
            });
        }

        // Archive cycle filter
        if (filled($this->selectedCycle)) {
            $query->where('cohort_archive_name', $this->selectedCycle);
        }

        // Batch filter
        if ($this->selectedBatchId) {
            $query->where('internship_batch_id', $this->selectedBatchId);
        }

        // Team filter
        if ($this->selectedTeamId) {
            $query->where('intern_team_id', $this->selectedTeamId);
        }

        // Domain filter
        if (filled($this->domainFilter)) {
            $query->where('domain', $this->domainFilter);
        }

        // Search box filter
        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('intern_code', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('college', 'like', $term)
                    ->orWhere('degree', 'like', $term)
                    ->orWhere('domain', 'like', $term)
                    ->orWhere('cohort_archive_name', 'like', $term)
                    ->orWhereHas('batch', fn ($b) => $b->where('batch_name', 'like', $term))
                    ->orWhereHas('team', fn ($t) => $t->where('team_name', 'like', $term)->orWhere('mentor_name', 'like', $term));
            });
        }

        // Custom Date Range filter
        if ($this->customStartDate && $this->customEndDate) {
            $field = in_array($this->dateField, ['joining_date', 'completion_date', 'created_at']) ? $this->dateField : 'joining_date';
            $query->whereBetween($field, [
                Carbon::parse($this->customStartDate)->startOfDay(),
                Carbon::parse($this->customEndDate)->endOfDay()
            ]);
        } elseif ($this->customStartDate) {
            $field = in_array($this->dateField, ['joining_date', 'completion_date', 'created_at']) ? $this->dateField : 'joining_date';
            $query->whereDate($field, '>=', $this->customStartDate);
        } elseif ($this->customEndDate) {
            $field = in_array($this->dateField, ['joining_date', 'completion_date', 'created_at']) ? $this->dateField : 'joining_date';
            $query->whereDate($field, '<=', $this->customEndDate);
        }

        return $query;
    }

    /**
     * CSV Stream Export
     */
    public function exportCsv(): StreamedResponse
    {
        $allTasks = Task::all();
        $allAssignments = TaskAssignment::all();

        // Fetch all matching records without pagination
        $interns = $this->getFilteredInternsQuery()->get();

        // Apply in-memory filters (attendance/task rate) & sorting if applicable
        $decorated = $interns->map(function ($intern) use ($allTasks, $allAssignments) {
            $intern->telemetry = $this->computeInternTelemetry($intern, $allTasks, $allAssignments);
            return $intern;
        });

        if ($this->attendanceFilter === 'high') {
            $decorated = $decorated->where('telemetry.attendance_pct', '>=', 90);
        } elseif ($this->attendanceFilter === 'medium') {
            $decorated = $decorated->whereBetween('telemetry.attendance_pct', [75, 89.9]);
        } elseif ($this->attendanceFilter === 'low') {
            $decorated = $decorated->where('telemetry.attendance_pct', '<', 75);
        }

        if ($this->taskFilter === 'completed') {
            $decorated = $decorated->where('telemetry.task_pct', '>=', 100);
        } elseif ($this->taskFilter === 'in_progress') {
            $decorated = $decorated->where('telemetry.pending_tasks', '>', 0);
        } elseif ($this->taskFilter === 'no_tasks') {
            $decorated = $decorated->where('telemetry.total_tasks', 0);
        }

        $allColDefs = $this->getColumnDefinitions();
        $cols = !empty($this->visibleColumns) ? $this->visibleColumns : array_keys($allColDefs);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="interns_comprehensive_report_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($decorated, $cols, $allColDefs) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header Row
            $headerRow = [];
            foreach ($cols as $col) {
                $headerRow[] = $allColDefs[$col] ?? ucfirst(str_replace('_', ' ', $col));
            }
            fputcsv($output, $headerRow);

            $srNo = 1;
            foreach ($decorated as $intern) {
                $tel = $intern->telemetry;
                $row = [];
                foreach ($cols as $col) {
                    $row[] = match ($col) {
                        'sr_no'               => $srNo,
                        'intern_code'         => $intern->intern_code ?: '—',
                        'name'                => $intern->name,
                        'status'              => $tel['is_archived'] ? 'Completed / Archived' : 'Active (In Progress)',
                        'cohort_archive_name' => $intern->cohort_archive_name ?: 'Current Active',
                        'email'               => $intern->email,
                        'phone'               => $intern->phone ?: '—',
                        'college'             => $intern->college ?: '—',
                        'degree'              => $intern->degree ?: '—',
                        'academic_year'       => $intern->academic_year ?: '—',
                        'domain'              => $intern->domain ?: '—',
                        'internship_role'     => $intern->internship_role ?: '—',
                        'batch_name'          => $intern->batch?->batch_name ?: 'Unassigned',
                        'batch_timing'        => $intern->batch?->batch_timing ?: '—',
                        'team_name'           => $intern->team?->team_name ?: ($intern->project_name ?: 'Unassigned'),
                        'mentor_name'         => $intern->team?->mentor_name ?: 'Mentor Not Assigned',
                        'joining_date'        => $intern->joining_date ? $intern->joining_date->format('d/m/Y') : '—',
                        'completion_date'     => $intern->completion_date ? $intern->completion_date->format('d/m/Y') : '—',
                        'attendance_pct'      => $tel['attendance_pct'] . '%',
                        'present_days'        => $tel['present'],
                        'wfh_days'            => $tel['wfh'],
                        'late_days'           => $tel['late'],
                        'absent_days'         => $tel['absent'],
                        'leave_days'          => $tel['leave'],
                        'total_working_days'  => $tel['working_days'],
                        'tasks_assigned'      => $tel['total_tasks'],
                        'tasks_completed'     => $tel['completed_tasks'],
                        'tasks_pending'       => $tel['pending_tasks'],
                        'task_completion_pct' => $tel['task_pct'] . '%',
                        'logbooks_count'      => $tel['logbooks_count'],
                        'cgpa'                => $intern->cgpa ?: '—',
                        'archive_note'        => $intern->archive_note ?: '—',
                        default               => $intern->{$col} ?? '—',
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
        // ── Pre-fetch Tasks & Assignments once in memory ──
        $allTasks = Task::all();
        $allAssignments = TaskAssignment::all();

        // ── Global KPI Counts across entire repository ──
        $totalRosterCount = Intern::count();
        $activeRosterCount = Intern::where('is_active', true)->whereNull('cohort_archive_name')->count();
        $archivedRosterCount = Intern::where(function ($q) {
            $q->where('is_active', false)->orWhereNotNull('cohort_archive_name');
        })->count();

        $archiveCycles = Intern::whereNotNull('cohort_archive_name')
            ->where('cohort_archive_name', '!=', '')
            ->distinct()
            ->pluck('cohort_archive_name')
            ->values()
            ->toArray();

        $batches = InternshipBatch::where('is_archived', false)->get();
        $teams = InternTeam::where('is_archived', false)->get();
        $domains = Intern::whereNotNull('domain')->where('domain', '!=', '')->distinct()->pluck('domain')->toArray();

        // ── Execute Filtered Query ──
        $query = $this->getFilteredInternsQuery();

        // In-memory telemetry decoration and sorting
        $allMatching = $query->get()->map(function ($intern) use ($allTasks, $allAssignments) {
            $intern->telemetry = $this->computeInternTelemetry($intern, $allTasks, $allAssignments);
            return $intern;
        });

        // Apply secondary in-memory filters
        if ($this->attendanceFilter === 'high') {
            $allMatching = $allMatching->where('telemetry.attendance_pct', '>=', 90);
        } elseif ($this->attendanceFilter === 'medium') {
            $allMatching = $allMatching->whereBetween('telemetry.attendance_pct', [75, 89.9]);
        } elseif ($this->attendanceFilter === 'low') {
            $allMatching = $allMatching->where('telemetry.attendance_pct', '<', 75);
        }

        if ($this->taskFilter === 'completed') {
            $allMatching = $allMatching->where('telemetry.task_pct', '>=', 100);
        } elseif ($this->taskFilter === 'in_progress') {
            $allMatching = $allMatching->where('telemetry.pending_tasks', '>', 0);
        } elseif ($this->taskFilter === 'no_tasks') {
            $allMatching = $allMatching->where('telemetry.total_tasks', 0);
        }

        // Apply sorting
        if ($this->sortBy === 'attendance_pct') {
            $allMatching = $this->sortDir === 'asc'
                ? $allMatching->sortBy('telemetry.attendance_pct')->values()
                : $allMatching->sortByDesc('telemetry.attendance_pct')->values();
        } elseif ($this->sortBy === 'task_pct') {
            $allMatching = $this->sortDir === 'asc'
                ? $allMatching->sortBy('telemetry.task_pct')->values()
                : $allMatching->sortByDesc('telemetry.task_pct')->values();
        } elseif ($this->sortBy === 'intern_code') {
            $allMatching = $this->sortDir === 'asc'
                ? $allMatching->sortBy('intern_code')->values()
                : $allMatching->sortByDesc('intern_code')->values();
        } elseif ($this->sortBy === 'joining_date') {
            $allMatching = $this->sortDir === 'asc'
                ? $allMatching->sortBy('joining_date')->values()
                : $allMatching->sortByDesc('joining_date')->values();
        } else {
            $allMatching = $this->sortDir === 'asc'
                ? $allMatching->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()
                : $allMatching->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }

        // ── Compute Filtered Scope Aggregates ──
        $filteredCount = $allMatching->count();
        $filteredActiveCount = $allMatching->where('telemetry.is_archived', false)->count();
        $filteredArchivedCount = $allMatching->where('telemetry.is_archived', true)->count();

        $avgAttendance = $filteredCount > 0
            ? round($allMatching->avg('telemetry.attendance_pct'), 1)
            : 0;

        $totalAssignedTasks = $allMatching->sum('telemetry.total_tasks');
        $totalCompletedTasks = $allMatching->sum('telemetry.completed_tasks');
        $overallTaskRate = $totalAssignedTasks > 0
            ? round(($totalCompletedTasks / $totalAssignedTasks) * 100, 1)
            : 0;

        $totalLogbooksSubmitted = $allMatching->sum('telemetry.logbooks_count');

        // Manual Pagination for memory-decorated collection
        $currentPage = $this->getPage();
        $perPageVal = $this->perPage > 0 ? $this->perPage : 9999;
        $paginatedItems = $allMatching->forPage($currentPage, $perPageVal)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $filteredCount,
            $perPageVal,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Fetch detail intern if modal is open
        $detailIntern = null;
        if ($this->showDetailModal && $this->selectedDetailInternId) {
            $detailIntern = Intern::with([
                'batch',
                'team',
                'attendances',
                'submissions.task',
                'logbooks',
                'offerletter',
                'completionCertificate',
                'completionLetter',
            ])->find($this->selectedDetailInternId);

            if ($detailIntern) {
                $detailIntern->telemetry = $this->computeInternTelemetry($detailIntern, $allTasks, $allAssignments);
            }
        }

        return [
            'interns'                => $paginator,
            'totalRosterCount'       => $totalRosterCount,
            'activeRosterCount'      => $activeRosterCount,
            'archivedRosterCount'    => $archivedRosterCount,
            'filteredCount'          => $filteredCount,
            'filteredActiveCount'    => $filteredActiveCount,
            'filteredArchivedCount'  => $filteredArchivedCount,
            'avgAttendance'          => $avgAttendance,
            'totalAssignedTasks'     => $totalAssignedTasks,
            'totalCompletedTasks'    => $totalCompletedTasks,
            'overallTaskRate'        => $overallTaskRate,
            'totalLogbooksSubmitted' => $totalLogbooksSubmitted,
            'archiveCycles'          => $archiveCycles,
            'batches'                => $batches,
            'teams'                  => $teams,
            'domains'                => $domains,
            'columnDefinitions'      => $this->getColumnDefinitions(),
            'detailIntern'           => $detailIntern,
        ];
    }
}
