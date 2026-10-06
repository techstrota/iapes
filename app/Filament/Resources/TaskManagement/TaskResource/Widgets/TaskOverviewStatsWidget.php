<?php

namespace App\Filament\Resources\TaskManagement\TaskResource\Widgets;

use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\InternManagement\InternshipBatch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskOverviewStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $activeTasksCount = Task::where('status', 'active')->count();
        $activeBatchesCount = InternshipBatch::where('is_archived', false)->count();

        $pendingEvalCount = TaskSubmission::whereNull('evaluated_at')->count();
        $evaluatedCount = TaskSubmission::whereNotNull('evaluated_at')->count();
        $totalSubmissions = TaskSubmission::count();

        $overdueCount = Task::where('status', 'active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $gradingVelocity = $totalSubmissions > 0
            ? round(($evaluatedCount / $totalSubmissions) * 100)
            : 100;

        return [
            Stat::make('Active Tasks', $activeTasksCount)
                ->description("Across {$activeBatchesCount} Active Batches")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Pending Evaluation', "{$pendingEvalCount} Submissions")
                ->description('Needs Mentor Grading & Review')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Overdue Tasks', "{$overdueCount} Deadlines")
                ->description('Passed Cohort SLA Deadline')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Cohort Grading Velocity', "{$gradingVelocity}%")
                ->description("{$evaluatedCount} of {$totalSubmissions} Graded")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
