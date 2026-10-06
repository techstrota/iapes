<?php

namespace App\Filament\Intern\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Models\InternManagement\Intern;
use App\Models\TaskManagement\TaskAssignment;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\{TextInput, FileUpload, Hidden};

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string $view = 'filament.intern.pages.dashboard';

    public function getHeading(): string
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        return [
            'data' => $this->getDashboardData(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getActions(): array
    {
        return [];
    }

    public function getDashboardData(): array
    {
        try {
            /** @var \App\Models\InternManagement\Intern|null $intern */
            $intern = Auth::user();
            if (!$intern) {
                return [];
            }
            $userId = $intern->id;

            // 1. Scoped Assignments (Individual + Team + Batch)
            $scopedAssignments = TaskAssignment::query()
                ->where(function (Builder $q) use ($userId) {
                    $q->where('intern_id', $userId)
                      ->orWhereHas('team.interns', fn ($q2) => $q2->where('interns.id', $userId))
                      ->orWhereExists(function ($q3) use ($userId) {
                          $q3->selectRaw(1)->from('interns')
                             ->whereColumn('interns.internship_batch_id', 'task_assignments.batch_id')
                             ->where('interns.id', $userId);
                      });
                })
                ->with(['task', 'team', 'batch']);

            $allAssignments = $scopedAssignments->get();
            $totalAssigned = $allAssignments->count();

            $submissions = TaskSubmission::where('intern_id', $userId)->get()->keyBy('task_id');

            $pendingTasks = [];
            $approvedCount = 0;
            $rejectedCount = 0;
            $reviewCount = 0;

            foreach ($allAssignments as $assignment) {
                $task = $assignment->task;
                if (!$task) {
                    continue;
                }

                $sub = $submissions->get($assignment->task_id);
                if (!$sub) {
                    $pendingTasks[] = [
                        'assignment' => $assignment,
                        'task'       => $task,
                        'submission' => null,
                        'status'     => 'pending',
                    ];
                } else {
                    if ($sub->status === 'approved') {
                        $approvedCount++;
                    } elseif ($sub->status === 'rejected') {
                        $rejectedCount++;
                        $pendingTasks[] = [
                            'assignment' => $assignment,
                            'task'       => $task,
                            'submission' => $sub,
                            'status'     => 'rejected',
                        ];
                    } else {
                        $reviewCount++;
                    }
                }
            }

            // Sort pending tasks by urgency: overdue first, then nearest due date
            usort($pendingTasks, function ($a, $b) {
                $dueA = $a['task']->due_date ? Carbon::parse($a['task']->due_date)->timestamp : PHP_INT_MAX;
                $dueB = $b['task']->due_date ? Carbon::parse($b['task']->due_date)->timestamp : PHP_INT_MAX;
                return $dueA <=> $dueB;
            });

            $pendingCount = count($pendingTasks);
            $avgScore = $submissions->whereNotNull('marks')->avg('marks');

            // 2. Attendance Data
            $today = Carbon::today()->toDateString();
            $todayAttendance = Attendance::where('intern_id', $userId)->whereDate('date', $today)->first();
            $allAttendance = Attendance::where('intern_id', $userId)->orderBy('date', 'desc')->get();
            $totalDays = $allAttendance->count();
            $presentDays = $allAttendance->where('status', 'present')->count();
            $lateDays = $allAttendance->where('status', 'late')->count();
            $leaveDays = $allAttendance->where('status', 'leave')->count();
            $absentDays = $allAttendance->where('status', 'absent')->count();
            $attendedDays = $presentDays + $lateDays;
            $attendanceRate = $totalDays > 0 ? round(($attendedDays / $totalDays) * 100, 1) : 100;

            // 3. Milestone Progress
            $offer = $intern->offerletter;
            $progress = 0;
            $daysLeft = 0;
            $totalDuration = 0;
            $elapsedDays = 0;
            $startDate = '—';
            $endDate = '—';
            if ($offer && $offer->joining_date && $offer->completion_date) {
                $start = Carbon::parse($offer->joining_date);
                $end   = Carbon::parse($offer->completion_date);
                $now   = now();
                $totalDuration = max(1, $start->diffInDays($end));
                $elapsedDays   = max(0, $start->diffInDays($now, false));
                $progress      = min(100, round(($elapsedDays / $totalDuration) * 100, 1));
                $daysLeft      = max(0, $now->diffInDays($end, false));
                $startDate     = $start->format('d M Y');
                $endDate       = $end->format('d M Y');
            }

            // 4. Team & Cohort
            $batch = $intern->batch;
            $team = $intern->team;
            $teammates = $intern->teammates()->get();

            // 5. Recent Submissions (Latest 5)
            $recentSubmissions = TaskSubmission::where('intern_id', $userId)
                ->with('task')
                ->latest('submitted_at')
                ->take(5)
                ->get();

            return [
                'intern'                => $intern,
                'total_assigned'        => $totalAssigned,
                'pending_count'         => $pendingCount,
                'approved_count'        => $approvedCount,
                'rejected_count'        => $rejectedCount,
                'review_count'          => $reviewCount,
                'avg_score'             => $avgScore ? round($avgScore, 1) : null,
                'pending_tasks'         => $pendingTasks,
                'today_attendance'      => $todayAttendance,
                'total_attendance_days' => $totalDays,
                'present_days'          => $presentDays,
                'late_days'             => $lateDays,
                'leave_days'            => $leaveDays,
                'absent_days'           => $absentDays,
                'attendance_rate'       => $attendanceRate,
                'milestone_progress'    => $progress,
                'days_left'             => $daysLeft,
                'total_duration'        => $totalDuration,
                'elapsed_days'          => $elapsedDays,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'batch'                 => $batch,
                'team'                  => $team,
                'teammates'             => $teammates,
                'recent_submissions'    => $recentSubmissions,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
