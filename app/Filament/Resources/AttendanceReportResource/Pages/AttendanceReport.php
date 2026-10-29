<?php

namespace App\Filament\Resources\AttendanceReportResource\Pages;

use App\Filament\Resources\AttendanceReportResource;
use App\Models\Attendance;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceReport extends Page
{
    use WithPagination;

    protected static string $resource = AttendanceReportResource::class;

    protected static string $view = 'filament.resources.attendance-report-resource.pages.attendance-report';

    protected static ?string $title = 'Comprehensive Attendance Intelligence & Audit Report';

    // ── Scope: 'all' | 'active' | 'archived' ───────────────────────────────
    public string $activeScope = 'all';

    // ── Perspective View Mode: 'intern' | 'date' | 'matrix' ───────────────
    public string $viewMode = 'intern'; // 'intern', 'date', 'matrix'

    // ── Search & Filter Controls ──────────────────────────────────────────
    public string $search = '';
    public ?int $selectedBatchId = null;
    public ?int $selectedTeamId = null;
    public string $selectedCycle = '';
    public string $timeRange = 'all'; // 'all', 'this_month', 'last_month', '30_days', 'custom'
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
    public string $healthFilter = 'all'; // 'all', 'high' (>=90%), 'medium' (75-89%), 'low' (<75%)
    public string $statusFilter = 'all'; // 'all', 'present', 'wfh', 'late', 'absent', 'leave'
    public string $sortBy = 'name';
    public string $sortDir = 'asc';
    public int $perPage = 11;

    // ── Pop-up Modal State for Export ─────────────────────────────────────
    public bool $showExportModal = false;

    // ── Pop-up Modal State for Intern Attendance Dossier ───────────────────
    public bool $showDetailModal = false;
    public ?int $selectedDetailInternId = null;

    // ── Export Column Definitions ─────────────────────────────────────────
    public array $visibleColumns = [
        'sr_no',
        'date',
        'intern_code',
        'intern_name',
        'intern_status',
        'cohort_cycle',
        'batch_name',
        'team_name',
        'mentor_name',
        'status',
        'start_time',
        'end_time',
        'late_duration',
        'note',
    ];

    public function updatedActiveScope(): void { $this->resetPage(); }
    public function updatedViewMode(): void { $this->resetPage(); }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSelectedBatchId(): void { $this->resetPage(); }
    public function updatedSelectedTeamId(): void { $this->resetPage(); }
    public function updatedSelectedCycle(): void { $this->resetPage(); }
    public function updatedTimeRange(): void
    {
        if ($this->timeRange === 'this_month') {
            $this->customStartDate = now()->startOfMonth()->toDateString();
            $this->customEndDate = now()->endOfMonth()->toDateString();
        } elseif ($this->timeRange === 'last_month') {
            $this->customStartDate = now()->subMonth()->startOfMonth()->toDateString();
            $this->customEndDate = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($this->timeRange === '30_days') {
            $this->customStartDate = now()->subDays(30)->toDateString();
            $this->customEndDate = now()->toDateString();
        } elseif ($this->timeRange === 'all') {
            $this->customStartDate = null;
            $this->customEndDate = null;
        }
        $this->resetPage();
    }
    public function updatedCustomStartDate(): void
    {
        $this->timeRange = 'custom';
        $this->resetPage();
    }
    public function updatedCustomEndDate(): void
    {
        $this->timeRange = 'custom';
        $this->resetPage();
    }
    public function updatedHealthFilter(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedBatchId = null;
        $this->selectedTeamId = null;
        $this->selectedCycle = '';
        $this->timeRange = 'all';
        $this->customStartDate = null;
        $this->customEndDate = null;
        $this->healthFilter = 'all';
        $this->statusFilter = 'all';
        $this->sortBy = 'name';
        $this->sortDir = 'asc';
        $this->resetPage();
    }

    public function setPerspective(string $mode): void
    {
        if (in_array($mode, ['intern', 'date', 'matrix'])) {
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
        $this->visibleColumns = ['sr_no', 'date', 'intern_code', 'intern_name', 'status', 'start_time', 'end_time'];
    }

    public function openInternDossier(int $internId): void
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
            'sr_no'         => 'Sr. No.',
            'date'          => 'Attendance Date',
            'intern_code'   => 'Intern ID / Code',
            'intern_name'   => 'Intern Full Name',
            'intern_status' => 'Roster Status (Active / Archived)',
            'cohort_cycle'  => 'Archive Cycle / Cohort',
            'college'       => 'College / Institution',
            'degree'        => 'Degree / Program',
            'batch_name'    => 'Batch Name',
            'team_name'     => 'Project Squad',
            'mentor_name'   => 'Assigned Mentor',
            'status'        => 'Attendance Status',
            'start_time'    => 'In-Time (Entry)',
            'end_time'      => 'Out-Time (Exit)',
            'late_duration' => 'Late Duration',
            'note'          => 'Notes / Remarks',
        ];
    }

    /**
     * Resolves date bounds based on timeRange filter.
     */
    public function getDateRangeBounds(): array
    {
        if ($this->customStartDate || $this->customEndDate) {
            return [$this->customStartDate, $this->customEndDate];
        }

        $startDate = null;
        $endDate = null;

        if ($this->timeRange === 'this_month') {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        } elseif ($this->timeRange === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth()->toDateString();
            $endDate = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($this->timeRange === '30_days') {
            $startDate = now()->subDays(30)->toDateString();
            $endDate = now()->toDateString();
        }

        return [$startDate, $endDate];
    }

    /**
     * CSV Stream Export
     */
    public function exportCsv(): StreamedResponse
    {
        [$startDate, $endDate] = $this->getDateRangeBounds();

        $query = Attendance::with(['intern.batch', 'intern.team']);

        // Date range
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Intern Scope & Filters
        $query->whereHas('intern', function ($q) {
            if ($this->activeScope === 'active') {
                $q->where('is_active', true)->whereNull('cohort_archive_name');
            } elseif ($this->activeScope === 'archived') {
                $q->where(function ($sub) {
                    $sub->where('is_active', false)->orWhereNotNull('cohort_archive_name');
                });
            }

            if (filled($this->selectedCycle)) {
                $q->where('cohort_archive_name', $this->selectedCycle);
            }

            if ($this->selectedBatchId) {
                $q->where('internship_batch_id', $this->selectedBatchId);
            }

            if ($this->selectedTeamId) {
                $q->where('intern_team_id', $this->selectedTeamId);
            }

            if (filled($this->search)) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('intern_code', 'like', $term)
                        ->orWhere('college', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            }
        });

        $records = $query->orderBy('date', 'desc')->get();

        $allColDefs = $this->getColumnDefinitions();
        $cols = !empty($this->visibleColumns) ? $this->visibleColumns : array_keys($allColDefs);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendance_report_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($records, $cols, $allColDefs) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            $headerRow = [];
            foreach ($cols as $col) {
                $headerRow[] = $allColDefs[$col] ?? ucfirst(str_replace('_', ' ', $col));
            }
            fputcsv($output, $headerRow);

            $srNo = 1;
            foreach ($records as $att) {
                $intern = $att->intern;
                $isArchived = !$intern?->is_active || filled($intern?->cohort_archive_name);

                $row = [];
                foreach ($cols as $col) {
                    $row[] = match ($col) {
                        'sr_no'         => $srNo,
                        'date'          => $att->date ? Carbon::parse($att->date)->format('d/m/Y') : '—',
                        'intern_code'   => $intern?->intern_code ?: '—',
                        'intern_name'   => $intern?->name ?: '—',
                        'intern_status' => $isArchived ? 'Completed / Archived' : 'Active (In Progress)',
                        'cohort_cycle'  => $intern?->cohort_archive_name ?: 'Current Active',
                        'college'       => $intern?->college ?: '—',
                        'degree'        => $intern?->degree ?: '—',
                        'batch_name'    => $intern?->batch?->batch_name ?: 'Unassigned',
                        'team_name'     => $intern?->team?->team_name ?: 'Unassigned',
                        'mentor_name'   => $intern?->team?->mentor_name ?: 'Not Assigned',
                        'status'        => ucfirst($att->status),
                        'start_time'    => $att->start_time ?: '—',
                        'end_time'      => $att->end_time ?: '—',
                        'late_duration' => $att->formatted_late_duration ?: '—',
                        'note'          => $att->note ?: '—',
                        default         => '—',
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
        [$startDate, $endDate] = $this->getDateRangeBounds();

        // ── 1. Fetch Foundations ──
        $batches = InternshipBatch::where('is_archived', false)->get();
        $teams = InternTeam::where('is_archived', false)->get();
        $archiveCycles = Intern::whereNotNull('cohort_archive_name')
            ->where('cohort_archive_name', '!=', '')
            ->distinct()
            ->pluck('cohort_archive_name')
            ->values()
            ->toArray();

        // Fetch Interns matching Roster Scope & Filters
        $internsQuery = Intern::with(['batch', 'team', 'attendances' => function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $q->where('date', '>=', $startDate);
            } elseif ($endDate) {
                $q->where('date', '<=', $endDate);
            }
        }]);

        if ($this->activeScope === 'active') {
            $internsQuery->where('is_active', true)->whereNull('cohort_archive_name');
        } elseif ($this->activeScope === 'archived') {
            $internsQuery->where(function ($q) {
                $q->where('is_active', false)->orWhereNotNull('cohort_archive_name');
            });
        }

        if (filled($this->selectedCycle)) {
            $internsQuery->where('cohort_archive_name', $this->selectedCycle);
        }

        if ($this->selectedBatchId) {
            $internsQuery->where('internship_batch_id', $this->selectedBatchId);
        }

        if ($this->selectedTeamId) {
            $internsQuery->where('intern_team_id', $this->selectedTeamId);
        }

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $internsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('intern_code', 'like', $term)
                    ->orWhere('college', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        $allScopedInterns = $internsQuery->get();

        // ── 2. Perspective 1: Group By Intern ──
        $internPerspective = $allScopedInterns->map(function ($intern) {
            $atts = $intern->attendances ?? collect();
            $present = $atts->where('status', 'present')->count();
            $wfh = $atts->where('status', 'wfh')->count();
            $late = $atts->where('status', 'late')->count();
            $absent = $atts->where('status', 'absent')->count();
            $leave = $atts->where('status', 'leave')->count();
            $holiday = $atts->where('status', 'holiday')->count();
            $totalDays = $atts->count();

            $workingDays = $present + $wfh + $late + $absent + $leave;
            $attendedDays = $present + $wfh + $late;
            $attendancePct = $workingDays > 0 ? round(($attendedDays / $workingDays) * 100, 1) : ($totalDays > 0 ? 100.0 : 0.0);

            $punctualityPct = $attendedDays > 0 ? round((($present + $wfh) / $attendedDays) * 100, 1) : 100.0;
            $totalLateMinutes = $atts->where('status', 'late')->sum('late_minutes');

            $lateHours = floor($totalLateMinutes / 60);
            $lateMins = $totalLateMinutes % 60;
            $lateText = $totalLateMinutes > 0 ? ($lateHours > 0 ? "{$lateHours}h {$lateMins}m" : "{$lateMins}m") : '—';

            return [
                'intern'          => $intern,
                'total_days'      => $totalDays,
                'working_days'    => $workingDays,
                'attended_days'   => $attendedDays,
                'present'         => $present,
                'wfh'             => $wfh,
                'late'            => $late,
                'absent'          => $absent,
                'leave'           => $leave,
                'holiday'         => $holiday,
                'attendance_pct'  => $attendancePct,
                'punctuality_pct' => $punctualityPct,
                'late_text'       => $lateText,
                'late_minutes'    => $totalLateMinutes,
                'is_archived'     => !$intern->is_active || filled($intern->cohort_archive_name),
            ];
        });

        // Apply Health filter to intern perspective
        if ($this->healthFilter === 'high') {
            $internPerspective = $internPerspective->where('attendance_pct', '>=', 90)->values();
        } elseif ($this->healthFilter === 'medium') {
            $internPerspective = $internPerspective->whereBetween('attendance_pct', [75, 89.9])->values();
        } elseif ($this->healthFilter === 'low') {
            $internPerspective = $internPerspective->where('attendance_pct', '<', 75)->values();
        }

        // ── 3. Fetch All Attendance Records in Scope for Perspective 2 & 3 ──
        $attQuery = Attendance::with(['intern.batch', 'intern.team'])
            ->whereIn('intern_id', $allScopedInterns->pluck('id'));

        if ($startDate && $endDate) {
            $attQuery->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $attQuery->where('date', '>=', $startDate);
        } elseif ($endDate) {
            $attQuery->where('date', '<=', $endDate);
        }

        if ($this->statusFilter !== 'all') {
            $attQuery->where('status', $this->statusFilter);
        }

        $allScopedAttendances = $attQuery->orderBy('date', 'desc')->get();

        // ── 4. Perspective 2: Group By Date (Muster Timeline) ──
        $datePerspective = $allScopedAttendances->groupBy('date')->map(function ($items, $dateStr) use ($allScopedInterns) {
            $totalMarked = $items->count();
            $present = $items->where('status', 'present')->count();
            $wfh = $items->where('status', 'wfh')->count();
            $late = $items->where('status', 'late')->count();
            $absent = $items->where('status', 'absent')->count();
            $leave = $items->where('status', 'leave')->count();
            $holiday = $items->where('status', 'holiday')->count();

            $workingTotal = $present + $wfh + $late + $absent + $leave;
            $attendedTotal = $present + $wfh + $late;
            $dailyPct = $workingTotal > 0 ? round(($attendedTotal / $workingTotal) * 100, 1) : 0;

            return [
                'date'           => $dateStr,
                'carbon'         => Carbon::parse($dateStr),
                'total_marked'   => $totalMarked,
                'present'        => $present,
                'wfh'            => $wfh,
                'late'           => $late,
                'absent'         => $absent,
                'leave'          => $leave,
                'holiday'        => $holiday,
                'daily_pct'      => $dailyPct,
            ];
        })->sortByDesc('date')->values();

        // ── 5. Perspective 3: Granular Log Matrix ──
        $matrixPerspective = $allScopedAttendances->values();

        // ── 6. Global KPIs (from current scoped attendance records) ──
        $totalPresentOverall = $allScopedAttendances->where('status', 'present')->count();
        $totalWfhOverall = $allScopedAttendances->where('status', 'wfh')->count();
        $totalLateOverall = $allScopedAttendances->where('status', 'late')->count();
        $totalAbsentOverall = $allScopedAttendances->where('status', 'absent')->count();
        $totalLeaveOverall = $allScopedAttendances->where('status', 'leave')->count();

        $totalAttendedOverall = $totalPresentOverall + $totalWfhOverall + $totalLateOverall;
        $totalWorkingOverall = $totalAttendedOverall + $totalAbsentOverall + $totalLeaveOverall;

        $overallAttendanceRate = $totalWorkingOverall > 0
            ? round(($totalAttendedOverall / $totalWorkingOverall) * 100, 1)
            : 0;

        $totalLateMinutesOverall = $allScopedAttendances->where('status', 'late')->sum('late_minutes');
        $overallLateHours = floor($totalLateMinutesOverall / 60);
        $overallLateMins = $totalLateMinutesOverall % 60;
        $overallLateDurationText = $totalLateMinutesOverall > 0
            ? ($overallLateHours > 0 ? "{$overallLateHours}h {$overallLateMins}m" : "{$overallLateMins}m")
            : '0m';

        // ── 7. Sorting & Pagination ──
        $currentPage = $this->getPage();
        $perPageVal = $this->perPage > 0 ? $this->perPage : 9999;

        if ($this->viewMode === 'intern') {
            if ($this->sortBy === 'attendance_pct') {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('attendance_pct')->values()
                    : $internPerspective->sortByDesc('attendance_pct')->values();
            } elseif ($this->sortBy === 'attended_days') {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('attended_days')->values()
                    : $internPerspective->sortByDesc('attended_days')->values();
            } elseif ($this->sortBy === 'late') {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('late')->values()
                    : $internPerspective->sortByDesc('late')->values();
            } else {
                $internPerspective = $this->sortDir === 'asc'
                    ? $internPerspective->sortBy('intern.name', SORT_NATURAL | SORT_FLAG_CASE)->values()
                    : $internPerspective->sortByDesc('intern.name', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }

            $totalItems = $internPerspective->count();
            $paginatedItems = $internPerspective->forPage($currentPage, $perPageVal)->values();
        } elseif ($this->viewMode === 'date') {
            if ($this->sortBy === 'daily_pct') {
                $datePerspective = $this->sortDir === 'asc'
                    ? $datePerspective->sortBy('daily_pct')->values()
                    : $datePerspective->sortByDesc('daily_pct')->values();
            } else {
                $datePerspective = $this->sortDir === 'asc'
                    ? $datePerspective->sortBy('date')->values()
                    : $datePerspective->sortByDesc('date')->values();
            }

            $totalItems = $datePerspective->count();
            $paginatedItems = $datePerspective->forPage($currentPage, $perPageVal)->values();
        } else {
            // Matrix perspective
            if ($this->sortBy === 'intern') {
                $matrixPerspective = $this->sortDir === 'asc'
                    ? $matrixPerspective->sortBy('intern.name', SORT_NATURAL | SORT_FLAG_CASE)->values()
                    : $matrixPerspective->sortByDesc('intern.name', SORT_NATURAL | SORT_FLAG_CASE)->values();
            } else {
                $matrixPerspective = $this->sortDir === 'asc'
                    ? $matrixPerspective->sortBy('date')->values()
                    : $matrixPerspective->sortByDesc('date')->values();
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

        // Fetch detail intern dossier if modal is open
        $detailInternData = null;
        if ($this->showDetailModal && $this->selectedDetailInternId) {
            $intern = Intern::with(['batch', 'team', 'attendances'])->find($this->selectedDetailInternId);
            if ($intern) {
                $internAtts = $intern->attendances()->orderBy('date', 'desc')->get();
                $present = $internAtts->where('status', 'present')->count();
                $wfh = $internAtts->where('status', 'wfh')->count();
                $late = $internAtts->where('status', 'late')->count();
                $absent = $internAtts->where('status', 'absent')->count();
                $leave = $internAtts->where('status', 'leave')->count();
                $totalDays = $internAtts->count();
                $workingDays = $present + $wfh + $late + $absent + $leave;
                $attendedDays = $present + $wfh + $late;
                $rate = $workingDays > 0 ? round(($attendedDays / $workingDays) * 100, 1) : 0;

                $detailInternData = [
                    'intern'       => $intern,
                    'attendances'  => $internAtts,
                    'total_days'   => $totalDays,
                    'working_days' => $workingDays,
                    'present'      => $present,
                    'wfh'          => $wfh,
                    'late'         => $late,
                    'absent'       => $absent,
                    'leave'        => $leave,
                    'rate'         => $rate,
                ];
            }
        }

        return [
            'paginator'               => $paginator,
            'totalScopedInterns'      => $allScopedInterns->count(),
            'totalAttendedOverall'    => $totalAttendedOverall,
            'totalPresentOverall'     => $totalPresentOverall,
            'totalWfhOverall'         => $totalWfhOverall,
            'totalLateOverall'        => $totalLateOverall,
            'totalAbsentOverall'      => $totalAbsentOverall,
            'totalLeaveOverall'       => $totalLeaveOverall,
            'overallAttendanceRate'   => $overallAttendanceRate,
            'overallLateDurationText' => $overallLateDurationText,
            'batches'                 => $batches,
            'teams'                   => $teams,
            'archiveCycles'           => $archiveCycles,
            'columnDefinitions'       => $this->getColumnDefinitions(),
            'detailInternData'        => $detailInternData,
        ];
    }
}
