<?php

namespace App\Filament\Intern\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AttendanceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $internId = Auth::id();

        $query = Attendance::where('intern_id', $internId);

        $totalDays = (clone $query)->count();
        $presentDays = (clone $query)->where('status', 'present')->count();
        $lateDays = (clone $query)->where('status', 'late')->count();
        $leaveDays = (clone $query)->where('status', 'leave')->count();
        $absentDays = (clone $query)->where('status', 'absent')->count();

        // Effective presence counts present + late as attended
        $attendedDays = $presentDays + $lateDays;
        $attendanceRate = $totalDays > 0 ? round(($attendedDays / $totalDays) * 100, 1) : 100;

        return [
            Stat::make('Attendance Rate', $attendanceRate . '%')
                ->description($attendanceRate >= 85 ? 'Excellent attendance!' : ($attendanceRate >= 75 ? 'Meets requirement' : 'Low attendance rate'))
                ->descriptionIcon($attendanceRate >= 85 ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-circle')
                ->icon('heroicon-m-chart-pie')
                ->color($attendanceRate >= 85 ? 'success' : ($attendanceRate >= 75 ? 'warning' : 'danger'))
                ->chart([80, 85, 90, 88, (int)$attendanceRate]),

            Stat::make('Present Days', $presentDays . ' / ' . $totalDays)
                ->description('Full days present on duty')
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Late Arrivals', $lateDays)
                ->description($lateDays > 0 ? 'Punctuality check' : 'Always on time! ⚡')
                ->descriptionIcon($lateDays > 0 ? 'heroicon-m-clock' : 'heroicon-m-sparkles')
                ->icon('heroicon-m-clock')
                ->color($lateDays > 2 ? 'warning' : 'gray'),

            Stat::make('Leaves & Absences', ($leaveDays + $absentDays))
                ->description($leaveDays . ' approved leave(s) · ' . $absentDays . ' absent')
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->icon('heroicon-m-exclamation-triangle')
                ->color($absentDays > 0 ? 'danger' : ($leaveDays > 0 ? 'info' : 'gray')),
        ];
    }
}
