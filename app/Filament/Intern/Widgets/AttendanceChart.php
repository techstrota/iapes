<?php

namespace App\Filament\Intern\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceChart extends ChartWidget
{
    protected static ?string $heading = '30-Day Attendance Trend';
    protected static ?string $description = 'Daily presence and punctuality log';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $internId = Auth::id();

        $attendanceData = Attendance::where('intern_id', $internId)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date', 'asc')
            ->get();

        $labels = $attendanceData->map(fn ($r) => Carbon::parse($r->date)->format('M d'));
        $data   = $attendanceData->map(fn ($r) => match ($r->status) {
            'present' => 1.0,
            'late'    => 0.7,
            'leave'   => 0.4,
            'absent'  => 0.1,
            default   => 0,
        });

        return [
            'datasets' => [
                [
                    'label'                => 'Status',
                    'data'                 => $data,
                    'fill'                 => 'start',
                    'borderColor'          => 'rgb(59, 130, 246)',
                    'backgroundColor'      => 'rgba(59, 130, 246, 0.12)',
                    'tension'              => 0.4,
                    'pointRadius'          => 4,
                    'pointHoverRadius'     => 6,
                    'pointBackgroundColor' => 'rgb(96, 165, 250)',
                    'pointBorderColor'     => '#0b1326',
                    'pointBorderWidth'     => 2,
                    'borderWidth'          => 2.5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend'  => ['display' => false],
                'tooltip' => [
                    'backgroundColor' => '#131b2e',
                    'borderColor'     => '#222a3d',
                    'borderWidth'     => 1,
                    'titleColor'      => '#ffffff',
                    'bodyColor'       => '#93c5fd',
                    'padding'         => 12,
                    'callbacks'       => [
                        'label' => "function(context) {
                            var val = context.parsed.y;
                            if (val >= 1.0) return 'Present (Full Day)';
                            if (val >= 0.7) return 'Late Arrival';
                            if (val >= 0.4) return 'Approved Leave';
                            return 'Absent';
                        }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid'  => ['display' => false, 'drawBorder' => false],
                    'ticks' => [
                        'color'         => '#8e909f',
                        'font'          => ['size' => 11],
                        'maxTicksLimit' => 6,
                    ],
                    'border' => ['display' => false],
                ],
                'y' => [
                    'min'     => 0,
                    'max'     => 1.2,
                    'display' => false,
                    'grid'    => ['display' => false],
                ],
            ],
        ];
    }
}