<?php

namespace App\Filament\Intern\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class InternStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Correct scoped query — matches AssignedTaskResource::getEloquentQuery()
        $scopedAssignments = TaskAssignment::query()
            ->where(function (Builder $q) use ($userId) {
                $q->where('intern_id', $userId)
                  ->orWhereHas('team.interns', fn ($q2) => $q2->where('interns.id', $userId))
                  ->orWhereExists(function ($q3) use ($userId) {
                      $q3->selectRaw(1)->from('interns')
                         ->whereColumn('interns.internship_batch_id', 'task_assignments.batch_id')
                         ->where('interns.id', $userId);
                  });
            });

        $totalAssigned = (clone $scopedAssignments)->count();
        $submittedIds  = TaskSubmission::where('intern_id', $userId)->pluck('task_id');
        $pendingCount  = (clone $scopedAssignments)->whereNotIn('task_id', $submittedIds)->count();
        $approvedCount = TaskSubmission::where('intern_id', $userId)->where('status', 'approved')->count();
        $rejectedCount = TaskSubmission::where('intern_id', $userId)->where('status', 'rejected')->count();
        $reviewedCount = TaskSubmission::where('intern_id', $userId)->whereIn('status', ['submitted', 'reviewed'])->count();
        $avgScore      = TaskSubmission::where('intern_id', $userId)->whereNotNull('marks')->avg('marks');

        $indexUrl = route('filament.intern.resources.task-management.assigned-tasks.index');

        return [
            Stat::make('Total Tasks', $totalAssigned)
                ->description('Your complete workload')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->icon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->chart([3, 5, 4, 6, $totalAssigned])
                ->url($indexUrl . '?activeTab=all'),

            Stat::make('⏳ Pending', $pendingCount)
                ->description($pendingCount > 0 ? 'Need your action' : 'All caught up! 🎉')
                ->descriptionIcon($pendingCount > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->icon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->chart([2, 4, 3, $pendingCount, $pendingCount])
                ->url($indexUrl . '?activeTab=pending'),

            Stat::make('📬 Under Review', $reviewedCount)
                ->description('Admin is reviewing')
                ->descriptionIcon('heroicon-m-eye')
                ->icon('heroicon-m-paper-airplane')
                ->color('info')
                ->chart([0, 1, 2, $reviewedCount, $reviewedCount])
                ->url($indexUrl . '?activeTab=submitted'),

            Stat::make('✅ Approved', $approvedCount)
                ->description('Quality work!')
                ->descriptionIcon('heroicon-m-check-badge')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->chart([1, 2, 3, 4, $approvedCount])
                ->url($indexUrl . '?activeTab=approved'),

            Stat::make('❌ Rejected', $rejectedCount)
                ->description($rejectedCount > 0 ? 'Resubmission needed' : 'Clean record! 🌟')
                ->descriptionIcon($rejectedCount > 0 ? 'heroicon-m-arrow-path' : 'heroicon-m-star')
                ->icon('heroicon-m-x-circle')
                ->color($rejectedCount > 0 ? 'danger' : 'gray')
                ->chart([0, 0, $rejectedCount, $rejectedCount, $rejectedCount])
                ->url($indexUrl . '?activeTab=rejected'),

            Stat::make('⭐ Avg Score', $avgScore ? round($avgScore, 1) . ' / 100' : '—')
                ->description('Based on evaluated tasks')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->icon('heroicon-m-trophy')
                ->color($avgScore >= 80 ? 'success' : ($avgScore >= 50 ? 'warning' : 'danger')),
        ];
    }
}