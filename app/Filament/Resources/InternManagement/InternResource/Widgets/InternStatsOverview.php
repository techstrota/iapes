<?php

namespace App\Filament\Resources\InternManagement\InternResource\Widgets;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InternStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalInterns = Intern::count();

        $activeInterns = Intern::where('is_active', true)
            ->where(function ($query) {
                $query->whereDoesntHave('offerletter')
                    ->orWhereHas('offerletter', fn ($q) => $q->whereNull('completion_date')->orWhere('completion_date', '>=', now()->toDateString()));
            })
            ->count();

        $presentCount = \App\Models\Attendance::whereDate('date', now()->toDateString())
            ->where('status', 'present')
            ->count();
        $presentDisplay = $presentCount > 0 ? "{$presentCount} / {$totalInterns}" : "{$activeInterns} / {$totalInterns}";

        $pendingTasks = \App\Models\TaskManagement\TaskSubmission::where(function ($q) {
            $q->whereNull('grade')->orWhere('status', 'submitted');
        })->count();

        $activeBatches = InternshipBatch::whereHas('interns', fn ($q) => $q->where('is_active', true))->count();
        if ($activeBatches === 0) {
            $activeBatches = InternshipBatch::count();
        }

        return [
            Stat::make('TOTAL INTERNS', $totalInterns)
                ->description('All registered candidates')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),

            Stat::make('PRESENT TODAY', $presentDisplay)
                ->description('Active / present today')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('PENDING TASKS', $pendingTasks)
                ->description('Awaiting review & grading')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('ACTIVE BATCHES', $activeBatches)
                ->description('Running cohort schedules')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color('warning'),
        ];
    }
}
