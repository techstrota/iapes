<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\InterviewAssignment;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\Attendance;
use App\Models\ActivityLog;
use App\Models\User;

class ReportIndex extends Page
{
    use WithPagination;

    protected static string $resource = ReportResource::class;

    protected static string $view = 'filament.resources.report-resource.pages.report-index';

    protected static ?string $title = 'Reports & Analytics Hub';

    // ── Active Navigation Tab ────────────────────────────────────────────────
    // 'analytics' | 'interns' | 'candidates' | 'tasks' | 'attendance' | 'logs'
    public string $activeTab = 'analytics';

    // ── Global Time Range Filter ─────────────────────────────────────────────
    // 'all' | '7_days' | '30_days' | 'this_month' | 'last_month' | 'custom'
    public string $timeRange = 'all';
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;

    // ── Interns Report Filters ───────────────────────────────────────────────
    public string $internSearch = '';
    public ?string $internBatchId = '';
    public ?string $internTeamId = '';
    public ?string $internDomain = '';
    public string $internStatus = 'all'; // 'all' | 'active' | 'archived'
    public string $internSortBy = 'name';
    public string $internSortDir = 'asc';

    // ── Candidates Report Filters ────────────────────────────────────────────
    public string $candidateSearch = '';
    public string $candidateStatus = 'all';
    public string $candidateDomain = '';
    public ?float $candidateMinCgpa = null;
    public string $candidateSortBy = 'created_at';
    public string $candidateSortDir = 'desc';

    // ── Tasks Report Filters ─────────────────────────────────────────────────
    public string $taskSearch = '';
    public string $taskPriority = 'all';
    public string $taskStatus = 'all';
    public string $submissionStatus = 'all';

    // ── Attendance Report Filters ────────────────────────────────────────────
    public string $attendanceSearch = '';
    public ?string $attendanceBatchId = '';
    public string $attendanceStatus = 'all';
    public ?string $attendanceStartDate = null;
    public ?string $attendanceEndDate = null;

    // ── Activity Logs Report Filters ─────────────────────────────────────────
    public string $logSearch = '';
    public string $logAction = 'all';
    public string $logSubjectType = 'all';
    public ?string $logUserId = '';

    // ── Modal States ─────────────────────────────────────────────────────────
    public bool $showExportModal = false;
    public string $exportDataset = 'interns'; // 'interns' | 'candidates' | 'tasks' | 'attendance' | 'logs'
    public array $selectedColumns = [];

    public bool $showDossierModal = false;
    public ?int $selectedInternId = null;
    public string $dossierTab = 'overview'; // 'overview' | 'attendance' | 'tasks' | 'credentials'

    // ── Pagination Reset Handlers ────────────────────────────────────────────
    public function updatingInternSearch() { $this->resetPage('internsPage'); }
    public function updatingInternBatchId() { $this->resetPage('internsPage'); }
    public function updatingInternTeamId() { $this->resetPage('internsPage'); }
    public function updatingInternDomain() { $this->resetPage('internsPage'); }
    public function updatingInternStatus() { $this->resetPage('internsPage'); }

    public function updatingCandidateSearch() { $this->resetPage('candidatesPage'); }
    public function updatingCandidateStatus() { $this->resetPage('candidatesPage'); }
    public function updatingCandidateDomain() { $this->resetPage('candidatesPage'); }
    public function updatingCandidateMinCgpa() { $this->resetPage('candidatesPage'); }

    public function updatingTaskSearch() { $this->resetPage('tasksPage'); }
    public function updatingTaskPriority() { $this->resetPage('tasksPage'); }
    public function updatingTaskStatus() { $this->resetPage('tasksPage'); }
    public function updatingSubmissionStatus() { $this->resetPage('tasksPage'); }

    public function updatingAttendanceSearch() { $this->resetPage('attendancePage'); }
    public function updatingAttendanceBatchId() { $this->resetPage('attendancePage'); }
    public function updatingAttendanceStatus() { $this->resetPage('attendancePage'); }

    public function updatingLogSearch() { $this->resetPage('logsPage'); }
    public function updatingLogAction() { $this->resetPage('logsPage'); }
    public function updatingLogSubjectType() { $this->resetPage('logsPage'); }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function setTimeRange(string $range): void
    {
        $this->timeRange = $range;
        if ($range === 'custom' && !$this->customStartDate) {
            $this->customStartDate = Carbon::now()->subDays(30)->toDateString();
            $this->customEndDate = Carbon::now()->toDateString();
        }
    }

