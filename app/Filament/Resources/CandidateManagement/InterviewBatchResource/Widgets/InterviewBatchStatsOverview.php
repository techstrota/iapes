<?php

namespace App\Filament\Resources\CandidateManagement\InterviewBatchResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\InterviewManagement\InterviewBatch;
use App\Models\InterviewManagement\InterviewAssignment;
use Carbon\Carbon;

class InterviewBatchStatsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $totalBatches = InterviewBatch::count();
        $openBatches = InterviewBatch::where('capacity_status', 'open')->where('workflow_status', 'scheduled')->count();
        $todayUpcoming = InterviewBatch::whereDate('interview_date', '>=', $today)->where('workflow_status', '!=', 'cancelled')->count();
        
        $totalCapacity = (int) InterviewBatch::sum('batch_size');
        $totalAssigned = (int) InterviewAssignment::count();
        $remainingSlots = max(0, $totalCapacity - $totalAssigned);

        // 7-day sparkline of created batches
        $sparkline = collect(range(6, 0))->map(function ($day) {
            return InterviewBatch::whereDate('created_at', Carbon::today()->subDays($day))->count();
        })->toArray();

        return [
            Stat::make('Total Batches', $totalBatches)
                ->description('All created interview batches')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->chart($sparkline),

            Stat::make('Open Batches', $openBatches)
                ->description("{$remainingSlots} total slots remaining")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Today & Upcoming', $todayUpcoming)
                ->description('Active scheduled sessions')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Candidates Scheduled', $totalAssigned)
                ->description("Across {$totalCapacity} total capacity")
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
