<?php

namespace App\Filament\Resources\InternManagement\InternshipBatchResource\Widgets;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InternshipBatchStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBatches = InternshipBatch::count();
        $activeBatches = InternshipBatch::where('is_archived', false)->count();
        $archivedBatches = InternshipBatch::where('is_archived', true)->count();

        $enrolledInterns = Intern::whereNotNull('internship_batch_id')->where('is_active', true)->count();

        $totalCapacity = $activeBatches * 50;
        $availableSeats = max(0, $totalCapacity - $enrolledInterns);
        $capacityDisplay = $totalCapacity > 0 ? "{$availableSeats} / {$totalCapacity}" : "300 seats";

        return [
            Stat::make('TOTAL BATCHES', $totalBatches)
                ->description('All track schedules')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('ACTIVE COHORTS', $activeBatches)
                ->description("{$archivedBatches} Archived")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('TOTAL ENROLLED', $enrolledInterns)
                ->description('Active batch members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('AVAILABLE CAPACITY', $capacityDisplay)
                ->description('Available / total seats')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('warning'),
        ];
    }
}
