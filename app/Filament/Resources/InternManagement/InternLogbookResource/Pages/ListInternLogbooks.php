<?php

namespace App\Filament\Resources\InternManagement\InternLogbookResource\Pages;

use App\Filament\Resources\InternManagement\InternLogbookResource;
use App\Models\Attendance;
use App\Models\InternManagement\DailyLogbook;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\QueryException;

class ListInternLogbooks extends Page
{
    use WithPagination;

    protected static string $resource = InternLogbookResource::class;
    protected static string $view = 'filament.intern-management.logbook-hub';
    protected static ?string $title = 'Intern Logbook Hub';

    // Tabs: 'datewise' (Cohort Overview & Calendar), 'individual' (Intern Ledger), 'register' (All Logs Table)
    public string $activeTab = 'datewise';

    // Date & Month Selection
    public string $selectedDate = '';
    public string $calendarMonth = '';

    // Date-wise sub-tab: 'submitted' or 'missing'
    public string $datewiseSubTab = 'submitted';

    // Directory / Search filters
    public string $search = '';
    public ?int $selectedBatchId = null;
    public ?int $selectedInternId = null;

    // Register Tab Filters
    public string $registerSearch = '';
    public ?int $registerBatchId = null;
    public ?int $registerInternId = null;
    public ?string $registerDateFrom = null;
    public ?string $registerDateUntil = null;
    public int $registerPerPage = 12;

    // Pop-up Modal State for Create & Edit
    public bool $showLogModal = false;
    public ?int $editingLogId = null;
    public ?int $modalInternId = null;
    public string $modalDate = '';
    public string $modalTasks = '';
    public string $modalRemarks = '';

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->calendarMonth = now()->format('Y-m');

