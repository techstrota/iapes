<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\InterviewManagement\Application;
use Carbon\Carbon;

class CandidateStatsOverview extends BaseWidget
{
    protected static string $view = 'filament.widgets.application-stats';
    protected function getColumns(): int
    {
        return 5;
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    protected function getStats(): array
    {
        // 7-day sparkline data for each status
        $sparkline = fn (string $status = null) => collect(range(6, 0))->map(function ($day) use ($status) {
            $query = Application::whereNotIn('status', ['pending', 'verified'])
                ->whereDate('created_at', Carbon::today()->subDays($day));
            if ($status) {
                $query->where('status', $status);
            }
            return $query->count();
        })->toArray();

        $total = Application::whereNotIn('status', ['pending', 'verified'])->count();
        $applied = Application::where('status', 'applied')->count();
        $scheduled = Application::where('status', 'interview_scheduled')->count();
        $shortlisted = Application::where('status', 'shortlisted')->count();
        $rejected = Application::where('status', 'rejected')->count();

        return [
            Stat::make('Total Candidates', $total)
                ->description('All applications received')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart($sparkline()),

            Stat::make('Applied', $applied)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('warning')
                ->chart($sparkline('applied')),

            Stat::make('Interview Scheduled', $scheduled)
                ->description('Interview upcoming')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->chart($sparkline('interview_scheduled')),

            Stat::make('Shortlisted', $shortlisted)
                ->description('Selected candidates')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($sparkline('shortlisted')),

            Stat::make('Rejected', $rejected)
                ->description('Not selected')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart($sparkline('rejected')),
        ];
    }
}
