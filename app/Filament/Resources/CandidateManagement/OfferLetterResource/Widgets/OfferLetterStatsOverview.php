<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Widgets;

use App\Models\InterviewManagement\OfferLetter;
use App\Models\InterviewManagement\OfferLetterTemplate;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OfferLetterStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected static ?int $sort = 1;

    protected static string $view = 'filament.widgets.offer-letter-stats-overview';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $total     = OfferLetter::count();
        $drafts    = OfferLetter::where('offer_status', 'draft')->count();
        $accepted  = OfferLetter::where('offer_status', 'accepted')->count();
        $rejected  = OfferLetter::where('offer_status', 'rejected')->count();
        $templates = OfferLetterTemplate::count();

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
                
        ];
    }
}
