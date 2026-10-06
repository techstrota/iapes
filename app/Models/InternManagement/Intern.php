<?php

namespace App\Models\InternManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\InterviewManagement\Application;

use App\Models\InterviewManagement\OfferLetter;
use App\Models\User;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\TaskManagement\Task;
use App\Models\TaskManagement\TaskAssignment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Traits\LogsActivity;

class Intern extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'application_id',
        'internship_batch_id',
        'offer_letter_id',
        'intern_team_id',
        'intern_code',
        'username',
        'password',
        'plain_password',
        'name',
        'email',
        'phone',
        'college',
        'degree',
        'university',
        'academic_year',
        'cohort_archive_name',
        'archive_note',
        'archived_at',
        'cgpa',
        'domain',
        'skills',
        'joining_date',
        'completion_date',
        'internship_role',
        'internship_position',
        'working_hours',
        'completion_letter_template',
        'project_name',
        'project_description',
        'issuing_date',
        'is_active',
        'intern_image',
        'cert_token',
        'cert_ref_id',
        'letter_ref_id',
        'grade',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'completion_date' => 'date',
        'issuing_date' => 'date',
        'archived_at' => 'datetime',
        'is_active' => 'boolean',
        'cgpa' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        // Note: cert_token is strictly generated only when an admin generates a certificate

        // Prevent UPDATING the application_id
        static::updating(function ($intern) {
            if ($intern->isDirty('application_id')) {
                // Revert it to the original value from the database
                $intern->application_id = $intern->getOriginal('application_id');
            }
        });

        static::saved(function ($intern) {
            if ($intern->internship_batch_id && $intern->batch) {
                $intern->batch->checkAndAutoArchive($intern->cohort_archive_name);
            }
            if ($intern->intern_team_id && $intern->team) {
                $intern->team->checkAndAutoArchive($intern->cohort_archive_name, $intern->archive_note);
            }
        });
    }

    public function getPlainPasswordAttribute(): string
    {
        if (!empty($this->attributes['plain_password'])) {
            return $this->attributes['plain_password'];
        }
        if (!empty($this->username)) {
            return str($this->username)->before('@user.com')->toString();
        }
        return 'ts' . now()->format('y') . '001';
    }

    public function getAssignedProjectNameAttribute(): string
    {
        return $this->team?->team_name ?: ($this->project_name ?: 'Project not assigned');
    }

    public function getAssignedProjectDescriptionAttribute(): string
    {
        return $this->team?->project_description ?: ($this->project_description ?: 'No description provided');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where(function ($q) {
            $q->where('is_active', false)
              ->orWhereNotNull('cohort_archive_name');
        });
    }

    public function scopeInCycle($query, ?string $cycleName)
    {
        if (!$cycleName) {
            return $query;
        }
        return $query->where('cohort_archive_name', $cycleName);
    }

    /**
     * User-driven action to promote an intern to completion and move them to an archival cycle.
     */
    public function promoteToCompletion(string $cycleName, ?string $note = null, ?string $completionDate = null): void
    {
        $this->is_active = false;
        $this->cohort_archive_name = $cycleName;
        $this->archive_note = $note;
        $this->archived_at = now();

        if ($completionDate) {
            $this->completion_date = $completionDate;
        }

        $this->save();

        // Also update related project team if this intern belongs to one
        if ($this->intern_team_id && $this->team) {
            $this->team->checkAndAutoArchive($cycleName, $note);
        }

        // Also update related internship batch if this intern belongs to one
        if ($this->internship_batch_id && $this->batch) {
            $this->batch->checkAndAutoArchive($cycleName);
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'intern';
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function offerletter()
    {
        // Make sure the method name 'offerletter' matches what you wrote in the Infolist
        return $this->belongsTo(\App\Models\InterviewManagement\OfferLetter::class, 'offer_letter_id');
    } 
    

    public function user(): BelongsTo
    {
        // Intern 'username' matches User 'email'
        return $this->belongsTo(User::class, 'username', 'email');
    }

    public function batch()
    {
        return $this->belongsTo(InternshipBatch::class, 'internship_batch_id');
    }

    public function team()
    {
        // Note: This assumes you added 'team_id' to your interns table
        return $this->belongsTo(InternTeam::class, 'intern_team_id');
    }

    public function teammates()
    {
        // Gets other interns in the same team, excluding the current intern
        return $this->hasMany(Intern::class, 'intern_team_id', 'intern_team_id')
            ->where('id', '!=', $this->id);
    }

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class, 'intern_id');
    }

    public function offer_letters() // For accessing dates in i-card
    {
        return $this->belongsTo(OfferLetter::class, 'offer_letter_id');
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'intern_id');
    }

    public function completionCertificate(): HasOne
    {
        return $this->hasOne(CompletionCertificate::class, 'intern_id');
    }

    public function completionLetter(): HasOne
    {
        return $this->hasOne(CompletionLetter::class, 'intern_id');
    }

    /**
     * Resolves active tasks assigned to this intern (directly, via team, or via batch).
     */
    public function getAssignedActiveTasks(?Collection $allActiveTasks = null, ?Collection $allActiveAssignments = null): Collection
    {
        if ($allActiveAssignments === null) {
            static $cachedActiveAssignments = null;
            static $cachedActiveTasks = null;
            if ($cachedActiveAssignments === null) {
                $cachedActiveTasks = Task::where('status', 'active')
                    ->orWhereNull('status')
                    ->get();
                $cachedActiveAssignments = TaskAssignment::whereIn(
                    'task_id',
                    $cachedActiveTasks->pluck('task_id')
                )->get();
            }
            $allActiveAssignments = $cachedActiveAssignments;
            $allActiveTasks = $cachedActiveTasks;
        }

        $assignedTaskIds = $allActiveAssignments->filter(function ($a) {
            if ($a->assigned_type === 'intern' && $a->intern_id == $this->id) {
                return true;
            }
            if ($a->assigned_type === 'team' && $this->intern_team_id && $a->team_id == $this->intern_team_id) {
                return true;
            }
            if ($a->assigned_type === 'batch' && $this->internship_batch_id && $a->batch_id == $this->internship_batch_id) {
                return true;
            }
            return false;
        })->pluck('task_id')->unique();

        return $allActiveTasks ? $allActiveTasks->whereIn('task_id', $assignedTaskIds)->values() : collect();
    }

    /**
     * Get task metrics for this intern: total assigned, completed, pending, overdue.
     */
    public function getTaskStats(?Collection $allActiveTasks = null, ?Collection $allActiveAssignments = null): array
    {
        $assignedTasks = $this->getAssignedActiveTasks($allActiveTasks, $allActiveAssignments);
        $totalAssigned = $assignedTasks->count();

        $submissions = $this->submissions ?? collect();

        $completedTaskIds = $submissions->whereIn('task_id', $assignedTasks->pluck('task_id'))
            ->whereIn('status', ['approved', 'completed', 'submitted', 'reviewed'])
            ->pluck('task_id')
            ->unique();

        $completedCount = $completedTaskIds->count();
        $pendingTasks = $assignedTasks->whereNotIn('task_id', $completedTaskIds);
        $pendingCount = $pendingTasks->count();

        $overdueCount = $pendingTasks->filter(function ($task) {
            return $task->due_date && Carbon::parse($task->due_date)->endOfDay()->isPast();
        })->count();

        return [
            'total'     => $totalAssigned,
            'completed' => $completedCount,
            'pending'   => $pendingCount,
            'overdue'   => $overdueCount,
        ];
    }

    public function getTaskStatsAttribute(): array
    {
        return $this->getTaskStats();
    }

    /**
     * Get attendance record for today if exists.
     */
    public function getTodayAttendanceAttribute(): ?\App\Models\Attendance
    {
        $todayDate = now()->toDateString();
        $attendances = $this->relationLoaded('attendances') ? $this->attendances : $this->attendances()->get();
        return $attendances->first(function ($a) use ($todayDate) {
            return \Carbon\Carbon::parse($a->date)->toDateString() === $todayDate;
        });
    }

    /**
     * Get current status for today:
     * 'completed' (if archived),
     * 'present' | 'wfh' | 'leave' | 'absent' (if attendance taken today),
     * 'week-off' (if Sunday and not taken),
     * 'unmarked' (if not taken today).
     */
    public function getTodayStatusAttribute(): string
    {
        if (!$this->is_active || filled($this->cohort_archive_name)) {
            return 'completed';
        }

        $today = $this->today_attendance;
        if ($today) {
            return strtolower($today->status);
        }

        if (now()->isSunday()) {
            return 'week-off';
        }

        return 'unmarked';
    }
}