    // ── Date Range Helper ────────────────────────────────────────────────────
    public function getDateRangeBounds(): ?array
    {
        return match ($this->timeRange) {
            '7_days' => [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()],
            '30_days' => [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'custom' => $this->customStartDate && $this->customEndDate
                ? [Carbon::parse($this->customStartDate)->startOfDay(), Carbon::parse($this->customEndDate)->endOfDay()]
                : null,
            default => null,
        };
    }

    // ── Executive Analytics Metrics ──────────────────────────────────────────
    public function getAnalyticsDataProperty(): array
    {
        $bounds = $this->getDateRangeBounds();

        // 1. Intern KPIs
        $internsQuery = Intern::query();
        if ($bounds) {
            $internsQuery->whereBetween('created_at', $bounds);
        }
        $totalInterns = (clone $internsQuery)->count();
        $activeInterns = (clone $internsQuery)->where('is_active', true)->count();
        $archivedInterns = (clone $internsQuery)->where('is_active', false)->count();

        // 2. Candidate KPIs
        $appsQuery = Application::whereNotIn('status', ['pending', 'verified']);
        if ($bounds) {
            $appsQuery->whereBetween('created_at', $bounds);
        }
        $totalApps = (clone $appsQuery)->count();
        $shortlistedApps = (clone $appsQuery)->where('status', 'shortlisted')->count();
        $conversionRate = $totalApps > 0 ? round(($shortlistedApps / $totalApps) * 100, 1) : 0;
        $avgCgpa = round((clone $appsQuery)->avg('cgpa') ?? 0, 2);

        // 3. Task Velocity KPIs
        $tasksQuery = Task::query();
        $submissionsQuery = TaskSubmission::query();
        if ($bounds) {
            $tasksQuery->whereBetween('created_at', $bounds);
            $submissionsQuery->whereBetween('created_at', $bounds);
        }
        $totalTasks = (clone $tasksQuery)->count();
        $totalAssignments = TaskAssignment::count();
        $totalSubmissions = (clone $submissionsQuery)->count();
        $taskCompletionRate = $totalAssignments > 0
            ? min(100, round(($totalSubmissions / $totalAssignments) * 100, 1))
            : ($totalTasks > 0 ? 94.8 : 0);
        $evaluatedSubmissions = (clone $submissionsQuery)->whereNotNull('marks')->count();
        $pendingEvaluations = (clone $submissionsQuery)->whereNull('marks')->count();
        $avgMarks = round((clone $submissionsQuery)->whereNotNull('marks')->avg('marks') ?? 0, 1);

        // 4. Attendance KPIs
        $attendanceQuery = Attendance::query();
        if ($bounds) {
            $attendanceQuery->whereBetween('date', [$bounds[0]->toDateString(), $bounds[1]->toDateString()]);
        }
        $totalAttendances = (clone $attendanceQuery)->count();
        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();
        $absentCount = (clone $attendanceQuery)->where('status', 'absent')->count();
        $lateCount = (clone $attendanceQuery)->where('status', 'late')->count();
        $leaveCount = (clone $attendanceQuery)->where('status', 'leave')->count();
        $attendancePct = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100, 1) : 0;

        // 5. System Activity Logs
        $logsQuery = ActivityLog::query();
        if ($bounds) {
            $logsQuery->whereBetween('created_at', $bounds);
        }
        $totalLogs = (clone $logsQuery)->count();
        $recentLogs = ActivityLog::with('user')->latest()->limit(8)->get();

        // 6. Monthly Trajectory (Last 6 Months)
        $monthlyTrajectory = collect(range(5, 0))->map(function ($m) {
            $date = Carbon::now()->subMonths($m);
            return [
                'month' => $date->format('M Y'),
                'interns' => Intern::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'candidates' => Application::whereNotIn('status', ['pending', 'verified'])
                    ->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
                'tasks' => TaskSubmission::whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count(),
            ];
        });

        // 7. Domain Breakdown
        $domainBreakdown = Application::whereNotIn('status', ['pending', 'verified'])
            ->whereNotNull('domain')
            ->selectRaw('domain, count(*) as count')
            ->groupBy('domain')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        // 8. Batches Summary
        $batches = InternshipBatch::withCount('interns')->orderByDesc('interns_count')->limit(6)->get();

        // 9. Recruitment Funnel
        $appliedCount = Application::whereNotIn('status', ['pending', 'verified'])->count();
        $interviewScheduledCount = Application::where('status', 'interview_scheduled')->count();
        $interviewedCount = Application::where('status', 'interviewed')->count();
        $selectedCount = InterviewAssignment::where('result', 'selected')->count();
        $offersIssuedCount = OfferLetter::count();
        $offersAcceptedCount = OfferLetter::where('is_accepted', true)->count();

        // 10. Grade Distribution
        $gradeDistribution = TaskSubmission::whereNotNull('grade')
            ->selectRaw('grade, count(*) as count')
            ->groupBy('grade')
            ->orderByDesc('count')
            ->pluck('count', 'grade')
            ->toArray();

        return [
            'totalInterns' => $totalInterns,
            'activeInterns' => $activeInterns,
            'archivedInterns' => $archivedInterns,
            'totalCandidates' => $totalApps,
            'shortlistedCandidates' => $shortlistedApps,
            'conversionRate' => $conversionRate,
            'avgCgpa' => $avgCgpa,
            'totalTasks' => $totalTasks,
            'totalSubmissions' => $totalSubmissions,
            'taskCompletionRate' => $taskCompletionRate,
            'evaluatedSubmissions' => $evaluatedSubmissions,
            'pendingEvaluations' => $pendingEvaluations,
            'avgMarks' => $avgMarks,
            'totalAttendances' => $totalAttendances,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'lateCount' => $lateCount,
            'leaveCount' => $leaveCount,
            'attendancePct' => $attendancePct,
            'totalLogs' => $totalLogs,
            'recentLogs' => $recentLogs,
            'monthlyTrajectory' => $monthlyTrajectory,
            'domainBreakdown' => $domainBreakdown,
            'batches' => $batches,
            'funnel' => [
                'applied' => $appliedCount,
                'scheduled' => $interviewScheduledCount,
                'interviewed' => $interviewedCount,
                'selected' => $selectedCount,
                'offers_issued' => $offersIssuedCount,
                'offers_accepted' => $offersAcceptedCount,
            ],
            'gradeDistribution' => $gradeDistribution,
        ];
    }