        $firstIntern = Intern::where('is_active', true)->orderBy('name')->first();
        $this->selectedInternId = $firstIntern?->id;
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['datewise', 'individual', 'register'])) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function setDatewiseSubTab(string $subTab): void
    {
        if (in_array($subTab, ['submitted', 'missing'])) {
            $this->datewiseSubTab = $subTab;
        }
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

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        if (!empty($date) && strlen($date) >= 7) {
            $this->calendarMonth = substr($date, 0, 7);
        }
    }

    public function selectIntern(int $internId): void
    {
        $this->selectedInternId = $internId;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBatchId(): void
    {
        $this->resetPage();
    }

    // Modal Control Methods
    public function openCreateLogModal(?int $internId = null, ?string $date = null): void
    {
        $this->editingLogId = null;
        $this->modalInternId = $internId ?: $this->selectedInternId ?: Intern::where('is_active', true)->value('id');
        $this->modalDate = $date ?: $this->selectedDate ?: now()->toDateString();
        $this->modalTasks = '';
        $this->modalRemarks = '';
        $this->showLogModal = true;
    }

    public function openEditLogModal(int $logId): void
    {
        $log = DailyLogbook::find($logId);
        if (!$log) {
            Notification::make()->title('Log not found')->danger()->send();
            return;
        }

        $this->editingLogId = $log->id;
        $this->modalInternId = $log->intern_id;
        $this->modalDate = $log->date instanceof \DateTimeInterface ? $log->date->format('Y-m-d') : (string)$log->date;
        $this->modalTasks = $log->tasks_completed;
        $this->modalRemarks = $log->supervisor_remarks ?? '';
        $this->showLogModal = true;
    }

    public function closeLogModal(): void
    {
        $this->showLogModal = false;
        $this->editingLogId = null;
    }

    public function saveLogModal(): void
    {
        $this->validate([
            'modalInternId' => 'required|exists:interns,id',
            'modalDate' => 'required|date',
            'modalTasks' => 'required|string|min:3',
        ], [
            'modalInternId.required' => 'Please select an intern.',
            'modalDate.required' => 'Please select a date.',
            'modalTasks.required' => "Please write today's work note.",
            'modalTasks.min' => "The work note must be at least 3 characters.",
        ]);

        $internId = $this->modalInternId;
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

        // Check duplicate entry
        $dupQuery = DailyLogbook::where('intern_id', $internId)
            ->whereDate('date', $targetDate);

        if ($this->editingLogId) {
            $dupQuery->where('id', '!=', $this->editingLogId);
        }

        if ($dupQuery->exists()) {
            Notification::make()
                ->title('Logbook Entry Already Exists')
                ->body('A logbook entry for ' . Carbon::parse($targetDate)->format('d M Y') . ' already exists for this intern. Please update that entry instead.')
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        try {
            if ($this->editingLogId) {
                $log = DailyLogbook::findOrFail($this->editingLogId);
                $log->update([
                    'intern_id' => $internId,
                    'date' => $targetDate,
                    'tasks_completed' => trim($this->modalTasks),
                    'supervisor_remarks' => filled($this->modalRemarks) ? trim($this->modalRemarks) : null,
                ]);

                Notification::make()
                    ->title('Daily logbook entry updated successfully')
                    ->success()
                    ->send();
            } else {
                DailyLogbook::create([
                    'intern_id' => $internId,
                    'date' => $targetDate,
                    'tasks_completed' => trim($this->modalTasks),
                    'supervisor_remarks' => filled($this->modalRemarks) ? trim($this->modalRemarks) : null,
                    'status' => 'submitted',
                ]);

                Notification::make()
                    ->title('Daily logbook entry created successfully')
                    ->success()
                    ->send();
            }

            $this->showLogModal = false;
            $this->editingLogId = null;
        } catch (UniqueConstraintViolationException | QueryException $e) {
            Notification::make()
                ->title('Duplicate Entry Detected')
                ->body('A log entry for this date already exists for this intern. Please update the existing entry.')
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function deleteLog(int $logId): void
    {
        $log = DailyLogbook::find($logId);
        if ($log) {
            $log->delete();
            Notification::make()
                ->title('Logbook entry deleted successfully')
                ->success()
                ->send();
        }
    }

    // High Level KPIs
    public function getExecutiveStats(): array
    {
        $totalActive = Intern::where('is_active', true)->count();
        $selectedDate = $this->selectedDate ?: now()->toDateString();

        $loggedToday = DailyLogbook::whereDate('date', $selectedDate)->count();
        $missingToday = max(0, $totalActive - $loggedToday);
        $rateToday = $totalActive > 0 ? round(($loggedToday / $totalActive) * 100, 1) : 0;

        $monthCarbon = Carbon::parse($this->calendarMonth . '-01');
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthCarbon->copy()->endOfMonth()->toDateString();

        $monthTotalLogs = DailyLogbook::whereBetween('date', [$startOfMonth, $endOfMonth])->count();

        // Check if selected date is Sunday or Holiday
        $isSunday = Carbon::parse($selectedDate)->isSunday();
        $isHoliday = Attendance::whereDate('date', $selectedDate)->where('status', 'holiday')->exists();

        return [
            'totalActive' => $totalActive,
            'loggedToday' => $loggedToday,
            'missingToday' => $missingToday,
            'rateToday' => $rateToday,
            'monthTotalLogs' => $monthTotalLogs,
            'isSunday' => $isSunday,
            'isHoliday' => $isHoliday,
        ];
    }

    // Date-Wise Calendar Matrix
    public function getDatewiseCalendarData(): array
    {
        if (empty($this->calendarMonth)) {
            $this->calendarMonth = now()->format('Y-m');
        }

        $monthCarbon = Carbon::parse($this->calendarMonth . '-01');
        $startOfMonth = $monthCarbon->copy()->startOfMonth();
        $endOfMonth = $monthCarbon->copy()->endOfMonth();

        // Sunday-start 7-day grid
        $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $totalActive = Intern::where('is_active', true)->count();

        // Fetch all logs in this grid window
        $logs = DailyLogbook::whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
            ->selectRaw('date, count(*) as count')
            ->groupBy('date')
            ->get();

        $logCountMap = [];
        foreach ($logs as $l) {
            $rawDate = $l->date;
            $dateKey = $rawDate instanceof \DateTimeInterface ? $rawDate->format('Y-m-d') : (string)$rawDate;
            $logCountMap[$dateKey] = (int) $l->count;
        }

        // Fetch holidays in grid window
        $holidays = Attendance::whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
            ->where('status', 'holiday')
            ->distinct()
            ->pluck('date')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : (string)$d)
            ->toArray();

        $calendarDays = [];
        $cursor = $startOfGrid->copy();

        while ($cursor->lte($endOfGrid)) {
            $dateStr = $cursor->toDateString();
            $isSunday = $cursor->isSunday();
            $isHoliday = in_array($dateStr, $holidays);
            $loggedCount = $logCountMap[$dateStr] ?? 0;
            $missingCount = max(0, $totalActive - $loggedCount);
            $isPast = $cursor->isPast() && !$cursor->isToday();
            $isFuture = $cursor->isFuture();

            $rate = $totalActive > 0 ? round(($loggedCount / $totalActive) * 100) : 0;

            $calendarDays[] = [
                'date' => $dateStr,
                'day' => $cursor->format('d'),
                'isCurrentMonth' => $cursor->month === $monthCarbon->month,
                'isToday' => $cursor->isToday(),
                'isSelected' => $dateStr === $this->selectedDate,
                'isSunday' => $isSunday,
                'isHoliday' => $isHoliday,
                'isPast' => $isPast,
                'isFuture' => $isFuture,
                'loggedCount' => $loggedCount,
                'missingCount' => $missingCount,
                'totalActive' => $totalActive,
                'rate' => $rate,
            ];

            $cursor->addDay();
        }

        return [
            'monthTitle' => $monthCarbon->format('F Y'),
            'calendarDays' => $calendarDays,
        ];
    }

    // Details for the Selected Date in Date-wise view
    public function getDatewiseDayDetails(): array
    {
        $targetDate = $this->selectedDate ?: now()->toDateString();
        $carbon = Carbon::parse($targetDate);

        // All active interns query (respecting search and batch filter)
        $internsQuery = Intern::where('is_active', true)->with(['batch', 'team']);

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $internsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('intern_code', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        if ($this->selectedBatchId) {
            $internsQuery->where('internship_batch_id', $this->selectedBatchId);
        }

        $allActiveInterns = $internsQuery->orderBy('name')->get();

        // Logs for this date
        $logs = DailyLogbook::whereDate('date', $targetDate)
            ->with(['intern.batch', 'intern.team'])
            ->get()
            ->keyBy('intern_id');

        $submittedList = [];
        $missingList = [];

        foreach ($allActiveInterns as $intern) {
            $log = $logs->get($intern->id);
            if ($log) {
                $submittedList[] = [
                    'intern' => $intern,
                    'log' => $log,
                ];
            } else {
                $missingList[] = [
                    'intern' => $intern,
                ];
            }
        }

        $isSunday = $carbon->isSunday();
        $holidayRecord = Attendance::whereDate('date', $targetDate)->where('status', 'holiday')->first();

        return [
            'dateFormatted' => $carbon->format('l, d F Y'),
            'isToday' => $carbon->isToday(),
            'isSunday' => $isSunday,
            'isHoliday' => !empty($holidayRecord),
            'holidayName' => $holidayRecord?->note ?? 'Official Holiday',
            'submitted' => $submittedList,
            'missing' => $missingList,
            'submittedCount' => count($submittedList),
            'missingCount' => count($missingList),
            'totalCount' => $allActiveInterns->count(),
        ];
    }

    // Individual Intern Ledger Data (Intern-Wise Tab)
    public function getIndividualData(): array
    {
        // Directory query
        $dirQuery = Intern::where('is_active', true)->with(['batch', 'team']);

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $dirQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('intern_code', 'like', $term);
            });
        }

        if ($this->selectedBatchId) {
            $dirQuery->where('internship_batch_id', $this->selectedBatchId);
        }

        $allInterns = $dirQuery->orderBy('name')->get();

        if (!$this->selectedInternId && $allInterns->isNotEmpty()) {
            $this->selectedInternId = $allInterns->first()->id;
        }

        $selectedIntern = $allInterns->firstWhere('id', $this->selectedInternId)
            ?: Intern::where('is_active', true)->find($this->selectedInternId);

        // Monthly calendar for selected intern
        if (empty($this->calendarMonth)) {
            $this->calendarMonth = now()->format('Y-m');
        }

        $monthCarbon = Carbon::parse($this->calendarMonth . '-01');
        $startOfMonth = $monthCarbon->copy()->startOfMonth();
        $endOfMonth = $monthCarbon->copy()->endOfMonth();

        $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $logbookMap = [];
        if ($selectedIntern) {
            $logs = DailyLogbook::where('intern_id', $selectedIntern->id)
                ->whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
                ->get();

            foreach ($logs as $l) {
                $rawDate = $l->date;
                $dateKey = $rawDate instanceof \DateTimeInterface ? $rawDate->format('Y-m-d') : (string)$rawDate;
                $logbookMap[$dateKey] = $l;
            }
        }

        // Holidays
        $holidays = Attendance::whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
            ->where('status', 'holiday')
            ->distinct()
            ->pluck('date')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : (string)$d)
            ->toArray();

        $calendarDays = [];
        $cursor = $startOfGrid->copy();

        $monthLoggedCount = 0;
        $monthWorkingDays = 0;

        while ($cursor->lte($endOfGrid)) {
            $dateStr = $cursor->toDateString();
            $isSunday = $cursor->isSunday();
            $isHoliday = in_array($dateStr, $holidays);
            $log = $logbookMap[$dateStr] ?? null;
            $isCurMonth = $cursor->month === $monthCarbon->month;
            $isPast = $cursor->isPast() && !$cursor->isToday();

            $status = 'future';
            if ($log) {
                $status = 'logged';
            } elseif ($isSunday) {
                $status = 'weekend';
            } elseif ($isHoliday) {
                $status = 'holiday';
            } elseif ($isPast || $cursor->isToday()) {
                $status = 'missing';
            }

            if ($isCurMonth && !$isSunday && !$isHoliday && ($isPast || $cursor->isToday())) {
                $monthWorkingDays++;
                if ($log) {
                    $monthLoggedCount++;
                }
            }

            $calendarDays[] = [
                'date' => $dateStr,
                'day' => $cursor->format('d'),
                'isCurrentMonth' => $isCurMonth,
                'isToday' => $cursor->isToday(),
                'isSelected' => $dateStr === $this->selectedDate,
                'status' => $status,
                'log' => $log,
            ];

            $cursor->addDay();
        }

        $complianceRate = $monthWorkingDays > 0 ? round(($monthLoggedCount / $monthWorkingDays) * 100, 1) : 100.0;
        $missingDaysCount = max(0, $monthWorkingDays - $monthLoggedCount);

        // Selected Day Drawer for this intern
        $selectedDayLog = null;
        if ($this->selectedDate && $selectedIntern) {
            $selectedDayLog = $logbookMap[$this->selectedDate] ?? null;
        }

        return [
            'interns' => $allInterns,
            'selectedIntern' => $selectedIntern,
            'calendarDays' => $calendarDays,
            'monthTitle' => $monthCarbon->format('F Y'),
            'monthLoggedCount' => $monthLoggedCount,
            'monthWorkingDays' => $monthWorkingDays,
            'missingDaysCount' => $missingDaysCount,
            'complianceRate' => $complianceRate,
            'selectedDayLog' => $selectedDayLog,
        ];
    }

    // Register Tab (Global Table Query)
    public function getRegisterData()
    {
        $query = DailyLogbook::with(['intern.batch', 'intern.team']);

        if (filled($this->registerSearch)) {
            $term = '%' . trim($this->registerSearch) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('tasks_completed', 'like', $term)
                  ->orWhere('supervisor_remarks', 'like', $term)
                  ->orWhereHas('intern', function ($sub) use ($term) {
                      $sub->where('name', 'like', $term)
                          ->orWhere('intern_code', 'like', $term);
                  });
            });
        }

        if ($this->registerBatchId) {
            $query->whereHas('intern', fn ($b) => $b->where('internship_batch_id', $this->registerBatchId));
        }

        if ($this->registerInternId) {
            $query->where('intern_id', $this->registerInternId);
        }

        if ($this->registerDateFrom) {
            $query->whereDate('date', '>=', $this->registerDateFrom);
        }

        if ($this->registerDateUntil) {
            $query->whereDate('date', '<=', $this->registerDateUntil);
        }

        return $query->orderBy('date', 'desc')->paginate($this->registerPerPage);
    }

    public function getViewData(): array
    {
        return [
            'stats' => $this->getExecutiveStats(),
            'batches' => InternshipBatch::where('is_archived', false)->orderBy('batch_name')->get(),
            'allActiveInterns' => Intern::where('is_active', true)->orderBy('name')->get(),
            'datewiseCalendar' => $this->getDatewiseCalendarData(),
            'datewiseDayDetails' => $this->getDatewiseDayDetails(),
            'individualData' => $this->getIndividualData(),
            'registerData' => $this->getRegisterData(),
        ];
    }
}
