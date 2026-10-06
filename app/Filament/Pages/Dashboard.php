<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\Attendance;
use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\InterviewAssignment;
use App\Models\InterviewManagement\InterviewBatch;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskSubmission;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'Enterprise Dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\LatestApplications::class,
        ];
    }

    public function getStats(): array
    {
        try {
            $totalInterns = Intern::count();
            $activeInterns = Intern::where('is_active', true)->count();
            $inactiveInterns = $totalInterns - $activeInterns;

            // Attendance Today
            $today = Carbon::today()->toDateString();
            $presentCount = Attendance::whereDate('date', $today)->where('status', 'present')->count();
            $wfhCount = Attendance::whereDate('date', $today)->where('status', 'wfh')->count();
            $absentCount = Attendance::whereDate('date', $today)->where('status', 'absent')->count();
            $leaveCount = Attendance::whereDate('date', $today)->where('status', 'leave')->count();
            $totalPresent = $presentCount + $wfhCount;
            $attendanceRate = $activeInterns > 0 ? round(($totalPresent / $activeInterns) * 100) : 0;

            // Recruitment Pipeline
            $totalApplications = Application::count();
            $appliedCount = Application::where('status', 'applied')->count();
            $interviewAssigned = InterviewAssignment::count();
            $interviewPresent = InterviewAssignment::where('attendance', 'present')->count();
            $interviewSelected = InterviewAssignment::where('result', 'selected')->count();
            $interviewAbsent = InterviewAssignment::where('attendance', 'absent')->count();
            $offerLettersCount = OfferLetter::count();

            // Batches & Squads
            $activeBatches = InternshipBatch::count();
            $activeTeams = InternTeam::count();
            $interviewBatches = InterviewBatch::count();

            // Tasks & Submissions
            $totalTasks = Task::count();
            $pendingSubmissions = TaskSubmission::where(function ($q) {
                $q->whereNull('grade')->orWhere('status', 'submitted');
            })->count();
            $evaluatedSubmissions = TaskSubmission::whereNotNull('grade')->where('status', '!=', 'submitted')->count();

            return [
                'total_interns' => $totalInterns,
                'active_interns' => $activeInterns,
                'inactive_interns' => $inactiveInterns,
                'present_today' => $presentCount,
                'wfh_today' => $wfhCount,
                'absent_today' => $absentCount,
                'leave_today' => $leaveCount,
                'total_present_today' => $totalPresent,
                'attendance_rate' => $attendanceRate,
                'total_applications' => $totalApplications,
                'applied_count' => $appliedCount,
                'interview_assigned' => $interviewAssigned,
                'interview_present' => $interviewPresent,
                'interview_selected' => $interviewSelected,
                'interview_absent' => $interviewAbsent,
                'offer_letters_count' => $offerLettersCount,
                'active_batches' => $activeBatches,
                'active_teams' => $activeTeams,
                'interview_batches' => $interviewBatches,
                'total_tasks' => $totalTasks,
                'pending_submissions' => $pendingSubmissions,
                'evaluated_submissions' => $evaluatedSubmissions,
            ];
        } catch (\Throwable $e) {
            return [
                'total_interns' => 0,
                'active_interns' => 0,
                'inactive_interns' => 0,
                'present_today' => 0,
                'wfh_today' => 0,
                'absent_today' => 0,
                'leave_today' => 0,
                'total_present_today' => 0,
                'attendance_rate' => 0,
                'total_applications' => 0,
                'applied_count' => 0,
                'interview_assigned' => 0,
                'interview_present' => 0,
                'interview_selected' => 0,
                'interview_absent' => 0,
                'offer_letters_count' => 0,
                'active_batches' => 0,
                'active_teams' => 0,
                'interview_batches' => 0,
                'total_tasks' => 0,
                'pending_submissions' => 0,
                'evaluated_submissions' => 0,
            ];
        }
    }
}
