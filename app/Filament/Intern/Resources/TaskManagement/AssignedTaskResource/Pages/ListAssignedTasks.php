<?php

namespace App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource\Pages;

use App\Filament\Intern\Resources\TaskManagement\AssignedTaskResource;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListAssignedTasks extends ListRecords
{
    protected static string $resource = AssignedTaskResource::class;

    protected static string $view = 'filament.intern.task-management.list-assigned-tasks';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTaskStats(): array
    {
        $userId = Auth::id();

        $scopedAssignments = TaskAssignment::query()
            ->where(function (Builder $q) use ($userId) {
                $q->where('intern_id', $userId)
                  ->orWhereHas('team.interns', fn ($q2) => $q2->where('interns.id', $userId))
                  ->orWhereExists(function ($q3) use ($userId) {
                      $q3->selectRaw(1)->from('interns')
                         ->whereColumn('interns.internship_batch_id', 'task_assignments.batch_id')
                         ->where('interns.id', $userId);
                  });
            })
            ->with(['task', 'task_submission'])
            ->get();

        $totalTasks = $scopedAssignments->count();
        $submissions = TaskSubmission::where('intern_id', $userId)->get()->keyBy('task_id');

        $pendingCount = 0;
        $inReviewCount = 0;
        $approvedCount = 0;
        $overdueCount = 0;
        $totalMarks = 0;
        $gradedCount = 0;

        foreach ($scopedAssignments as $assignment) {
            $task = $assignment->task;
            $sub = $submissions->get($assignment->task_id);
            $status = $sub?->status;

            if (!$sub || $status === 'rejected') {
                $pendingCount++;
                if ($task?->due_date && \Carbon\Carbon::parse($task->due_date)->isPast()) {
                    $overdueCount++;
                }
            } elseif (in_array($status, ['submitted', 'reviewed'])) {
                $inReviewCount++;
            } elseif ($status === 'approved') {
                $approvedCount++;
                if ($sub->marks !== null) {
                    $totalMarks += $sub->marks;
                    $gradedCount++;
                }
            }
        }

        $avgScore = $gradedCount > 0 ? round($totalMarks / $gradedCount) : 0;
        $completionRate = $totalTasks > 0 ? round(($approvedCount / $totalTasks) * 100) : 0;

        return [
            'total_tasks'     => $totalTasks,
            'pending_action'  => $pendingCount,
            'in_review'       => $inReviewCount,
            'approved_count'  => $approvedCount,
            'overdue_count'   => $overdueCount,
            'avg_score'       => $avgScore,
            'completion_rate' => $completionRate,
        ];
    }

    // ── Status Tabs ────────────────────────────────────────────────
    public function getTabs(): array
    {
        $userId = Auth::id();

        $scopedTaskIds = TaskAssignment::query()
            ->where(function (Builder $q) use ($userId) {
                $q->where('intern_id', $userId)
                  ->orWhereHas('team.interns', fn ($q2) => $q2->where('interns.id', $userId))
                  ->orWhereExists(function ($q3) use ($userId) {
                      $q3->selectRaw(1)->from('interns')
                         ->whereColumn('interns.internship_batch_id', 'task_assignments.batch_id')
                         ->where('interns.id', $userId);
                  });
            })
            ->pluck('task_id')
            ->unique();

        $submittedIds  = TaskSubmission::where('intern_id', $userId)->pluck('task_id');
        $approvedIds   = TaskSubmission::where('intern_id', $userId)->where('status', 'approved')->pluck('task_id');
        $rejectedIds   = TaskSubmission::where('intern_id', $userId)->where('status', 'rejected')->pluck('task_id');
        $reviewedIds   = TaskSubmission::where('intern_id', $userId)->whereIn('status', ['submitted', 'reviewed'])->pluck('task_id');
        $pendingIds    = $scopedTaskIds->diff($submittedIds);

        return [
            'pending' => Tab::make('Pending')
                ->icon('heroicon-o-clock')
                ->badge($pendingIds->count())
                ->badgeColor($pendingIds->count() > 0 ? 'warning' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $pendingIds)),

            'submitted' => Tab::make('In Review')
                ->icon('heroicon-o-paper-airplane')
                ->badge($reviewedIds->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $reviewedIds)),

            'approved' => Tab::make('Approved')
                ->icon('heroicon-o-check-circle')
                ->badge($approvedIds->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $approvedIds)),

            'rejected' => Tab::make('Revision Needed')
                ->icon('heroicon-o-exclamation-triangle')
                ->badge($rejectedIds->count())
                ->badgeColor($rejectedIds->count() > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('task_id', $rejectedIds)),

            'all' => Tab::make('All Tasks')
                ->icon('heroicon-o-squares-2x2')
                ->badge($scopedTaskIds->count()),
        ];
    }
}
