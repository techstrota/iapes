<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Pages;

use App\Filament\Resources\TaskManagement\TaskResource;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\InternManagement\InternshipBatch;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected static string $view = 'filament.task-management.list-tasks';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getTaskStats(): array
    {
        try {
            $activeTasksCount = Task::where('status', 'active')->count();
            $activeBatchesCount = InternshipBatch::count();
            $pendingEvalCount = TaskSubmission::where(function ($q) {
                $q->whereNull('grade')->orWhere('status', 'submitted');
            })->count();
            $evaluatedCount = TaskSubmission::whereNotNull('grade')->where('status', '!=', 'submitted')->count();
            $totalSubmissions = TaskSubmission::count();
            $overdueCount = Task::where('status', 'active')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count();
            $gradingVelocity = $totalSubmissions > 0
                ? round(($evaluatedCount / $totalSubmissions) * 100)
                : 100;
            $totalTasks = Task::count();

            return [
                'active_tasks'      => $activeTasksCount,
                'active_batches'    => $activeBatchesCount,
                'pending_eval'      => $pendingEvalCount,
                'evaluated_count'   => $evaluatedCount,
                'total_submissions' => $totalSubmissions,
                'overdue_count'     => $overdueCount,
                'grading_velocity'  => $gradingVelocity,
                'total_tasks'       => $totalTasks,
            ];
        } catch (\Throwable $e) {
            return [
                'active_tasks'      => 0,
                'active_batches'    => 0,
                'pending_eval'      => 0,
                'evaluated_count'   => 0,
                'total_submissions' => 0,
                'overdue_count'     => 0,
                'grading_velocity'  => 100,
                'total_tasks'       => 0,
            ];
        }
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'active';
    }

    public function getTabs(): array
    {
        $activeCount = Task::query()
            ->where('status', 'active')
            ->count();

        $needsEvalCount = Task::query()
            ->whereHas('submissions', fn ($q) => $q->whereNull('evaluated_at'))
            ->count();

        $overdueCount = Task::query()
            ->where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $completedCount = Task::query()
            ->where('status', 'completed')
            ->count();

        $totalCount = Task::count();

        return [
            'active' => Tab::make('Active Tasks')
                ->icon('heroicon-m-bolt')
                ->badge($activeCount)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),

            'needs_eval' => Tab::make('Needs Evaluation')
                ->icon('heroicon-m-clipboard-document-check')
                ->badge($needsEvalCount)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('submissions', fn ($q) => $q->whereNull('evaluated_at'))),

            'overdue' => Tab::make('Overdue Tasks')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge($overdueCount)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')->whereNotNull('due_date')->where('due_date', '<', now())),

            'completed' => Tab::make('Completed & Graded')
                ->icon('heroicon-m-check-badge')
                ->badge($completedCount)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),

            'all' => Tab::make('All Tasks')
                ->icon('heroicon-m-squares-2x2')
                ->badge($totalCount),
        ];
    }
}
