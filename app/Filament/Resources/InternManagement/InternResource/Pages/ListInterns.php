<?php

namespace App\Filament\Resources\InternManagement\InternResource\Pages;

use App\Filament\Resources\InternManagement\InternResource;
use App\Models\Attendance;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;

class ListInterns extends Page
{
    use WithPagination;

    protected static string $resource = InternResource::class;

    protected static string $view = 'filament.intern-management.intern-resource.pages.list-interns';

    protected static ?string $title = 'Interns Directory';

    public string $search = '';
    public string $activeTab = 'all'; // 'all', 'batch', 'team'
    public string $viewMode = 'current'; // 'current', 'archived', 'all'
    public ?string $selectedArchiveCycle = null;
    public ?int $selectedBatchId = null;
    public array $selectedInterns = [];
    public int $perPage = 10;

    // Archival / Promotion Modal state
    public bool $showArchiveModal = false;
    public string $archiveCycleName = '';
    public string $archiveNote = '';
    public ?string $archiveCompletionDate = null;

    // Assign Task Pop-up Modal state
    public bool $showAssignTaskModal = false;
    public ?int $selectedTaskId = null;

    // Mark Attendance Pop-up Modal state
    public bool $showAttendanceModal = false;
    public string $selectedAttendanceStatus = 'present'; // 'present', 'wfh', 'leave', 'absent'
    public string $attendanceDate = '';
    public string $attendanceNote = '';

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedViewMode(): void
    {
        $this->resetPage();
        $this->selectedInterns = [];
    }