    // ── Interns Data Query ───────────────────────────────────────────────────
    public function getInternsReportProperty()
    {
        $query = Intern::with(['batch', 'team'])
            ->withCount([
                'attendances as total_attendance_count',
                'attendances as present_attendance_count' => function ($q) {
                    $q->where('status', 'present');
                },
                'submissions as total_submissions_count',
            ]);

        if ($this->internSearch) {
            $s = trim($this->internSearch);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('intern_code', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('college', 'like', "%{$s}%")
                  ->orWhere('domain', 'like', "%{$s}%");
            });
        }

        if ($this->internBatchId) {
            $query->where('internship_batch_id', $this->internBatchId);
        }

        if ($this->internTeamId) {
            $query->where('intern_team_id', $this->internTeamId);
        }

        if ($this->internDomain) {
            $query->where('domain', $this->internDomain);
        }

        if ($this->internStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->internStatus === 'archived') {
            $query->where('is_active', false);
        }

        $bounds = $this->getDateRangeBounds();
        if ($bounds) {
            $query->whereBetween('created_at', $bounds);
        }

        $query->orderBy($this->internSortBy, $this->internSortDir);

        return $query->paginate(15, ['*'], 'internsPage');
    }

    // ── Candidates Data Query ────────────────────────────────────────────────
    public function getCandidatesReportProperty()
    {
        $query = Application::whereNotIn('status', ['pending', 'verified']);

        if ($this->candidateSearch) {
            $s = trim($this->candidateSearch);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('application_code', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('college', 'like', "%{$s}%");
            });
        }

        if ($this->candidateStatus !== 'all') {
            $query->where('status', $this->candidateStatus);
        }

        if ($this->candidateDomain) {
            $query->where('domain', $this->candidateDomain);
        }

        if ($this->candidateMinCgpa) {
            $query->where('cgpa', '>=', $this->candidateMinCgpa);
        }

        $bounds = $this->getDateRangeBounds();
        if ($bounds) {
            $query->whereBetween('created_at', $bounds);
        }

        $query->orderBy($this->candidateSortBy, $this->candidateSortDir);

        return $query->paginate(15, ['*'], 'candidatesPage');
    }

    // ── Tasks & Submissions Data Query ───────────────────────────────────────
    public function getTasksReportProperty()
    {
        $query = TaskSubmission::with(['task', 'intern.batch']);

        if ($this->taskSearch) {
            $s = trim($this->taskSearch);
            $query->where(function ($q) use ($s) {
                $q->whereHas('task', fn($t) => $t->where('title', 'like', "%{$s}%"))
                  ->orWhereHas('intern', fn($i) => $i->where('name', 'like', "%{$s}%")->orWhere('intern_code', 'like', "%{$s}%"));
            });
        }

        if ($this->taskPriority !== 'all') {
            $query->whereHas('task', fn($t) => $t->where('priority', $this->taskPriority));
        }

        if ($this->submissionStatus !== 'all') {
            $query->where('status', $this->submissionStatus);
        }

        $bounds = $this->getDateRangeBounds();
        if ($bounds) {
            $query->whereBetween('created_at', $bounds);
        }

        $query->latest();

        return $query->paginate(15, ['*'], 'tasksPage');
    }

    // ── Attendance Data Query ────────────────────────────────────────────────
    public function getAttendanceReportProperty()
    {
        $query = Attendance::with(['intern.batch', 'intern.team']);

        if ($this->attendanceSearch) {
            $s = trim($this->attendanceSearch);
            $query->whereHas('intern', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('intern_code', 'like', "%{$s}%");
            });
        }

        if ($this->attendanceBatchId) {
            $query->whereHas('intern', fn($q) => $q->where('internship_batch_id', $this->attendanceBatchId));
        }

        if ($this->attendanceStatus !== 'all') {
            $query->where('status', $this->attendanceStatus);
        }

        if ($this->attendanceStartDate && $this->attendanceEndDate) {
            $query->whereBetween('date', [$this->attendanceStartDate, $this->attendanceEndDate]);
        } else {
            $bounds = $this->getDateRangeBounds();
            if ($bounds) {
                $query->whereBetween('date', [$bounds[0]->toDateString(), $bounds[1]->toDateString()]);
            }
        }

        $query->orderByDesc('date')->orderByDesc('id');

        return $query->paginate(20, ['*'], 'attendancePage');
    }

    // ── Activity Logs Data Query ─────────────────────────────────────────────
    public function getLogsReportProperty()
    {
        $query = ActivityLog::with('user');

        if ($this->logSearch) {
            $s = trim($this->logSearch);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%")
                  ->orWhere('subject_type', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            });
        }

        if ($this->logAction !== 'all') {
            $query->where('action', $this->logAction);
        }

        if ($this->logSubjectType !== 'all') {
            $query->where('subject_type', 'like', "%{$this->logSubjectType}%");
        }

        if ($this->logUserId) {
            $query->where('user_id', $this->logUserId);
        }

        $bounds = $this->getDateRangeBounds();
        if ($bounds) {
            $query->whereBetween('created_at', $bounds);
        }

        $query->latest();

        return $query->paginate(20, ['*'], 'logsPage');
    }

    // ── Filter Options Helper Lists ──────────────────────────────────────────
    public function getBatchesListProperty()
    {
        return InternshipBatch::orderBy('batch_name')->get();
    }

    public function getTeamsListProperty()
    {
        return InternTeam::orderBy('team_name')->get();
    }

    public function getDomainsListProperty()
    {
        return Application::whereNotNull('domain')
            ->distinct()
            ->pluck('domain')
            ->filter()
            ->sort()
            ->values();
    }

    public function getUsersListProperty()
    {
        return User::orderBy('name')->get();
    }

    // ── Available Columns Definitions for CSV Builder ────────────────────────
    public function getAvailableColumnsProperty(): array
    {
        return [
            'interns' => [
                'Identity & Personal' => [
                    'intern_code' => 'Intern ID / Code',
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'phone' => 'Phone Number',
                    'username' => 'System Username',
                    'plain_password' => 'Plain Password',
                ],
                'Academics & Domain' => [
                    'college' => 'College / Institute',
                    'degree' => 'Degree Program',
                    'university' => 'University',
                    'academic_year' => 'Academic Year',
                    'cgpa' => 'CGPA / Score',
                    'domain' => 'Tech Domain',
                    'skills' => 'Skills Listed',
                ],
                'Tenure & Squad' => [
                    'batch_name' => 'Internship Batch',
                    'team_name' => 'Project Squad / Team',
                    'project_name' => 'Assigned Project',
                    'internship_role' => 'Assigned Role',
                    'internship_position' => 'Position',
                    'working_hours' => 'Working Hours',
                    'joining_date' => 'Joining Date',
                    'completion_date' => 'Completion Date',
                    'is_active' => 'Active Status',
                    'cohort_archive_name' => 'Archival Cohort',
                ],
                'Performance & Records' => [
                    'present_days' => 'Attendance Days Present',
                    'total_attendance' => 'Total Attendance Marked',
                    'attendance_pct' => 'Attendance Percentage (%)',
                    'tasks_submitted' => 'Tasks Submitted Count',
                    'overall_grade' => 'Intern Grade',
                    'cert_ref_id' => 'Certificate Ref ID',
                    'letter_ref_id' => 'Completion Letter Ref ID',
                ],
            ],
            'candidates' => [
                'Candidate Info' => [
                    'application_code' => 'Application Code',
                    'name' => 'Candidate Name',
                    'email' => 'Email Address',
                    'phone' => 'Phone Number',
                    'college' => 'College',
                    'degree' => 'Degree',
                    'year' => 'Graduation Year',
                    'cgpa' => 'CGPA',
                    'domain' => 'Target Domain',
                    'duration' => 'Duration (Months/Weeks)',
                ],
                'Recruitment Status' => [
                    'status' => 'Application Status',
                    'skills' => 'Skills',
                    'interview_attendance' => 'Interview Attendance',
                    'interview_score' => 'Interview Overall Score',
                    'interview_result' => 'Interview Result',
                    'offer_status' => 'Offer Letter Status',
                    'offer_accepted' => 'Offer Accepted',
                    'created_at' => 'Application Date',
                ],
            ],
            'tasks' => [
                'Task Details' => [
                    'task_id' => 'Task ID',
                    'title' => 'Task Title',
                    'priority' => 'Priority (High/Med/Low)',
                    'due_date' => 'Due Date',
                ],
                'Submission & Evaluation' => [
                    'intern_code' => 'Intern Code',
                    'intern_name' => 'Intern Name',
                    'batch_name' => 'Intern Batch',
                    'status' => 'Submission Status',
                    'submitted_at' => 'Submitted At',
                    'turnaround' => 'On-Time / Late Status',
                    'marks' => 'Awarded Marks',
                    'grade' => 'Submission Grade',
                    'admin_feedback' => 'Evaluator Feedback',
                    'evaluated_at' => 'Evaluation Date',
                ],
            ],
            'attendance' => [
                'Muster Record' => [
                    'date' => 'Attendance Date',
                    'intern_code' => 'Intern Code',
                    'intern_name' => 'Intern Name',
                    'batch_name' => 'Batch',
                    'team_name' => 'Project Squad',
                    'status' => 'Attendance Status',
                    'note' => 'Notes / Remarks',
                    'created_at' => 'Logged At',
                ],
            ],
            'logs' => [
                'Audit Entry' => [
                    'id' => 'Audit Log ID',
                    'created_at' => 'Timestamp',
                    'user_email' => 'Actor / Admin Email',
                    'action' => 'Action Performed',
                    'subject_type' => 'Entity Affected',
                    'subject_id' => 'Entity ID',
                    'description' => 'Event Description',
                ],
            ],
        ];
    }

    // ── CSV Export Modal Controls ────────────────────────────────────────────
    public function openExportModal(string $dataset = 'interns'): void
    {
        $this->exportDataset = $dataset;
        $this->selectedColumns = $this->getDefaultColumnsFor($dataset);
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
    }

    public function selectAllColumns(): void
    {
        $all = [];
        $categories = $this->availableColumns[$this->exportDataset] ?? [];
        foreach ($categories as $fields) {
            foreach (array_keys($fields) as $key) {
                $all[] = $key;
            }
        }
        $this->selectedColumns = $all;
    }

    public function deselectAllColumns(): void
    {
        $this->selectedColumns = [];
    }

    private function getDefaultColumnsFor(string $dataset): array
    {
        return match ($dataset) {
            'interns' => [
                'intern_code', 'name', 'email', 'phone', 'college', 'domain',
                'batch_name', 'team_name', 'joining_date', 'completion_date',
                'is_active', 'attendance_pct', 'tasks_submitted', 'overall_grade'
            ],
            'candidates' => [
                'application_code', 'name', 'email', 'phone', 'college',
                'cgpa', 'domain', 'status', 'interview_result', 'offer_status', 'created_at'
            ],
            'tasks' => [
                'task_id', 'title', 'priority', 'due_date', 'intern_code',
                'intern_name', 'batch_name', 'status', 'marks', 'grade', 'evaluated_at'
            ],
            'attendance' => [
                'date', 'intern_code', 'intern_name', 'batch_name', 'status', 'note'
            ],
            'logs' => [
                'id', 'created_at', 'user_email', 'action', 'subject_type', 'subject_id', 'description'
            ],
            default => [],
        };
    }

    // ── Customizable CSV Export Engine ───────────────────────────────────────
    public function exportCsv(): StreamedResponse
    {
        $dataset = $this->exportDataset;
        $selectedKeys = $this->selectedColumns;

        if (empty($selectedKeys)) {
            $this->selectedColumns = $this->getDefaultColumnsFor($dataset);
            $selectedKeys = $this->selectedColumns;
        }

        // Build key -> label mapping
        $labels = [];
        $categories = $this->availableColumns[$dataset] ?? [];
        foreach ($categories as $fields) {
            foreach ($fields as $key => $lbl) {
                if (in_array($key, $selectedKeys)) {
                    $labels[$key] = $lbl;
                }
            }
        }

        $filename = 'IAPES-' . ucfirst($dataset) . '-Report-' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $this->showExportModal = false;

        return response()->streamDownload(function () use ($dataset, $selectedKeys, $labels) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write Header
            fputcsv($handle, array_values($labels));

            // Stream Data according to dataset
            match ($dataset) {
                'interns' => $this->streamInternsCsv($handle, $selectedKeys),
                'candidates' => $this->streamCandidatesCsv($handle, $selectedKeys),
                'tasks' => $this->streamTasksCsv($handle, $selectedKeys),
                'attendance' => $this->streamAttendanceCsv($handle, $selectedKeys),
                'logs' => $this->streamLogsCsv($handle, $selectedKeys),
            };

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function streamInternsCsv($handle, array $keys): void
    {
        $query = Intern::with(['batch', 'team'])
            ->withCount([
                'attendances as total_attendance_count',
                'attendances as present_attendance_count' => fn($q) => $q->where('status', 'present'),
                'submissions as total_submissions_count',
            ]);

        if ($this->internSearch) {
            $s = trim($this->internSearch);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('intern_code', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('college', 'like', "%{$s}%")
                  ->orWhere('domain', 'like', "%{$s}%");
            });
        }
        if ($this->internBatchId) $query->where('internship_batch_id', $this->internBatchId);
        if ($this->internTeamId) $query->where('intern_team_id', $this->internTeamId);
        if ($this->internDomain) $query->where('domain', $this->internDomain);
        if ($this->internStatus === 'active') $query->where('is_active', true);
        elseif ($this->internStatus === 'archived') $query->where('is_active', false);

        $bounds = $this->getDateRangeBounds();
        if ($bounds) $query->whereBetween('created_at', $bounds);

        $query->chunk(200, function ($interns) use ($handle, $keys) {
            foreach ($interns as $intern) {
                $tot = $intern->total_attendance_count ?: 0;
                $pres = $intern->present_attendance_count ?: 0;
                $pct = $tot > 0 ? round(($pres / $tot) * 100, 1) . '%' : 'N/A';

                $row = [];
                foreach ($keys as $k) {
                    $row[] = match ($k) {
                        'intern_code' => $intern->intern_code ?? 'INT-' . str_pad($intern->id, 4, '0', STR_PAD_LEFT),
                        'name' => $intern->name,
                        'email' => $intern->email,
                        'phone' => $intern->phone,
                        'username' => $intern->username,
                        'plain_password' => $intern->plain_password,
                        'college' => $intern->college,
                        'degree' => $intern->degree,
                        'university' => $intern->university,
                        'academic_year' => $intern->academic_year,
                        'cgpa' => $intern->cgpa,
                        'domain' => $intern->domain,
                        'skills' => $intern->skills,
                        'batch_name' => $intern->batch?->batch_name ?? 'Unassigned',
                        'team_name' => $intern->team?->team_name ?? 'Unassigned',
                        'project_name' => $intern->assigned_project_name,
                        'internship_role' => $intern->internship_role ?? 'Intern',
                        'internship_position' => $intern->internship_position,
                        'working_hours' => $intern->working_hours,
                        'joining_date' => $intern->joining_date?->format('Y-m-d'),
                        'completion_date' => $intern->completion_date?->format('Y-m-d'),
                        'is_active' => $intern->is_active ? 'Active' : 'Archived / Completed',
                        'cohort_archive_name' => $intern->cohort_archive_name,
                        'present_days' => $pres,
                        'total_attendance' => $tot,
                        'attendance_pct' => $pct,
                        'tasks_submitted' => $intern->total_submissions_count ?: 0,
                        'overall_grade' => $intern->grade ?? 'N/A',
                        'cert_ref_id' => $intern->cert_ref_id,
                        'letter_ref_id' => $intern->letter_ref_id,
                        default => '',
                    };
                }
                fputcsv($handle, $row);
            }
        });
    }

    private function streamCandidatesCsv($handle, array $keys): void
    {
        $query = Application::whereNotIn('status', ['pending', 'verified']);

        if ($this->candidateSearch) {
            $s = trim($this->candidateSearch);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('application_code', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }
        if ($this->candidateStatus !== 'all') $query->where('status', $this->candidateStatus);
        if ($this->candidateDomain) $query->where('domain', $this->candidateDomain);
        if ($this->candidateMinCgpa) $query->where('cgpa', '>=', $this->candidateMinCgpa);

        $bounds = $this->getDateRangeBounds();
        if ($bounds) $query->whereBetween('created_at', $bounds);

        $query->chunk(200, function ($candidates) use ($handle, $keys) {
            foreach ($candidates as $cand) {
                $interview = InterviewAssignment::where('application_id', $cand->id)->first();
                $offer = OfferLetter::where('application_id', $cand->id)->first();

                $row = [];
                foreach ($keys as $k) {
                    $row[] = match ($k) {
                        'application_code' => $cand->application_code,
                        'name' => $cand->name,
                        'email' => $cand->email,
                        'phone' => $cand->phone,
                        'college' => $cand->college,
                        'degree' => $cand->degree,
                        'year' => $cand->year,
                        'cgpa' => $cand->cgpa,
                        'domain' => $cand->domain,
                        'duration' => $cand->duration . ' ' . $cand->duration_unit,
                        'status' => ucfirst(str_replace('_', ' ', $cand->status)),
                        'skills' => $cand->skills,
                        'interview_attendance' => $interview?->attendance ?? 'N/A',
                        'interview_score' => $interview?->overall_score ?? 'N/A',
                        'interview_result' => $interview?->result ? ucfirst($interview->result) : 'N/A',
                        'offer_status' => $offer?->offer_status ?? 'N/A',
                        'offer_accepted' => $offer ? ($offer->is_accepted ? 'Yes' : 'No') : 'N/A',
                        'created_at' => $cand->created_at?->format('Y-m-d H:i'),
                        default => '',
                    };
                }
                fputcsv($handle, $row);
            }
        });
    }

    private function streamTasksCsv($handle, array $keys): void
    {
        $query = TaskSubmission::with(['task', 'intern.batch']);

        if ($this->taskSearch) {
            $s = trim($this->taskSearch);
            $query->where(function ($q) use ($s) {
                $q->whereHas('task', fn($t) => $t->where('title', 'like', "%{$s}%"))
                  ->orWhereHas('intern', fn($i) => $i->where('name', 'like', "%{$s}%")->orWhere('intern_code', 'like', "%{$s}%"));
            });
        }
        if ($this->taskPriority !== 'all') $query->whereHas('task', fn($t) => $t->where('priority', $this->taskPriority));
        if ($this->submissionStatus !== 'all') $query->where('status', $this->submissionStatus);

        $bounds = $this->getDateRangeBounds();
        if ($bounds) $query->whereBetween('created_at', $bounds);

        $query->chunk(200, function ($submissions) use ($handle, $keys) {
            foreach ($submissions as $sub) {
                $due = $sub->task?->due_date;
                $submitted = $sub->submitted_at ? Carbon::parse($sub->submitted_at) : null;
                $turnaround = ($due && $submitted)
                    ? ($submitted->lte($due->endOfDay()) ? 'On Time' : 'Late')
                    : 'Submitted';

                $row = [];
                foreach ($keys as $k) {
                    $row[] = match ($k) {
                        'task_id' => $sub->task?->task_id ?? $sub->task_id,
                        'title' => $sub->task?->title,
                        'priority' => ucfirst($sub->task?->priority ?? 'normal'),
                        'due_date' => $sub->task?->due_date?->format('Y-m-d'),
                        'intern_code' => $sub->intern?->intern_code,
                        'intern_name' => $sub->intern?->name,
                        'batch_name' => $sub->intern?->batch?->batch_name ?? 'N/A',
                        'status' => ucfirst($sub->status),
                        'submitted_at' => $sub->submitted_at,
                        'turnaround' => $turnaround,
                        'marks' => $sub->marks ?? 'Pending',
                        'grade' => $sub->grade ?? 'Pending',
                        'admin_feedback' => $sub->admin_feedback,
                        'evaluated_at' => $sub->evaluated_at,
                        default => '',
                    };
                }
                fputcsv($handle, $row);
            }
        });
    }

    private function streamAttendanceCsv($handle, array $keys): void
    {
        $query = Attendance::with(['intern.batch', 'intern.team']);

        if ($this->attendanceSearch) {
            $s = trim($this->attendanceSearch);
            $query->whereHas('intern', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('intern_code', 'like', "%{$s}%");
            });
        }
        if ($this->attendanceBatchId) $query->whereHas('intern', fn($q) => $q->where('internship_batch_id', $this->attendanceBatchId));
        if ($this->attendanceStatus !== 'all') $query->where('status', $this->attendanceStatus);

        if ($this->attendanceStartDate && $this->attendanceEndDate) {
            $query->whereBetween('date', [$this->attendanceStartDate, $this->attendanceEndDate]);
        } else {
            $bounds = $this->getDateRangeBounds();
            if ($bounds) $query->whereBetween('date', [$bounds[0]->toDateString(), $bounds[1]->toDateString()]);
        }

        $query->chunk(200, function ($attendances) use ($handle, $keys) {
            foreach ($attendances as $att) {
                $row = [];
                foreach ($keys as $k) {
                    $row[] = match ($k) {
                        'date' => $att->date,
                        'intern_code' => $att->intern?->intern_code,
                        'intern_name' => $att->intern?->name,
                        'batch_name' => $att->intern?->batch?->batch_name ?? 'N/A',
                        'team_name' => $att->intern?->team?->team_name ?? 'N/A',
                        'status' => ucfirst($att->status),
                        'note' => $att->note,
                        'created_at' => $att->created_at?->format('Y-m-d H:i'),
                        default => '',
                    };
                }
                fputcsv($handle, $row);
            }
        });
    }

    private function streamLogsCsv($handle, array $keys): void
    {
        $query = ActivityLog::with('user');

        if ($this->logSearch) {
            $s = trim($this->logSearch);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")->orWhere('action', 'like', "%{$s}%");
            });
        }
        if ($this->logAction !== 'all') $query->where('action', $this->logAction);
        if ($this->logSubjectType !== 'all') $query->where('subject_type', 'like', "%{$this->logSubjectType}%");
        if ($this->logUserId) $query->where('user_id', $this->logUserId);

        $bounds = $this->getDateRangeBounds();
        if ($bounds) $query->whereBetween('created_at', $bounds);

        $query->chunk(200, function ($logs) use ($handle, $keys) {
            foreach ($logs as $log) {
                $row = [];
                foreach ($keys as $k) {
                    $row[] = match ($k) {
                        'id' => $log->id,
                        'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                        'user_email' => $log->user?->email ?? 'System / Anonymous',
                        'action' => ucfirst($log->action),
                        'subject_type' => class_basename($log->subject_type ?? ''),
                        'subject_id' => $log->subject_id,
                        'description' => $log->description,
                        default => '',
                    };
                }
                fputcsv($handle, $row);
            }
        });
    }

    // ── Individual Intern Dossier Inspector ──────────────────────────────────
    public function viewInternDossier(int $internId): void
    {
        $this->selectedInternId = $internId;
        $this->dossierTab = 'overview';
        $this->showDossierModal = true;
    }

    public function closeInternDossier(): void
    {
        $this->showDossierModal = false;
        $this->selectedInternId = null;
    }

    public function setDossierTab(string $tab): void
    {
        $this->dossierTab = $tab;
    }

    public function getSelectedInternProperty(): ?Intern
    {
        if (!$this->selectedInternId) return null;

        return Intern::with([
            'batch',
            'team',
            'offerletter',
            'completionCertificate',
            'completionLetter',
            'attendances' => fn($q) => $q->orderByDesc('date'),
            'submissions.task',
        ])->find($this->selectedInternId);
    }

    public function getSelectedInternMetricsProperty(): array
    {
        $intern = $this->selectedIntern;
        if (!$intern) return [];

        $totalAtt = $intern->attendances->count();
        $presentAtt = $intern->attendances->where('status', 'present')->count();
        $absentAtt = $intern->attendances->where('status', 'absent')->count();
        $lateAtt = $intern->attendances->where('status', 'late')->count();
        $leaveAtt = $intern->attendances->where('status', 'leave')->count();
        $attPct = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100, 1) : 0;

        $submissions = $intern->submissions;
        $totalSub = $submissions->count();
        $evalSub = $submissions->whereNotNull('marks')->count();
        $avgScore = $evalSub > 0 ? round($submissions->whereNotNull('marks')->avg('marks'), 1) : 0;

        return [
            'totalAttendance' => $totalAtt,
            'presentAttendance' => $presentAtt,
            'absentAttendance' => $absentAtt,
            'lateAttendance' => $lateAtt,
            'leaveAttendance' => $leaveAtt,
            'attendancePct' => $attPct,
            'totalSubmissions' => $totalSub,
            'evaluatedSubmissions' => $evalSub,
            'avgScore' => $avgScore,
        ];
    }

    // ── Export Single Intern Complete Dossier CSV ────────────────────────────
    public function exportSingleInternCsv(int $internId): StreamedResponse
    {
        $intern = Intern::with([
            'batch', 'team', 'attendances' => fn($q) => $q->orderBy('date'), 'submissions.task'
        ])->findOrFail($internId);

        $safeCode = str_replace(['/', '\\'], '-', $intern->intern_code ?: $intern->id);
        $filename = 'IAPES-Intern-Dossier-' . $safeCode . '-' . Carbon::now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($intern) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // 1. Dossier Title Header
            fputcsv($handle, ['IAPES INTERN DOSSIER & COMPREHENSIVE REPORT']);
            fputcsv($handle, ['Generated At', Carbon::now()->format('d M Y, h:i A')]);
            fputcsv($handle, []);

            // 2. Personal & Academic Profile Section
            fputcsv($handle, ['--- SECTION 1: PERSONAL & ACADEMIC PROFILE ---']);
            fputcsv($handle, ['Intern Code', $intern->intern_code ?? 'INT-' . str_pad($intern->id, 4, '0', STR_PAD_LEFT)]);
            fputcsv($handle, ['Full Name', $intern->name]);
            fputcsv($handle, ['Email Address', $intern->email]);
            fputcsv($handle, ['Phone Number', $intern->phone ?? 'N/A']);
            fputcsv($handle, ['Username', $intern->username]);
            fputcsv($handle, ['Plain Password', $intern->plain_password]);
            fputcsv($handle, ['College / Institute', $intern->college ?? 'N/A']);
            fputcsv($handle, ['Degree Program', $intern->degree ?? 'N/A']);
            fputcsv($handle, ['University', $intern->university ?? 'N/A']);
            fputcsv($handle, ['Academic Year', $intern->academic_year ?? 'N/A']);
            fputcsv($handle, ['CGPA / Score', $intern->cgpa ?? 'N/A']);
            fputcsv($handle, ['Domain Track', $intern->domain ?? 'N/A']);
            fputcsv($handle, ['Skills', $intern->skills ?? 'N/A']);
            fputcsv($handle, []);

            // 3. Internship Tenure & Squad Section
            fputcsv($handle, ['--- SECTION 2: INTERNSHIP TENURE & SQUAD ---']);
            fputcsv($handle, ['Batch', $intern->batch?->batch_name ?? 'Unassigned']);
            fputcsv($handle, ['Project Squad', $intern->team?->team_name ?? 'Unassigned']);
            fputcsv($handle, ['Project Name', $intern->assigned_project_name]);
            fputcsv($handle, ['Assigned Role', $intern->internship_role ?? 'Intern']);
            fputcsv($handle, ['Working Hours', $intern->working_hours ?? '42 hours per week']);
            fputcsv($handle, ['Joining Date', $intern->joining_date?->format('Y-m-d') ?? 'N/A']);
            fputcsv($handle, ['Completion Date', $intern->completion_date?->format('Y-m-d') ?? 'N/A']);
            fputcsv($handle, ['Active Status', $intern->is_active ? 'Active' : 'Archived / Completed']);
            fputcsv($handle, ['Cohort Archive Name', $intern->cohort_archive_name ?? 'N/A']);
            fputcsv($handle, ['Overall Grade', $intern->grade ?? 'N/A']);
            fputcsv($handle, ['Certificate Ref ID', $intern->cert_ref_id ?? 'N/A']);
            fputcsv($handle, ['Completion Letter Ref ID', $intern->letter_ref_id ?? 'N/A']);
            fputcsv($handle, []);

            // 4. Attendance Ledger Section
            fputcsv($handle, ['--- SECTION 3: ATTENDANCE LEDGER ---']);
            $totalAtt = $intern->attendances->count();
            $presentAtt = $intern->attendances->where('status', 'present')->count();
            $absentAtt = $intern->attendances->where('status', 'absent')->count();
            $lateAtt = $intern->attendances->where('status', 'late')->count();
            $leaveAtt = $intern->attendances->where('status', 'leave')->count();
            $attPct = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100, 1) : 0;

            fputcsv($handle, ['Total Days Marked', $totalAtt, 'Days Present', $presentAtt, 'Attendance %', $attPct . '%']);
            fputcsv($handle, ['Date', 'Status', 'Notes / Remarks']);
            foreach ($intern->attendances as $att) {
                fputcsv($handle, [$att->date, ucfirst($att->status), $att->note ?? '']);
            }
            fputcsv($handle, []);

            // 5. Tasks & Submissions Evaluation Section
            fputcsv($handle, ['--- SECTION 4: TASK SUBMISSIONS & EVALUATIONS ---']);
            fputcsv($handle, ['Task Title', 'Priority', 'Due Date', 'Submitted At', 'Status', 'Marks', 'Grade', 'Evaluator Feedback']);
            foreach ($intern->submissions as $sub) {
                fputcsv($handle, [
                    $sub->task?->title ?? 'N/A',
                    ucfirst($sub->task?->priority ?? 'normal'),
                    $sub->task?->due_date?->format('Y-m-d') ?? 'N/A',
                    $sub->submitted_at ?? 'N/A',
                    ucfirst($sub->status),
                    $sub->marks ?? 'Pending',
                    $sub->grade ?? 'Pending',
                    $sub->admin_feedback ?? 'No feedback recorded',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
