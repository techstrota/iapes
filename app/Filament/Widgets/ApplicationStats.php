<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\InterviewAssignment;
use App\Models\InternManagement\Intern;
use Carbon\Carbon;

class ApplicationStats extends BaseWidget
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
        $applicationsLast7Days = collect(range(6, 0))->map(function ($day) {
            return Application::whereDate(
            'created_at',
            Carbon::today()->subDays($day)
            )
            ->count();
        });

        return [
            Stat::make('Total Applications', Application::whereNotIn('status', ['pending', 'verified'])->count())
            ->description('Total applications received')
            ->descriptionIcon('heroicon-m-document-text')
            ->color('primary')
            ->chart($applicationsLast7Days->toArray()),


            Stat::make(
            'Interview Assigned',
            InterviewAssignment::count()
            )
            ->description('Candidates scheduled for interview')
            ->descriptionIcon('heroicon-m-calendar-days')
            ->color('info')
            ->chart($applicationsLast7Days->toArray()),


            Stat::make(
            'Appeared for Interview',
            InterviewAssignment::where('attendance', 'present')->count()
        )
            ->description('Candidates attended interview')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('success')
            ->chart($applicationsLast7Days->toArray()),


            Stat::make(
            'Selected Candidates',
            InterviewAssignment::where('result', 'selected')->count()
        )
            ->description('Candidates successfully selected')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success')
            ->chart($applicationsLast7Days->toArray()),


            Stat::make(
            'Interview Absent',
            InterviewAssignment::where('attendance', 'absent')->count()
        )
            ->description('Candidates absent for interview')
            ->descriptionIcon('heroicon-m-x-circle')
            ->color('danger')
            ->chart($applicationsLast7Days->toArray()),
        
        Stat::make(
            'Active Interns',
            Intern::where('is_active', true)->count()
            )
            ->description('Total Active Interns')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('info')
            ->chart($applicationsLast7Days->toArray()),


        Stat::make(
            'Total Interns',
            Intern::count()
            )
            ->description('Total Interns')
            ->descriptionIcon('heroicon-m-user-group')
            ->color('success')
            ->chart($applicationsLast7Days->toArray()),
        ];
        
    }
}