    public function updatedSelectedArchiveCycle(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBatchId(): void
    {
        $this->resetPage();
    }

    public function toggleSelectIntern(int $id): void
    {
        $id = (int) $id;
        if (in_array($id, $this->selectedInterns)) {
            $this->selectedInterns = array_values(array_filter($this->selectedInterns, fn ($i) => (int) $i !== $id));
        } else {
            $this->selectedInterns[] = $id;
        }
    }

    public function selectAllOnPage(array $pageIds): void
    {
        $pageIds = array_map('intval', $pageIds);
        $diff = array_diff($pageIds, $this->selectedInterns);
        if (empty($diff)) {
            // Already all selected on page, so unselect page
            $this->selectedInterns = array_values(array_diff($this->selectedInterns, $pageIds));
        } else {
            // Select all on page
            $this->selectedInterns = array_values(array_unique(array_merge($this->selectedInterns, $pageIds)));
        }
    }

    public function selectAllActiveInterns(): void
    {
        $activeIds = Intern::where('is_active', true)->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->selectedInterns = $activeIds;

        Notification::make()
            ->title('Selected all ' . count($activeIds) . ' active interns')
            ->info()
            ->send();
    }

    public function openArchiveModal(): void
    {
        if (empty($this->selectedInterns)) {
            // If no interns are manually checked, auto-select all current active interns
            $activeIds = Intern::where('is_active', true)->pluck('id')->map(fn ($id) => (int) $id)->toArray();
            if (empty($activeIds)) {
                Notification::make()
                    ->title('No active interns found to archive')
                    ->warning()
                    ->send();
                return;
            }
            $this->selectedInterns = $activeIds;
        }

        if (empty($this->archiveCycleName)) {
            $this->archiveCycleName = 'Cohort ' . date('Y') . ' - Annual Batch';
        }
        $this->archiveCompletionDate = now()->toDateString();
        $this->showArchiveModal = true;
    }

    public function closeArchiveModal(): void
    {
        $this->showArchiveModal = false;
    }

    public function executeArchival(): void
    {
        $cycleName = trim($this->archiveCycleName);
        if (empty($cycleName)) {
            Notification::make()
                ->title('Please enter a Cycle / Archive Name')
                ->danger()
                ->send();
            return;
        }

        if (empty($this->selectedInterns)) {
            Notification::make()
                ->title('No interns selected to archive')
                ->warning()
                ->send();
            return;
        }

        $interns = Intern::whereIn('id', $this->selectedInterns)->get();
        $count = $interns->count();
        $batchIds = $interns->pluck('internship_batch_id')->filter()->unique();
        $teamIds = $interns->pluck('intern_team_id')->filter()->unique();

        foreach ($interns as $intern) {
            $intern->promoteToCompletion(
                cycleName: $cycleName,
                note: $this->archiveNote ?: null,
                completionDate: $this->archiveCompletionDate ?: now()->toDateString()
            );
        }

        foreach ($batchIds as $bId) {
            \App\Models\InternManagement\InternshipBatch::find($bId)?->checkAndAutoArchive($cycleName);
        }

        foreach ($teamIds as $tId) {
            \App\Models\InternManagement\InternTeam::find($tId)?->checkAndAutoArchive($cycleName, $this->archiveNote ?: null);
        }

        $this->showArchiveModal = false;
        $this->selectedInterns = [];
        $this->archiveNote = '';
        $this->resetPage();

        Notification::make()
            ->title("Promoted and Archived {$count} interns under cycle '{$cycleName}'")
            ->body('Historical records, offer letters, project details, and certificates have been preserved.')
            ->success()
            ->send();
    }

    public function deselectAll(): void
    {
        $this->selectedInterns = [];
    }

    public function openAttendanceModal(): void
    {
        if (empty($this->selectedInterns)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return;
        }

        $this->selectedAttendanceStatus = 'present';
        $this->attendanceDate = now()->toDateString();
        $this->attendanceNote = '';
        $this->showAttendanceModal = true;
    }

    public function closeAttendanceModal(): void
    {
        $this->showAttendanceModal = false;
        $this->attendanceNote = '';
    }

    public function executeMarkAttendance(): void
    {
        if (empty($this->selectedInterns)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return;
        }

        $validStatus = in_array($this->selectedAttendanceStatus, ['present', 'wfh', 'leave', 'absent'])
            ? $this->selectedAttendanceStatus
            : 'present';

        $date = filled($this->attendanceDate) ? $this->attendanceDate : now()->toDateString();
        $note = filled($this->attendanceNote) ? trim($this->attendanceNote) : null;

        $count = 0;
        foreach ($this->selectedInterns as $id) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $id,
                    'date' => $date,
                ],
                [
                    'status' => $validStatus,
                    'note' => $note,
                ]
            );
            $count++;
        }

        $statusLabels = [
            'present' => 'Present',
            'wfh'     => 'Work From Home (WFH)',
            'leave'   => 'Leave',
            'absent'  => 'Absent',
        ];

        $statusLabel = $statusLabels[$validStatus] ?? ucfirst($validStatus);

        Notification::make()
            ->title("Attendance marked as {$statusLabel} for {$count} interns on " . \Carbon\Carbon::parse($date)->format('d M Y'))
            ->success()
            ->send();

        $this->showAttendanceModal = false;
        $this->selectedInterns = [];
        $this->attendanceNote = '';
    }

    public function markAttendanceForSelected(string $status = 'present'): void
    {
        if (empty($this->selectedInterns)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return;
        }

        $this->selectedAttendanceStatus = in_array($status, ['present', 'wfh', 'leave', 'absent']) ? $status : 'present';
        $this->executeMarkAttendance();
    }

    public function markAllActiveAttendance(): void
    {
        $activeInterns = Intern::where('is_active', true)->get();
        foreach ($activeInterns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'status' => 'present',
                ]
            );
        }

        Notification::make()
            ->title('Marked attendance as Present for all active interns today')
            ->success()
            ->send();
    }

    public function openAssignTaskModal(): void
    {
        if (empty($this->selectedInterns)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return;
        }

        $activeTask = Task::where('status', 'active')
            ->orWhereNull('status')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->first();

        $this->selectedTaskId = $activeTask?->task_id;
        $this->showAssignTaskModal = true;
    }

    public function closeAssignTaskModal(): void
    {
        $this->showAssignTaskModal = false;
        $this->selectedTaskId = null;
    }

    public function executeAssignTask(): void
    {
        if (empty($this->selectedInterns)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return;
        }

        if (!$this->selectedTaskId) {
            Notification::make()->title('Please select an active task to assign')->warning()->send();
            return;
        }

        $task = Task::find($this->selectedTaskId);
        if (!$task) {
            Notification::make()->title('Selected task not found')->danger()->send();
            return;
        }

        $assignedCount = 0;
        foreach ($this->selectedInterns as $internId) {
            $assignment = TaskAssignment::firstOrCreate([
                'task_id'       => $task->task_id,
                'assigned_type' => 'intern',
                'intern_id'     => $internId,
            ]);
            if ($assignment->wasRecentlyCreated) {
                $assignedCount++;
            }
        }

        $totalSelected = count($this->selectedInterns);
        $taskTitle = $task->title;

        $this->showAssignTaskModal = false;
        $this->selectedTaskId = null;
        $this->selectedInterns = [];

        Notification::make()
            ->title("Task '{$taskTitle}' successfully assigned! 🚀")
            ->body("Assigned to {$totalSelected} candidates ({$assignedCount} new, " . ($totalSelected - $assignedCount) . " already had access).")
            ->success()
            ->send();
    }

    public function redirectToCreateTaskWithSelected()
    {
        if (empty($this->selectedInterns)) {
            Notification::make()->title('No interns selected')->warning()->send();
            return null;
        }

        $internIds = implode(',', $this->selectedInterns);
        $this->showAssignTaskModal = false;

        return $this->redirect(route('filament.admin.resources.task-management.tasks.create', [
            'intern_ids' => $internIds,
        ]));
    }

    public function assignTaskSelected(): void
    {
        $this->openAssignTaskModal();
    }

    public function exportSelected()
    {
        $ids = !empty($this->selectedInterns)
            ? $this->selectedInterns
            : $this->getInternsQuery()->pluck('id')->toArray();

        $interns = Intern::with(['batch', 'team'])->whereIn('id', $ids)->get();

        $csvFileName = 'interns_export_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($interns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Intern Code',
                'Name',
                'Email',
                'Phone',
                'College',
                'Degree',
                'University',
                'Role',
                'Position',
                'Cohort Batch',
                'Project / Squad',
                'Joining Date',
                'Completion Date',
                'Grade',
                'Status',
            ]);

            foreach ($interns as $intern) {
                fputcsv($file, [
                    $intern->intern_code ?: 'INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT),
                    $intern->name,
                    $intern->email,
                    $intern->phone,
                    $intern->college,
                    $intern->degree,
                    $intern->university,
                    $intern->internship_role,
                    $intern->internship_position,
                    $intern->batch?->batch_name ?? 'Batch not assigned',
                    $intern->project_name ?? $intern->team?->team_name ?? 'Project not assigned',
                    $intern->joining_date ? \Carbon\Carbon::parse($intern->joining_date)->format('Y-m-d') : '',
                    $intern->completion_date ? \Carbon\Carbon::parse($intern->completion_date)->format('Y-m-d') : '',
                    $intern->grade ?? 'Not Graded',
                    $intern->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($file);
        }, $csvFileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function getStats(): array
    {
        $totalInterns = Intern::count();

        $activeInterns = Intern::where('is_active', true)
            ->where(function ($query) {
                $query->whereDoesntHave('offerletter')
                    ->orWhereHas('offerletter', fn ($q) => $q->whereNull('completion_date')->orWhere('completion_date', '>=', now()->toDateString()));
            })
            ->count();

        $presentCount = Attendance::whereDate('date', now()->toDateString())
            ->where('status', 'present')
            ->count();

        $presentDisplay = "{$presentCount} / {$activeInterns}";

        $pendingTasks = TaskSubmission::where(function ($q) {
            $q->whereNull('grade')->orWhere('status', 'submitted');
        })->count();

        $activeBatches = InternshipBatch::whereHas('interns', fn ($q) => $q->where('is_active', true))->count();
        if ($activeBatches === 0) {
            $activeBatches = InternshipBatch::count();
        }

        return [
            'total' => $totalInterns,
            'present_today' => $presentDisplay,
            'pending_tasks' => $pendingTasks,
            'active_batches' => $activeBatches,
        ];
    }

    public function getInternsQuery()
    {
        $query = Intern::with(['offerletter', 'application', 'batch', 'team', 'submissions', 'attendances']);

        // View Mode Filter: Current Active, Archived Cycles, or All
        if ($this->viewMode === 'current') {
            $query->where('is_active', true);
        } elseif ($this->viewMode === 'archived') {
            $query->where('is_active', false);
            if (filled($this->selectedArchiveCycle)) {
                $query->where('cohort_archive_name', $this->selectedArchiveCycle);
            }
        }

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('intern_code', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('college', 'like', $term)
                    ->orWhere('degree', 'like', $term)
                    ->orWhere('internship_role', 'like', $term)
                    ->orWhere('project_name', 'like', $term)
                    ->orWhere('cohort_archive_name', 'like', $term)
                    ->orWhereHas('batch', fn ($sub) => $sub->where('batch_name', 'like', $term))
                    ->orWhereHas('team', fn ($sub) => $sub->where('team_name', 'like', $term));
            });
        }

        if ($this->selectedBatchId) {
            $query->where('internship_batch_id', $this->selectedBatchId);
        }

        if ($this->activeTab === 'batch') {
            $query->orderBy('internship_batch_id');
        } elseif ($this->activeTab === 'team') {
            $query->orderBy('intern_team_id');
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    public function getViewData(): array
    {
        $perPageValue = $this->perPage > 0 ? $this->perPage : 9999;
        $interns = $this->getInternsQuery()->paginate($perPageValue);

        $selectedNames = '';
        if (!empty($this->selectedInterns)) {
            $selectedNames = Intern::whereIn('id', $this->selectedInterns)
                ->get()
                ->map(fn ($i) => $i->name ?: $i->intern_code)
                ->implode(', ');
        }

        $activeCount = Intern::where('is_active', true)->count();
        $archivedCount = Intern::where('is_active', false)->count();
        $totalCount = Intern::count();

        $archiveCycles = Intern::whereNotNull('cohort_archive_name')
            ->where('cohort_archive_name', '!=', '')
            ->distinct()
            ->pluck('cohort_archive_name')
            ->toArray();

        $groupedBatches = null;
        if ($this->activeTab === 'batch') {
            $groupedBatches = $interns->getCollection()
                ->groupBy(fn ($i) => $i->internship_batch_id ?? 0)
                ->map(function ($items, $batchId) {
                    $batch = $batchId > 0 ? InternshipBatch::find($batchId) : null;
                    return [
                        'batch' => $batch,
                        'batch_id' => $batchId,
                        'name' => $batch ? $batch->batch_name : 'Batch Not Assigned',
                        'timing' => $batch ? $batch->batch_timing : null,
                        'interns' => $items,
                        'count' => $items->count(),
                        'is_unassigned' => $batchId == 0,
                    ];
                })
                ->sortBy(fn ($g) => $g['is_unassigned'] ? 1 : 0)
                ->values();
        }

        $groupedTeams = null;
        if ($this->activeTab === 'team') {
            $groupedTeams = $interns->getCollection()
                ->groupBy(fn ($i) => $i->intern_team_id ?? 0)
                ->map(function ($items, $teamId) {
                    $team = $teamId > 0 ? InternTeam::find($teamId) : null;
                    return [
                        'team' => $team,
                        'team_id' => $teamId,
                        'name' => $team ? $team->team_name : 'Project Not Assigned',
                        'track' => $team?->track ?? 'Unassigned Track',
                        'mentor_name' => $team?->mentor_name,
                        'mentor_title' => $team?->mentor_title,
                        'status' => $team?->status ?? 'on_track',
                        'squad_badge' => $team?->squad_badge ?? ($items->count() . ' Interns'),
                        'interns' => $items,
                        'count' => $items->count(),
                        'is_unassigned' => $teamId == 0,
                    ];
                })
                ->sortBy(fn ($g) => $g['is_unassigned'] ? 1 : 0)
                ->values();
        }

        $activeTasks = Task::where('status', 'active')
            ->orWhereNull('status')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeAssignments = TaskAssignment::whereIn('task_id', $activeTasks->pluck('task_id'))->get();

        $selectedInternModels = !empty($this->selectedInterns)
            ? Intern::whereIn('id', $this->selectedInterns)->get()
            : collect();

        $selectedTaskModel = $this->selectedTaskId
            ? Task::find($this->selectedTaskId)
            : null;

        return [
            'stats' => $this->getStats(),
            'batches' => InternshipBatch::all(),
            'interns' => $interns,
            'groupedBatches' => $groupedBatches,
            'groupedTeams' => $groupedTeams,
            'currentPageIds' => $interns->pluck('id')->map(fn ($id) => (int) $id)->toArray(),
            'totalInternsCount' => $totalCount,
            'activeCount' => $activeCount,
            'archivedCount' => $archivedCount,
            'archiveCycles' => $archiveCycles,
            'selectedNames' => $selectedNames,
            'viewMode' => $this->viewMode,
            'selectedArchiveCycle' => $this->selectedArchiveCycle,
            'activeTasks' => $activeTasks,
            'activeAssignments' => $activeAssignments,
            'selectedInternModels' => $selectedInternModels,
            'selectedTaskModel' => $selectedTaskModel,
        ];
    }
}

