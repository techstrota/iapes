<?php

namespace App\Filament\Resources\ApplicationReportResource\Pages;

use App\Filament\Resources\ApplicationReportResource;
use App\Models\InterviewManagement\Application;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationReport extends Page
{
    use WithPagination;

    protected static string $resource = ApplicationReportResource::class;

    protected static string $view = 'filament.resources.application-report-resource.pages.application-report';

    protected static ?string $title = 'Application Intelligence & Reports';

    // ── Archival Controls ────────────────────────────────────────────────────
    // 'all' (Active + Archived) | 'active' | 'archived'
    public string $archiveStatus = 'all';
    public string $selectedCohort = '';

    // ── Table Search & Filters ───────────────────────────────────────────────
    public string $search = '';
    public string $status = 'all';
    public string $domain = '';
    public ?float $minCgpa = null;
    public string $timeRange = 'all'; // 'all' | '7_days' | '30_days' | 'this_month' | 'last_month' | 'custom'
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';
    public int $perPage = 15;

    // ── Dynamic Column Visibility (All DB columns supported, ID removed, Sr. No. added by default) ──
    public array $visibleColumns = [
        'application_code',
        'name',
        'email',
        'phone',
        'college',
        'degree',
        'year',
        'cgpa',
        'domain',
        'duration',
        'duration_unit',
        'skills',
        'resume_path',
        'status',
        'is_archived',
        'cohort_archive_name',
        'archive_note',
        'archived_at',
        'created_at',
        'updated_at',
    ];

    // Reset pagination when any filter changes
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDomain(): void { $this->resetPage(); }
    public function updatingMinCgpa(): void { $this->resetPage(); }
    public function updatingTimeRange(): void { $this->resetPage(); }
    public function updatingCustomStartDate(): void { $this->resetPage(); }
    public function updatingCustomEndDate(): void { $this->resetPage(); }
    public function updatingArchiveStatus(): void { $this->resetPage(); }
    public function updatingSelectedCohort(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function setArchiveStatus(string $status): void
    {
        $this->archiveStatus = $status;
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->archiveStatus = 'all';
        $this->selectedCohort = '';
        $this->search = '';
        $this->status = 'all';
        $this->domain = '';
        $this->minCgpa = null;
        $this->timeRange = 'all';
        $this->customStartDate = null;
        $this->customEndDate = null;
        $this->sortBy = 'created_at';
        $this->sortDir = 'desc';
        $this->selectAllColumns();
        $this->resetPage();
    }

    // ── Dynamic Column Visibility Definitions (ID removed, grouped logically) ──
    public function getColumnDefinitionsProperty(): array
    {
        return [
            // Candidate & Academics
            'application_code'     => ['label' => 'Application Code',   'category' => 'academics', 'description' => 'Unique code (APP/...)'],
            'name'                 => ['label' => 'Name',               'category' => 'academics', 'description' => 'Applicant full name'],
            'email'                => ['label' => 'Email',              'category' => 'academics', 'description' => 'Primary contact email'],
            'phone'                => ['label' => 'Phone',              'category' => 'academics', 'description' => 'Primary contact phone'],
            'college'              => ['label' => 'College',            'category' => 'academics', 'description' => 'Institute / University'],
            'degree'               => ['label' => 'Degree',             'category' => 'academics', 'description' => 'Academic degree/program'],
            'year'                 => ['label' => 'Year',               'category' => 'academics', 'description' => 'Graduation year or semester'],
            'cgpa'                 => ['label' => 'CGPA',               'category' => 'academics', 'description' => 'Academic grade score'],

            // Track & Evaluation
            'domain'               => ['label' => 'Domain',             'category' => 'track',     'description' => 'Internship specialty role'],
            'duration'             => ['label' => 'Duration',           'category' => 'track',     'description' => 'Internship duration length'],
            'duration_unit'        => ['label' => 'Duration Unit',      'category' => 'track',     'description' => 'Duration unit (months, days)'],
            'skills'               => ['label' => 'Skills',             'category' => 'track',     'description' => 'Reported technical skills'],
            'resume_path'          => ['label' => 'Resume',             'category' => 'track',     'description' => 'Resume document link'],
            'status'               => ['label' => 'Status',             'category' => 'track',     'description' => 'Current recruitment stage'],

            // Archival & Audit
            'is_archived'          => ['label' => 'Archival State',     'category' => 'archive',   'description' => 'Active vs Archived status'],
            'cohort_archive_name'  => ['label' => 'Cohort Cycle',       'category' => 'archive',   'description' => 'Historical cohort cycle name'],
            'archive_note'         => ['label' => 'Archive Note',       'category' => 'archive',   'description' => 'Archival note/comment'],
            'archived_at'          => ['label' => 'Archived At',        'category' => 'archive',   'description' => 'Timestamp of archival'],
            'verification_token'   => ['label' => 'Verification Token', 'category' => 'archive',   'description' => 'Security token for verification'],
            'created_at'           => ['label' => 'Created At',         'category' => 'archive',   'description' => 'Submission timestamp'],
            'updated_at'           => ['label' => 'Updated At',         'category' => 'archive',   'description' => 'Last update timestamp'],
        ];
    }

    public function isColumnVisible(string $col): bool
    {
        return in_array($col, $this->visibleColumns, true);
    }

    public function toggleColumn(string $col): void
    {
        if (in_array($col, $this->visibleColumns, true)) {
            // Keep at least 1 column visible
            if (count($this->visibleColumns) > 1) {
                $this->visibleColumns = array_values(array_diff($this->visibleColumns, [$col]));
            }
        } else {
            $this->visibleColumns[] = $col;
        }
    }

    public function selectAllColumns(): void
    {
        $this->visibleColumns = array_keys($this->getColumnDefinitionsProperty());
    }

    public function resetColumns(): void
    {
        $this->selectAllColumns();
    }

    // ── Live KPI Metrics & Pie Chart Calculations ────────────────────────────
    public function getMetricsProperty(): array
    {
        $base = Application::whereNotIn('status', ['pending', 'verified']);

        $totalApplications = (clone $base)->count();
        $activeApplications = (clone $base)
            ->where(function ($q) {
                $q->where('is_archived', false)->orWhereNull('is_archived');
            })
            ->count();
        $archivedApplications = (clone $base)->where('is_archived', true)->count();

        $activePct = $totalApplications > 0 ? round(($activeApplications / $totalApplications) * 100, 1) : 0;
        $archivedPct = $totalApplications > 0 ? round(($archivedApplications / $totalApplications) * 100, 1) : 0;

        // Distinct recruitment cohorts
        $cohortsList = Application::whereNotNull('cohort_archive_name')
            ->where('cohort_archive_name', '!=', '')
            ->distinct()
            ->pluck('cohort_archive_name')
            ->filter()
            ->values()
            ->toArray();

        $cohortsCount = count($cohortsList);

        // Stage distribution
        $statusCounts = [
            'applied'              => (clone $base)->where('status', 'applied')->count(),
            'interview_scheduled'  => (clone $base)->where('status', 'interview_scheduled')->count(),
            'interviewed'          => (clone $base)->where('status', 'interviewed')->count(),
            'shortlisted'          => (clone $base)->where('status', 'shortlisted')->count(),
            'rejected'             => (clone $base)->where('status', 'rejected')->count(),
        ];

        $shortlisted = $statusCounts['shortlisted'];
        $conversionRate = $totalApplications > 0 ? round(($shortlisted / $totalApplications) * 100, 1) : 0;

        // Distinct domains for filter
        $domainsList = Application::whereNotNull('domain')
            ->where('domain', '!=', '')
            ->distinct()
            ->pluck('domain')
            ->sort()
            ->values()
            ->toArray();

        // SVG Donut Chart Calculation (circumference ≈ 364.4)
        $circumference = 364.4;
        $activeDash = $totalApplications > 0 ? round(($activeApplications / $totalApplications) * $circumference, 1) : 0;
        $archivedDash = $totalApplications > 0 ? round(($archivedApplications / $totalApplications) * $circumference, 1) : 0;
        $archivedOffset = -$activeDash;

        return [
            'totalApplications'    => $totalApplications,
            'activeApplications'   => $activeApplications,
            'archivedApplications' => $archivedApplications,
            'activePct'            => $activePct,
            'archivedPct'          => $archivedPct,
            'cohortsList'          => $cohortsList,
            'cohortsCount'         => $cohortsCount,
            'statusCounts'         => $statusCounts,
            'conversionRate'       => $conversionRate,
            'domainsList'          => $domainsList,
            'chart' => [
                'circumference'   => $circumference,
                'activeDash'      => $activeDash,
                'archivedDash'    => $archivedDash,
                'archivedOffset'  => $archivedOffset,
            ],
        ];
    }

    // ── Filtered Applications Query ──────────────────────────────────────────
    public function getApplicationsQuery()
    {
        $query = Application::query()
            ->whereNotIn('status', ['pending', 'verified']);

        // Archival filter logic
        if ($this->archiveStatus === 'active') {
            $query->where(function ($q) {
                $q->where('is_archived', false)->orWhereNull('is_archived');
            });
        } elseif ($this->archiveStatus === 'archived') {
            $query->where('is_archived', true);
        }
        // When 'all', include both active and archived applications

        // Cohort cycle filter (if chosen)
        if (!empty($this->selectedCohort)) {
            $query->where('cohort_archive_name', $this->selectedCohort);
        }

        // Status Filter
        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Domain Filter
        if (!empty($this->domain)) {
            $query->where('domain', $this->domain);
        }

        // Min CGPA Filter
        if ($this->minCgpa !== null && $this->minCgpa > 0) {
            $query->where('cgpa', '>=', $this->minCgpa);
        }

        // Time Range Filter
        if ($this->timeRange === '7_days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($this->timeRange === '30_days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($this->timeRange === 'this_month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        } elseif ($this->timeRange === 'last_month') {
            $query->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ]);
        } elseif ($this->timeRange === 'custom') {
            if ($this->customStartDate) {
                $query->whereDate('created_at', '>=', $this->customStartDate);
            }
            if ($this->customEndDate) {
                $query->whereDate('created_at', '<=', $this->customEndDate);
            }
        }

        // Search Filter
        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('application_code', 'like', "%{$search}%")
                  ->orWhere('college', 'like', "%{$search}%")
                  ->orWhere('degree', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('skills', 'like', "%{$search}%")
                  ->orWhere('cohort_archive_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $allowedSorts = [
            'application_code', 'name', 'email', 'phone', 'college', 'degree',
            'year', 'cgpa', 'domain', 'duration', 'duration_unit', 'status',
            'is_archived', 'cohort_archive_name', 'archived_at', 'created_at', 'updated_at'
        ];
        $sortCol = in_array($this->sortBy, $allowedSorts, true) ? $this->sortBy : 'created_at';
        $sortDirection = strtolower($this->sortDir) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortCol, $sortDirection);
    }

    public function getApplicationsProperty()
    {
        return $this->getApplicationsQuery()->paginate($this->perPage);
    }

    // ── Export CSV with Default "Sr. No." as first column ─────────────────────
    public function exportCsv(): StreamedResponse
    {
        $filename = 'application-report-' . now()->format('Y-m-d-His') . '.csv';
        $query = $this->getApplicationsQuery();
        $visible = $this->visibleColumns;
        $colDefs = $this->getColumnDefinitionsProperty();

        // Build CSV headers with "Sr. No." always prepended as the very first column
        $headers = ['Sr. No.'];
        foreach ($visible as $colKey) {
            $headers[] = $colDefs[$colKey]['label'] ?? $colKey;
        }

        return response()->streamDownload(function () use ($query, $visible, $headers) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM so Excel opens properly without garbled characters
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers);

            $srNo = 1;
            foreach ($query->cursor() as $app) {
                // Initialize row with sequential Sr. No. (1, 2, 3...)
                $row = [$srNo++];
                
                foreach ($visible as $colKey) {
                    switch ($colKey) {
                        case 'application_code':
                            $row[] = $app->application_code ?: ('APP/' . $app->id);
                            break;
                        case 'name':
                            $row[] = $app->name ?? '';
                            break;
                        case 'email':
                            $row[] = $app->email ?? '';
                            break;
                        case 'phone':
                            $row[] = $app->phone ?? '';
                            break;
                        case 'college':
                            $row[] = $app->college ?? '';
                            break;
                        case 'degree':
                            $row[] = $app->degree ?? '';
                            break;
                        case 'year':
                            $row[] = $app->year ?? '';
                            break;
                        case 'cgpa':
                            $row[] = $app->cgpa !== null ? number_format((float)$app->cgpa, 2) : '';
                            break;
                        case 'domain':
                            $row[] = $app->domain ?? '';
                            break;
                        case 'duration':
                            $row[] = $app->duration ?? '';
                            break;
                        case 'duration_unit':
                            $row[] = $app->duration_unit ?? '';
                            break;
                        case 'skills':
                            $row[] = $app->skills ?? '';
                            break;
                        case 'resume_path':
                            $row[] = $app->resume_path ? asset('storage/' . $app->resume_path) : 'No Resume';
                            break;
                        case 'status':
                            $row[] = ucfirst(str_replace('_', ' ', $app->status ?? 'applied'));
                            break;
                        case 'is_archived':
                            $row[] = $app->is_archived ? 'Archived' : 'Active';
                            break;
                        case 'cohort_archive_name':
                            $row[] = $app->cohort_archive_name ?? '';
                            break;
                        case 'archive_note':
                            $row[] = $app->archive_note ?? '';
                            break;
                        case 'archived_at':
                            $row[] = $app->archived_at ? $app->archived_at->format('Y-m-d H:i:s') : '';
                            break;
                        case 'verification_token':
                            $row[] = $app->verification_token ?? '';
                            break;
                        case 'created_at':
                            $row[] = $app->created_at ? $app->created_at->format('Y-m-d H:i:s') : '';
                            break;
                        case 'updated_at':
                            $row[] = $app->updated_at ? $app->updated_at->format('Y-m-d H:i:s') : '';
                            break;
                        default:
                            $row[] = '';
                    }
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
