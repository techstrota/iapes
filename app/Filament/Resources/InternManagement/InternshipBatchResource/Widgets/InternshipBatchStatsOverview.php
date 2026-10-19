<?php

namespace App\Filament\Resources\InternManagement\InternshipBatchResource\Widgets;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InterviewManagement\OfferLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class InternshipBatchStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeBatchesList = InternshipBatch::where('is_archived', false)->get();
        $totalBatches = InternshipBatch::count();
        $activeBatches = $activeBatchesList->count();
        $archivedBatches = InternshipBatch::where('is_archived', true)->count();

        $activeBatchIds = $activeBatchesList->pluck('id');
        $enrolledInterns = Intern::whereIn('internship_batch_id', $activeBatchIds)->where('is_active', true)->count();

        // Total interns whose offer letters were accepted
        $totalAcceptedOffers = OfferLetter::where('offer_status', 'accepted')
            ->orWhere('is_accepted', true)
            ->count();

        $totalCapacity = (int) $activeBatchesList->sum(fn ($b) => $b->capacity ?: 50);
        $availableSeats = max(0, $totalCapacity - $enrolledInterns);
        $occupancyPct = $totalCapacity > 0 ? round(($enrolledInterns / $totalCapacity) * 100) : 0;
        $availablePct = 100 - $occupancyPct;

        $capacityDisplay = "{$availableSeats} Seats Left";

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
                ->description(new HtmlString("Active batch members<br><span style='font-size: 11px; opacity: 0.85;'>Total <strong>{$totalAcceptedOffers}</strong> interns enrolled from accepted offers</span>"))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('AVAILABLE CAPACITY', $capacityDisplay)
                ->description("{$enrolledInterns} / {$totalCapacity} booked • {$availablePct}% available")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($availableSeats > 0 ? 'success' : 'danger'),
        ];
    }
}
