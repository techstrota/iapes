<?php

namespace App\Filament\Intern\Resources\AttendanceResource\Pages;

use App\Filament\Intern\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\InternManagement\DailyLogbook;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static string $view = 'filament.intern.attendance.list-attendances';
    protected static ?string $title = 'Attendance & Work Logbook';

    public string $calendarMonth = '';
    public ?string $selectedDate = null;
    public string $viewMode = 'calendar';

    // Pop-up Modal State for Daily Logbook CRUD
    public bool $showLogModal = false;
    public ?int $editingLogId = null;
    public string $modalDate = '';
    public string $modalTasks = '';
    public string $modalSupervisorFeedback = '';

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

    public function openCreateLogModal(?string $date = null): void
    {
        $targetDate = $date ?: ($this->selectedDate ?: now()->toDateString());
        $internId = Auth::id();

        $eligibility = DailyLogbook::isDateEligibleForLog($targetDate, $internId);
        if (!($eligibility['allowed'] ?? $eligibility['eligible'] ?? true)) {
            Notification::make()
                ->title('Logbook Submission Disabled')
                ->body($eligibility['reason'])
                ->warning()
                ->send();
            return;
        }

        $this->editingLogId = null;
        $this->modalDate = $targetDate;
        $this->modalTasks = '';
        $this->modalSupervisorFeedback = '';
        $this->showLogModal = true;
    }

    public function openEditLogModal(int $logId): void
    {
        $log = DailyLogbook::where('intern_id', Auth::id())->find($logId);
        if (!$log) {
            Notification::make()->title('Log entry not found')->danger()->send();
            return;
        }

        $targetDate = $log->date instanceof \DateTimeInterface ? $log->date->format('Y-m-d') : (string)$log->date;
        $eligibility = DailyLogbook::isDateEligibleForLog($targetDate, Auth::id());
        if (!($eligibility['allowed'] ?? $eligibility['eligible'] ?? true)) {
            Notification::make()
                ->title('Logbook Editing Disabled')
                ->body($eligibility['reason'])
                ->warning()
                ->send();
            return;
        }

        $this->editingLogId = $log->id;
        $this->modalDate = $targetDate;
        $this->modalTasks = $log->tasks_completed;
        $this->modalSupervisorFeedback = $log->supervisor_remarks ?? '';
        $this->showLogModal = true;
    }

    public function closeLogModal(): void
    {
        $this->showLogModal = false;
        $this->editingLogId = null;
        $this->modalTasks = '';
        $this->modalSupervisorFeedback = '';
    }

    public function saveLogModal(): void
    {
        $this->validate([
            'modalDate' => 'required|date',
            'modalTasks' => 'required|string|min:3',
        ], [
            'modalDate.required' => 'Please select a date for the work note.',
            'modalTasks.required' => "Please write what you have accomplished today.",
            'modalTasks.min' => "Your work note must be at least 3 characters.",
        ]);

        $internId = Auth::id();
        $targetDate = Carbon::parse($this->modalDate)->format('Y-m-d');

        // Check if date is eligible (not Sunday or Holiday)
        $eligibility = DailyLogbook::isDateEligibleForLog($targetDate, $internId);
        if (!($eligibility['allowed'] ?? $eligibility['eligible'] ?? true)) {
            Notification::make()
                ->title('Date Not Eligible')
                ->body($eligibility['reason'])
                ->warning()
                ->send();
            return;
        }

        // Prevent duplicate entries for the same intern and date
        $dupQuery = DailyLogbook::where('intern_id', $internId)
            ->whereDate('date', $targetDate);

        if ($this->editingLogId) {
            $dupQuery->where('id', '!=', $this->editingLogId);
        }

        if ($dupQuery->exists()) {
            Notification::make()
                ->title('Logbook Entry Already Exists')
                ->body('A logbook entry for ' . Carbon::parse($targetDate)->format('d M Y') . ' already exists. Please update that entry instead.')
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        try {
            if ($this->editingLogId) {
                $log = DailyLogbook::where('intern_id', $internId)->findOrFail($this->editingLogId);
                $log->update([
                    'date' => $targetDate,
                    'tasks_completed' => trim($this->modalTasks),
                ]);

                Notification::make()
                    ->title('Work log updated successfully')
                    ->success()
                    ->send();
            } else {
                DailyLogbook::create([
                    'intern_id' => $internId,
                    'date' => $targetDate,
                    'tasks_completed' => trim($this->modalTasks),
                    'status' => 'submitted',
                ]);

                Notification::make()
                    ->title('Daily work note submitted successfully')
                    ->success()
                    ->send();
            }

            $this->selectedDate = $targetDate;
            $this->showLogModal = false;
            $this->editingLogId = null;
        } catch (UniqueConstraintViolationException | QueryException $e) {
            Notification::make()
                ->title('Duplicate Entry Detected')
                ->body('A log entry for this date already exists. Please update the existing entry.')
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function deleteLog(int $logId): void
    {
        $log = DailyLogbook::where('intern_id', Auth::id())->find($logId);
        if ($log) {
            $log->delete();
            Notification::make()
                ->title('Daily work log deleted successfully')
                ->success()
                ->send();
        }
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

        $logbooks = \App\Models\InternManagement\DailyLogbook::where('intern_id', $internId)
            ->whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
            ->get();

        $logbookMap = [];
        foreach ($logbooks as $log) {
            $rawDate = $log->date;
            $dateKey = $rawDate instanceof \DateTimeInterface ? $rawDate->format('Y-m-d') : (string)$rawDate;
            $logbookMap[$dateKey] = [
                'id' => $log->id,
                'tasks_completed' => $log->tasks_completed,
                'supervisor_remarks' => $log->supervisor_remarks,
            ];
        }

        $calendarDays = [];
        $cursor = $startOfGrid->copy();
        while ($cursor->lte($endOfGrid)) {
            $dateStr = $cursor->toDateString();
            $isSunday = $cursor->isSunday();
            $attRecord = $attendanceMap[$dateStr] ?? null;
            $logRecord = $logbookMap[$dateStr] ?? null;

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
                'logbook'        => $logRecord,
                'hasLogbook'     => !empty($logRecord),
            ];

            $cursor->addDay();
        }

        // Monthly stats for current viewing month
        $monthQuery = Attendance::where('intern_id', $internId)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);

        $monthPresent = (clone $monthQuery)->where('status', 'present')->count();
        $monthLeave   = (clone $monthQuery)->where('status', 'leave')->count();
        $monthAbsent  = (clone $monthQuery)->where('status', 'absent')->count();
        $monthHoliday = (clone $monthQuery)->where('status', 'holiday')->count();

        $monthAttended = $monthPresent;
        $monthTotalMarked = $monthAttended + $monthLeave + $monthAbsent;
        $monthCompliance = $monthTotalMarked > 0 ? round(($monthAttended / $monthTotalMarked) * 100, 1) : 100.0;

        // Selected day detail
        $selectedDayInfo = null;
        if ($this->selectedDate) {
            $selCarbon = Carbon::parse($this->selectedDate);
            $selDateStr = $selCarbon->format('Y-m-d');
            $selAtt = $attendanceMap[$selDateStr] ?? null;
            $isSelHoliday = ($selAtt['status'] ?? null) === 'holiday' || Attendance::whereDate('date', $selDateStr)->where('status', 'holiday')->exists();
            $selectedDayInfo = [
                'date'      => $selDateStr,
                'formatted' => $selCarbon->format('l, d F Y'),
                'isToday'   => $selCarbon->isToday(),
                'isWeekend' => $selCarbon->isSunday(),
                'isHoliday' => $isSelHoliday,
                'status'    => $selAtt['status'] ?? ($isSelHoliday ? 'holiday' : ($selCarbon->isSunday() ? 'weekend' : ($selCarbon->isPast() && !$selCarbon->isToday() ? 'unmarked' : 'future'))),
                'note'      => $selAtt['note'] ?? null,
                'logbook'   => $logbookMap[$selDateStr] ?? null,
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
