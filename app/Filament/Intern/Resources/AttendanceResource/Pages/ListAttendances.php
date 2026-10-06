<?php

namespace App\Filament\Intern\Resources\AttendanceResource\Pages;

use App\Filament\Intern\Resources\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static string $view = 'filament.intern.attendance.list-attendances';

    public string $calendarMonth = '';
    public ?string $selectedDate = null;
    public string $viewMode = 'calendar';

    public function mount(): void
    {
        parent::mount();
        $this->calendarMonth = now()->format('Y-m');
        $this->selectedDate = now()->toDateString();
    }

    public function previousMonth(): void
    {
        $base = !empty($this->calendarMonth) ? $this->calendarMonth : now()->format('Y-m');
        $this->calendarMonth = Carbon::parse($base . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $base = !empty($this->calendarMonth) ? $this->calendarMonth : now()->format('Y-m');
        $this->calendarMonth = Carbon::parse($base . '-01')->addMonth()->format('Y-m');
    }

    public function goToCurrentMonth(): void
    {
        $this->calendarMonth = now()->format('Y-m');
        $this->selectedDate = now()->toDateString();
    }

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
        if (!empty($date) && strlen($date) >= 7) {
            $this->calendarMonth = substr($date, 0, 7);
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getAttendanceStats(): array
    {
        $internId = Auth::id();
        $query = Attendance::where('intern_id', $internId);

        $totalDays   = (clone $query)->count();
        $presentDays = (clone $query)->where('status', 'present')->count();
        $leaveDays   = (clone $query)->where('status', 'leave')->count();
        $absentDays  = (clone $query)->where('status', 'absent')->count();

        $attendedDays = $presentDays;
        $attendanceRate = $totalDays > 0 ? round(($attendedDays / $totalDays) * 100, 1) : 100;

        $todayRecord = (clone $query)->whereDate('date', Carbon::today())->first();
        $todayStatus = $todayRecord?->status;

        return [
            'total_days'      => $totalDays,
            'present_days'    => $presentDays,
            'leave_days'      => $leaveDays,
            'absent_days'     => $absentDays,
            'attended_days'   => $attendedDays,
            'attendance_rate' => $attendanceRate,
            'today_status'    => $todayStatus,
        ];
    }

    public function getCalendarData(): array
    {
        $internId = Auth::id();
        $intern = Auth::user();

        if (empty($this->calendarMonth)) {
            $this->calendarMonth = now()->format('Y-m');
        }

        $monthCarbon = Carbon::parse($this->calendarMonth . '-01');
        $startOfMonth = $monthCarbon->copy()->startOfMonth();
        $endOfMonth = $monthCarbon->copy()->endOfMonth();

        // Sunday-start 7-column grid
        $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $attendances = Attendance::where('intern_id', $internId)
            ->whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
            ->get();

        $attendanceMap = [];
        foreach ($attendances as $att) {
            $rawDate = $att->date;
            $dateKey = $rawDate instanceof \DateTimeInterface ? $rawDate->format('Y-m-d') : (string)$rawDate;
            $attendanceMap[$dateKey] = [
                'id' => $att->id,
                'status' => $att->status,
                'note' => $att->note,
                'created_at' => $att->created_at,
            ];
        }

        $calendarDays = [];
        $cursor = $startOfGrid->copy();
        while ($cursor->lte($endOfGrid)) {
            $dateStr = $cursor->toDateString();
            $isSunday = $cursor->isSunday();
            $attRecord = $attendanceMap[$dateStr] ?? null;

            if ($attRecord) {
                $status = $attRecord['status'];
            } elseif ($isSunday) {
                $status = 'weekend';
            } elseif ($cursor->isPast() && !$cursor->isToday()) {
                $status = 'unmarked';
            } else {
                $status = 'future';
            }

            $calendarDays[] = [
                'date'           => $dateStr,
                'day'            => $cursor->format('d'),
                'isCurrentMonth' => $cursor->month === $monthCarbon->month,
                'isToday'        => $cursor->isToday(),
                'isSelected'     => $dateStr === $this->selectedDate,
                'isWeekend'      => $isSunday,
                'status'         => $status,
                'note'           => $attRecord['note'] ?? null,
                'rawRecord'      => $attRecord,
            ];

            $cursor->addDay();
        }

        // Monthly stats for current viewing month
        $monthQuery = Attendance::where('intern_id', $internId)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);

        $monthPresent = (clone $monthQuery)->where('status', 'present')->count();
        $monthLeave   = (clone $monthQuery)->where('status', 'leave')->count();
        $monthAbsent  = (clone $monthQuery)->where('status', 'absent')->count();

        $monthAttended = $monthPresent;
        $monthTotalMarked = $monthAttended + $monthLeave + $monthAbsent;
        $monthCompliance = $monthTotalMarked > 0 ? round(($monthAttended / $monthTotalMarked) * 100, 1) : 100.0;

        // Selected day detail
        $selectedDayInfo = null;
        if ($this->selectedDate) {
            $selCarbon = Carbon::parse($this->selectedDate);
            $selAtt = $attendanceMap[$this->selectedDate] ?? null;
            $selectedDayInfo = [
                'date'      => $this->selectedDate,
                'formatted' => $selCarbon->format('l, d F Y'),
                'isToday'   => $selCarbon->isToday(),
                'isWeekend' => $selCarbon->isSunday(),
                'status'    => $selAtt['status'] ?? ($selCarbon->isSunday() ? 'weekend' : ($selCarbon->isPast() && !$selCarbon->isToday() ? 'unmarked' : 'future')),
                'note'      => $selAtt['note'] ?? null,
            ];
        }

        return [
            'calendarDays'     => $calendarDays,
            'monthTitle'       => $monthCarbon->format('F Y'),
            'monthPresent'     => $monthPresent,
            'monthLeave'       => $monthLeave,
            'monthAbsent'      => $monthAbsent,
            'monthAttended'    => $monthAttended,
            'monthTotalMarked' => $monthTotalMarked,
            'monthCompliance'  => $monthCompliance,
            'selectedDayInfo'  => $selectedDayInfo,
            'batchTiming'      => $intern?->batch?->batch_timing,
        ];
    }

    public function getTabs(): array
    {
        $internId = Auth::id();
        $baseQuery = Attendance::where('intern_id', $internId);

        $allCount     = (clone $baseQuery)->count();
        $presentCount = (clone $baseQuery)->where('status', 'present')->count();
        $leaveCount   = (clone $baseQuery)->where('status', 'leave')->count();
        $absentCount  = (clone $baseQuery)->where('status', 'absent')->count();

        return [
            'all' => Tab::make('All Logs')
                ->icon('heroicon-o-squares-2x2')
                ->badge($allCount),

            'present' => Tab::make('Present')
                ->icon('heroicon-o-check-circle')
                ->badge($presentCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'present')),

            'leave' => Tab::make('Approved Leave')
                ->icon('heroicon-o-sun')
                ->badge($leaveCount)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'leave')),

            'absent' => Tab::make('Absent')
                ->icon('heroicon-o-x-circle')
                ->badge($absentCount)
                ->badgeColor($absentCount > 0 ? 'danger' : 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'absent')),
        ];
    }
}
