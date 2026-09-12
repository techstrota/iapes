<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Widgets;

use App\Models\InterviewManagement\OfferLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OfferLetterStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $total    = OfferLetter::count();
        $drafts   = OfferLetter::where('offer_status', 'draft')->count();
        $accepted = OfferLetter::where('offer_status', 'accepted')->count();
        $rejected = OfferLetter::where('offer_status', 'rejected')->count();

        return [
            Stat::make('Total Offers', $total)
                ->icon('heroicon-o-document-check')
                ->color('gray'),

            Stat::make('Pending Drafts', $drafts)
                ->description('Auto-generated or awaiting review')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Accepted Offers', $accepted)
                ->description('Intern accounts created')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Rejected Offers', $rejected)
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
