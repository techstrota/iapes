<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Carbon\Carbon;

class ListAttendances extends Page
{
    use WithPagination;

    protected static string $resource = AttendanceResource::class;
    protected static string $view = 'filament.intern-management.attendance-hub';
    protected static ?string $title = 'Attendance Hub';

    // 4 Stitch views: 'muster', 'batch', 'group', 'individual'
    public string $activeTab = 'muster';

    public string $search = '';
    public ?int $selectedBatchId = null;
    public ?int $selectedTeamId = null;
    public string $statusFilter = 'all'; // all, present, wfh, absent, leave, unmarked
    public string $selectedDate = '';
    public int $perPage = 10;

    // For Individual Tab
    public ?int $selectedInternId = null;
    public string $calendarMonth = '';

    // Mark Attendance Modal State
    public bool $showMarkModal = false;
    public ?int $markInternId = null; // null = roll-call all active interns, or specific intern id
    public string $markDate = '';
    public string $markStatus = 'present'; // 'present', 'absent', 'wfh', 'leave'
    public string $markNote = '';

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->calendarMonth = now()->format('Y-m');

        // Set initial selected intern for individual view
        $firstIntern = Intern::where('is_active', true)->first();
        $this->selectedInternId = $firstIntern?->id;
    }

    public function updatedSelectedDate(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['muster', 'batch', 'group', 'individual'])) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function openInternCalendar(int $internId): void
    {
        $this->selectedInternId = $internId;
        $this->activeTab = 'individual';
        $this->calendarMonth = Carbon::parse($this->selectedDate)->format('Y-m');
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

    public function updatedSelectedTeamId(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
        $this->resetPage();
    }

    public function nextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
        $this->resetPage();
    }

    public function previousWeek(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subWeek()->toDateString();
        $this->resetPage();
    }

    public function nextWeek(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addWeek()->toDateString();
        $this->resetPage();
    }

    public function setToday(): void
    {
        $this->selectedDate = now()->toDateString();
        $this->calendarMonth = now()->format('Y-m');
        $this->resetPage();
    }

    public function previousMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth . '-01')->addMonth()->format('Y-m');
    }

    // Modal Control Methods
    public function openMarkModal(?int $internId = null): void
    {
        $this->markInternId = $internId;
        $this->markDate = $this->selectedDate;
        $this->markStatus = 'present';
        $this->markNote = '';
        $this->showMarkModal = true;
    }

    public function closeMarkModal(): void
    {
        $this->showMarkModal = false;
    }

    public function setMarkStatus(string $status): void
    {
        $this->markStatus = $status;
    }

    public function submitMarkModal(): void
    {
        $date = $this->markDate ?: $this->selectedDate;
        $status = $this->markStatus;
        $note = filled($this->markNote) ? trim($this->markNote) : null;

        if ($this->markInternId) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $this->markInternId,
                    'date' => $date,
                ],
                [
                    'status' => $status,
                    'note' => $note,
                ]
            );

            $intern = Intern::find($this->markInternId);
            $name = $intern?->name ?: ($intern?->intern_code ?? 'Intern');

            Notification::make()
                ->title("Attendance marked as " . ucfirst($status) . " for {$name}")
                ->success()
                ->send();
        } else {
            // Roll-Call All active interns for the chosen date
            $interns = Intern::where('is_active', true)->get();
            foreach ($interns as $intern) {
                Attendance::updateOrCreate(
                    [
                        'intern_id' => $intern->id,
                        'date' => $date,
                    ],
                    [
                        'status' => $status,
                        'note' => $note,
                    ]
                );
            }

            Notification::make()
                ->title("Roll-Call All: Marked all active interns as " . ucfirst($status) . " for " . Carbon::parse($date)->format('d M Y'))
                ->success()
                ->send();
        }

        $this->showMarkModal = false;
    }

    public function setAttendanceStatus(int $internId, string $status, ?string $date = null): void
    {
        $targetDate = $date ?: $this->selectedDate;

        Attendance::updateOrCreate(
            [
                'intern_id' => $internId,
                'date' => $targetDate,
            ],
            [
                'status' => $status,
            ]
        );

        Notification::make()
            ->title('Attendance marked as ' . ucfirst($status))
            ->success()
            ->duration(1500)
            ->send();
    }

    public function markAllPresent(): void
    {
        $interns = Intern::where('is_active', true)->get();
        foreach ($interns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $this->selectedDate,
                ],
                [
                    'status' => 'present',
                ]
            );
        }

        Notification::make()
            ->title('All active interns marked as Present for ' . Carbon::parse($this->selectedDate)->format('d M Y'))
            ->success()
            ->send();
    }

    public function markAllAbsent(): void
    {
        $interns = Intern::where('is_active', true)->get();
        foreach ($interns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $this->selectedDate,
                ],
                [
                    'status' => 'absent',
                ]
            );
        }

        Notification::make()
            ->title('All active interns marked as Absent for ' . Carbon::parse($this->selectedDate)->format('d M Y'))
            ->warning()
            ->send();
    }

    public function markAllWfh(): void
    {
        $interns = Intern::where('is_active', true)->get();
        foreach ($interns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $this->selectedDate,
                ],
                [
                    'status' => 'wfh',
                ]
            );
        }

        Notification::make()
            ->title('All active interns marked as WFH for ' . Carbon::parse($this->selectedDate)->format('d M Y'))
            ->info()
            ->send();
    }

    public function markAllLeave(): void
    {
        $interns = Intern::where('is_active', true)->get();
        foreach ($interns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $this->selectedDate,
                ],
                [
                    'status' => 'leave',
                ]
            );
        }

        Notification::make()
            ->title('All active interns marked on Leave for ' . Carbon::parse($this->selectedDate)->format('d M Y'))
            ->info()
            ->send();
    }

    public function markBatchPresent(int $batchId): void
    {
        $query = Intern::where('is_active', true);
        if ($batchId > 0) {
            $query->where('internship_batch_id', $batchId);
        } else {
            $query->whereNull('internship_batch_id');
        }
        $interns = $query->get();

        foreach ($interns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $this->selectedDate,
                ],
                ['status' => 'present']
            );
        }

        Notification::make()
            ->title('Batch roll-call recorded: All marked Present')
            ->success()
            ->send();
    }

    public function markTeamPresent(int $teamId): void
    {
        $query = Intern::where('is_active', true);
        if ($teamId > 0) {
            $query->where('intern_team_id', $teamId);
        } else {
            $query->whereNull('intern_team_id');
        }
        $interns = $query->get();

        foreach ($interns as $intern) {
            Attendance::updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'date' => $this->selectedDate,
                ],
                ['status' => 'present']
            );
        }

        Notification::make()
            ->title('Squad roll-call recorded: All marked Present')
            ->success()
            ->send();
    }

    public function exportAttendance()
    {
        $date = $this->selectedDate;
        $interns = Intern::with(['attendances' => fn ($q) => $q->where('date', $date), 'batch', 'team'])
            ->where(function ($q) use ($date) {
                $q->where('is_active', true)
                  ->orWhereHas('attendances', fn ($sub) => $sub->where('date', $date));
            })
            ->get();

        $csvFileName = 'attendance_' . $date . '.csv';

        return response()->streamDownload(function () use ($interns, $date) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Intern Code', 'Name', 'Batch', 'Project', 'Status']);

            foreach ($interns as $intern) {
                $status = $intern->attendances->first()?->status ?? 'unmarked';
                fputcsv($file, [
                    $date,
                    $intern->intern_code ?: 'INT-' . str_pad($intern->id, 3, '0', STR_PAD_LEFT),
                    $intern->name,
                    $intern->batch?->batch_name ?? 'Batch not assigned',
                    $intern->project_name ?? $intern->team?->team_name ?? 'Project not assigned',
                    ucfirst($status),
                ]);
            }
            fclose($file);
        }, $csvFileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function getStats(): array
    {
        $total = Intern::where('is_active', true)->count();
        $date = $this->selectedDate;

        $present = Attendance::where('date', $date)->where('status', 'present')->count();
        $wfh = Attendance::where('date', $date)->where('status', 'wfh')->count();
        $leave = Attendance::where('date', $date)->where('status', 'leave')->count();
        $absent = Attendance::where('date', $date)->where('status', 'absent')->count();
        $unmarked = max(0, $total - ($present + $wfh + $leave + $absent));

        $marked = $present + $wfh + $leave + $absent;
        $rate = $marked > 0 ? round((($present + $wfh) / $marked) * 100, 1) : 0.0;
        $isSunday = Carbon::parse($date)->isSunday();

        return [
            'total' => $total,
            'present' => $present,
            'wfh' => $wfh,
            'leave' => $leave,
            'absent' => $absent,
            'unmarked' => $unmarked,
            'rate' => $rate,
            'isAnyMarked' => $marked > 0,
            'isSunday' => $isSunday,
            'isWeekOff' => $isSunday,
        ];
    }

    public function getWeekDays(): array
    {
        $centerDate = Carbon::parse($this->selectedDate);
        $startOfWeek = $centerDate->copy()->startOfWeek(Carbon::MONDAY);
        $days = [];

        // 7 days: Monday through Saturday (working days) + Sunday (scheduled week-off)
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $isSunday = $day->isSunday();
            $days[] = [
                'date' => $day->toDateString(),
                'dayName' => $day->format('D'),
                'dayNum' => $day->format('d'),
                'isToday' => $day->isToday(),
                'isSelected' => $day->toDateString() === $this->selectedDate,
                'isSunday' => $isSunday,
                'isWeekOff' => $isSunday,
            ];
        }

        return $days;
    }

    public function getWeeklyChartDistribution(): array
    {
        $weekDays = $this->getWeekDays();
        $total = Intern::where('is_active', true)->count() ?: 1;
        $distribution = [];

        foreach ($weekDays as $day) {
            $d = $day['date'];
            $isSunday = $day['isSunday'] ?? false;
            $p = Attendance::where('date', $d)->where('status', 'present')->count();
            $w = Attendance::where('date', $d)->where('status', 'wfh')->count();
            $l = Attendance::where('date', $d)->where('status', 'leave')->count();
            $a = Attendance::where('date', $d)->where('status', 'absent')->count();

            $pPct = round(($p / $total) * 100);
            $wPct = round(($w / $total) * 100);
            $lPct = round(($l / $total) * 100);
            $aPct = round(($a / $total) * 100);

            $distribution[] = [
                'date' => $d,
                'label' => $day['dayName'] . ' ' . $day['dayNum'],
                'isToday' => $day['isToday'],
                'isSelected' => $day['isSelected'],
                'isSunday' => $isSunday,
                'isWeekOff' => $isSunday,
                'present' => $p,
                'wfh' => $w,
                'leave' => $l,
                'absent' => $a,
                'pPct' => $pPct,
                'wPct' => $wPct,
                'lPct' => $lPct,
                'aPct' => $aPct,
                'totalPct' => min(100, $pPct + $wPct + $lPct + $aPct),
            ];
        }

        return $distribution;
    }

    public function getInternsQuery()
    {
        $date = $this->selectedDate;
        $query = Intern::where('is_active', true)->with([
            'attendances',
            'batch',
            'team',
        ]);

        if (filled($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('intern_code', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('project_name', 'like', $term)
                    ->orWhereHas('batch', fn ($b) => $b->where('batch_name', 'like', $term));
            });
        }

        if ($this->selectedBatchId) {
            $query->where('internship_batch_id', $this->selectedBatchId);
        }

        if ($this->selectedTeamId) {
            $query->where('intern_team_id', $this->selectedTeamId);
        }

        if ($this->statusFilter !== 'all') {
            $filter = $this->statusFilter;
            if ($filter === 'unmarked') {
                $query->where(function ($sub) use ($date) {
                    $sub->whereDoesntHave('attendances', fn ($q) => $q->where('date', $date))
                        ->orWhereHas('attendances', fn ($q) => $q->where('date', $date)->where('status', 'unmarked'));
                });
            } else {
                $query->whereHas('attendances', fn ($q) => $q->where('date', $date)->where('status', $filter));
            }
        }

        return $query->orderBy('name');
    }

    public function getBatchesOverview(): array
    {
        $batches = InternshipBatch::where('is_archived', false)->get();
        $date = $this->selectedDate;
        $weekDays = $this->getWeekDays();
        $batchData = [];

        foreach ($batches as $batch) {
            $interns = Intern::where('is_active', true)
                ->where('internship_batch_id', $batch->id)
                ->with(['attendances', 'team'])
                ->get();

            $total = $interns->count();
            $presentToday = 0;
            $wfhToday = 0;
            $leaveToday = 0;
            $absentToday = 0;

            $memberRows = [];

            foreach ($interns as $intern) {
                $attToday = $intern->attendances->firstWhere('date', $date);
                $status = $attToday?->status ?? 'unmarked';

                if ($status === 'present') $presentToday++;
                elseif ($status === 'wfh') $wfhToday++;
                elseif ($status === 'leave') $leaveToday++;
                elseif ($status === 'absent') $absentToday++;

                // Week day statuses
                $weekStatus = [];
                foreach ($weekDays as $wd) {
                    $att = $intern->attendances->firstWhere('date', $wd['date']);
                    $weekStatus[$wd['date']] = $att?->status ?? 'unmarked';
                }

                $allAtt = $intern->attendances->whereIn('status', ['present', 'wfh', 'leave', 'absent']);
                $tot = $allAtt->count();
                $rate = $tot > 0 ? round(($allAtt->whereIn('status', ['present', 'wfh'])->count() / $tot) * 100, 1) : 0.0;

                $memberRows[] = [
                    'intern' => $intern,
                    'statusToday' => $status,
                    'weekStatus' => $weekStatus,
                    'rate' => $rate,
                ];
            }

            $markedTotal = $presentToday + $wfhToday + $leaveToday + $absentToday;
            $rate = $markedTotal > 0 ? round((($presentToday + $wfhToday) / $markedTotal) * 100, 1) : 0.0;

            $batchData[] = [
                'batch' => $batch,
                'total' => $total,
                'presentToday' => $presentToday,
                'wfhToday' => $wfhToday,
                'leaveToday' => $leaveToday,
                'absentToday' => $absentToday,
                'rate' => $rate,
                'members' => $memberRows,
                'is_unassigned' => false,
            ];
        }

        // Include unassigned batch interns so they are not missing from attendance overview
        $unassignedInterns = Intern::where('is_active', true)
            ->whereNull('internship_batch_id')
            ->with(['attendances', 'team'])
            ->get();

        if ($unassignedInterns->count() > 0) {
            $total = $unassignedInterns->count();
            $presentToday = 0;
            $wfhToday = 0;
            $leaveToday = 0;
            $absentToday = 0;
            $memberRows = [];

            foreach ($unassignedInterns as $intern) {
                $attToday = $intern->attendances->firstWhere('date', $date);
                $status = $attToday?->status ?? 'unmarked';

                if ($status === 'present') $presentToday++;
                elseif ($status === 'wfh') $wfhToday++;
                elseif ($status === 'leave') $leaveToday++;
                elseif ($status === 'absent') $absentToday++;

                $weekStatus = [];
                foreach ($weekDays as $wd) {
                    $att = $intern->attendances->firstWhere('date', $wd['date']);
                    $weekStatus[$wd['date']] = $att?->status ?? 'unmarked';
                }

                $allAtt = $intern->attendances->whereIn('status', ['present', 'wfh', 'leave', 'absent']);
                $tot = $allAtt->count();
                $rate = $tot > 0 ? round(($allAtt->whereIn('status', ['present', 'wfh'])->count() / $tot) * 100, 1) : 0.0;

                $memberRows[] = [
                    'intern' => $intern,
                    'statusToday' => $status,
                    'weekStatus' => $weekStatus,
                    'rate' => $rate,
                ];
            }

            $markedTotal = $presentToday + $wfhToday + $leaveToday + $absentToday;
            $rate = $markedTotal > 0 ? round((($presentToday + $wfhToday) / $markedTotal) * 100, 1) : 0.0;

            $dummyBatch = new InternshipBatch();
            $dummyBatch->id = 0;
            $dummyBatch->batch_name = 'Batch Not Assigned';
            $dummyBatch->batch_timing = 'Shift Not Assigned';
            $dummyBatch->no_of_interns = $total;

            $batchData[] = [
                'batch' => $dummyBatch,
                'total' => $total,
                'presentToday' => $presentToday,
                'wfhToday' => $wfhToday,
                'leaveToday' => $leaveToday,
                'absentToday' => $absentToday,
                'rate' => $rate,
                'members' => $memberRows,
                'is_unassigned' => true,
            ];
        }

        return $batchData;
    }

    public function getGroupsOverview(): array
    {
        $teams = InternTeam::where('is_archived', false)->get();
        $date = $this->selectedDate;
        $weekDays = $this->getWeekDays();
        $groupData = [];

        foreach ($teams as $team) {
            $interns = Intern::where('is_active', true)
                ->where('intern_team_id', $team->id)
                ->with(['attendances', 'batch'])
                ->get();

            $total = $interns->count();
            $presentToday = 0;
            $wfhToday = 0;
            $leaveToday = 0;
            $absentToday = 0;

            $memberRows = [];

            foreach ($interns as $intern) {
                $attToday = $intern->attendances->firstWhere('date', $date);
                $status = $attToday?->status ?? 'unmarked';

                if ($status === 'present') $presentToday++;
                elseif ($status === 'wfh') $wfhToday++;
                elseif ($status === 'leave') $leaveToday++;
                elseif ($status === 'absent') $absentToday++;

                // Week day statuses
                $weekStatus = [];
                foreach ($weekDays as $wd) {
                    $att = $intern->attendances->firstWhere('date', $wd['date']);
                    $weekStatus[$wd['date']] = $att?->status ?? 'unmarked';
                }

                $allAtt = $intern->attendances->whereIn('status', ['present', 'wfh', 'leave', 'absent']);
                $tot = $allAtt->count();
                $rate = $tot > 0 ? round(($allAtt->whereIn('status', ['present', 'wfh'])->count() / $tot) * 100, 1) : 0.0;

                $memberRows[] = [
                    'intern' => $intern,
                    'statusToday' => $status,
                    'weekStatus' => $weekStatus,
                    'rate' => $rate,
                ];
            }

            $markedTotal = $presentToday + $wfhToday + $leaveToday + $absentToday;
            $rate = $markedTotal > 0 ? round((($presentToday + $wfhToday) / $markedTotal) * 100, 1) : 0.0;

            $groupData[] = [
                'team' => $team,
                'total' => $total,
                'presentToday' => $presentToday,
                'wfhToday' => $wfhToday,
                'leaveToday' => $leaveToday,
                'absentToday' => $absentToday,
                'rate' => $rate,
                'members' => $memberRows,
                'is_unassigned' => false,
            ];
        }

        // Include unassigned project interns so they are not missing from squad overview
        $unassignedTeamInterns = Intern::where('is_active', true)
            ->whereNull('intern_team_id')
            ->with(['attendances', 'batch'])
            ->get();

        if ($unassignedTeamInterns->count() > 0) {
            $total = $unassignedTeamInterns->count();
            $presentToday = 0;
            $wfhToday = 0;
            $leaveToday = 0;
            $absentToday = 0;
            $memberRows = [];

            foreach ($unassignedTeamInterns as $intern) {
                $attToday = $intern->attendances->firstWhere('date', $date);
                $status = $attToday?->status ?? 'unmarked';

                if ($status === 'present') $presentToday++;
                elseif ($status === 'wfh') $wfhToday++;
                elseif ($status === 'leave') $leaveToday++;
                elseif ($status === 'absent') $absentToday++;

                $weekStatus = [];
                foreach ($weekDays as $wd) {
                    $att = $intern->attendances->firstWhere('date', $wd['date']);
                    $weekStatus[$wd['date']] = $att?->status ?? 'unmarked';
                }

                $allAtt = $intern->attendances->whereIn('status', ['present', 'wfh', 'leave', 'absent']);
                $tot = $allAtt->count();
                $rate = $tot > 0 ? round(($allAtt->whereIn('status', ['present', 'wfh'])->count() / $tot) * 100, 1) : 0.0;

                $memberRows[] = [
                    'intern' => $intern,
                    'statusToday' => $status,
                    'weekStatus' => $weekStatus,
                    'rate' => $rate,
                ];
            }

            $markedTotal = $presentToday + $wfhToday + $leaveToday + $absentToday;
            $rate = $markedTotal > 0 ? round((($presentToday + $wfhToday) / $markedTotal) * 100, 1) : 0.0;

            $dummyTeam = new InternTeam();
            $dummyTeam->id = 0;
            $dummyTeam->team_name = 'Project Squad Not Assigned';
            $dummyTeam->track = 'General Pool';
            $dummyTeam->mentor_name = 'Unassigned';
            $dummyTeam->mentor_title = 'Direct Supervision';

            $groupData[] = [
                'team' => $dummyTeam,
                'total' => $total,
                'presentToday' => $presentToday,
                'wfhToday' => $wfhToday,
                'leaveToday' => $leaveToday,
                'absentToday' => $absentToday,
                'rate' => $rate,
                'members' => $memberRows,
                'is_unassigned' => true,
            ];
        }

        return $groupData;
    }

    public function getIndividualData(): array
    {
        $allActiveInterns = Intern::where('is_active', true)
            ->with(['attendances', 'batch', 'team'])
            ->orderBy('name')
            ->get();

        if (!$this->selectedInternId && $allActiveInterns->isNotEmpty()) {
            $this->selectedInternId = $allActiveInterns->first()->id;
        }

        $selectedIntern = $allActiveInterns->firstWhere('id', $this->selectedInternId);

        // Calculate Calendar days for $this->calendarMonth
        $monthCarbon = Carbon::parse($this->calendarMonth . '-01');
        $startOfMonth = $monthCarbon->copy()->startOfMonth();
        $endOfMonth = $monthCarbon->copy()->endOfMonth();

        // Sunday-start grid
        $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $attendanceMap = [];
        if ($selectedIntern) {
            $attendances = Attendance::where('intern_id', $selectedIntern->id)
                ->whereBetween('date', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
                ->get();
            foreach ($attendances as $att) {
                $rawDate = $att->date;
                $dateKey = $rawDate instanceof \DateTimeInterface ? $rawDate->format('Y-m-d') : (string)$rawDate;
                $attendanceMap[$dateKey] = $att->status;
            }
        }

        $calendarDays = [];
        $cursor = $startOfGrid->copy();
        while ($cursor->lte($endOfGrid)) {
            $dateStr = $cursor->toDateString();
            // Only Sunday is off - Saturday is a working day
            $isWeekend = $cursor->isSunday();
            $status = $attendanceMap[$dateStr] ?? ($isWeekend ? 'weekend' : 'unmarked');

            $calendarDays[] = [
                'date' => $dateStr,
                'day' => $cursor->format('d'),
                'isCurrentMonth' => $cursor->month === $monthCarbon->month,
                'isToday' => $cursor->isToday(),
                'isSelected' => $dateStr === $this->selectedDate,
                'isWeekend' => $isWeekend,
                'status' => $status,
            ];
            $cursor->addDay();
        }

        // Stats for selected intern
        $internStats = [
            'present' => 0,
            'wfh' => 0,
            'leave' => 0,
            'absent' => 0,
            'compliance' => 0.0,
            'totalMarked' => 0,
        ];

        if ($selectedIntern) {
            $allAtt = $selectedIntern->attendances;
            $internStats['present'] = $allAtt->where('status', 'present')->count();
            $internStats['wfh'] = $allAtt->where('status', 'wfh')->count();
            $internStats['leave'] = $allAtt->where('status', 'leave')->count();
            $internStats['absent'] = $allAtt->where('status', 'absent')->count();
            $totalMarked = $internStats['present'] + $internStats['wfh'] + $internStats['leave'] + $internStats['absent'];
            $internStats['totalMarked'] = $totalMarked;
            $internStats['compliance'] = $totalMarked > 0
                ? round((($internStats['present'] + $internStats['wfh']) / $totalMarked) * 100, 1)
                : 0.0;
        }

        return [
            'interns' => $allActiveInterns,
            'selectedIntern' => $selectedIntern,
            'calendarDays' => $calendarDays,
            'monthTitle' => $monthCarbon->format('F Y'),
            'internStats' => $internStats,
        ];
    }

    public function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'batches' => InternshipBatch::where('is_archived', false)->get(),
            'teams' => InternTeam::where('is_archived', false)->get(),
            'interns' => $this->getInternsQuery()->paginate($this->perPage),
            'allActiveInterns' => Intern::where('is_active', true)->orderBy('name')->get(),
            'selectedDateFormatted' => Carbon::parse($this->selectedDate)->format('D, d M Y'),
            'selectedWeekFormatted' => Carbon::parse($this->selectedDate)->format('F Y') . ' (Week ' . Carbon::parse($this->selectedDate)->weekOfYear . ')',
            'isToday' => $this->selectedDate === now()->toDateString(),
            'weekDays' => $this->getWeekDays(),
            'chartDistribution' => $this->getWeeklyChartDistribution(),
            'batchesOverview' => $this->getBatchesOverview(),
            'groupsOverview' => $this->getGroupsOverview(),
            'individualData' => $this->getIndividualData(),
        ];
    }
}
