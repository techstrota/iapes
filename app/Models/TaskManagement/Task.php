<?php

namespace App\Models\TaskManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\TaskManagement\TaskAssignment;
use App\Traits\LogsActivity;
use Illuminate\Support\Collection;

class Task extends Model
{
    use LogsActivity;

    protected $primaryKey = 'task_id';

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'attachment',
        'priority',
        'status', // NEW: 'active' | 'completed'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class, 'task_id');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

    // ─── Helper Attributes ─────────────────────────────────────────

    /**
     * Resolves ALL interns assigned to this task, regardless of
     * whether it was assigned to an individual intern, a team, or a batch.
     * Returns a Collection of Intern models.
     */
    public function getAssignedInternsAttribute(): Collection
    {
        $assignment = $this->assignments()->with(['intern', 'team.interns', 'batch.interns'])->first();

        if (!$assignment) {
            return collect();
        }

        return match ($assignment->assigned_type) {
            'intern' => $assignment->intern ? collect([$assignment->intern]) : collect(),
            'team'   => $assignment->team?->interns ?? collect(),
            'batch'  => $assignment->batch?->interns ?? collect(),
            default  => collect(),
        };
    }

    /**
     * Returns the total count of interns assigned to this task.
     */
    public function getTotalAssignedCountAttribute(): int
    {
        return $this->assigned_interns->count();
    }

    /**
     * Returns count of interns who have submitted (any status).
     */
    public function getSubmittedCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Returns count of interns who are assigned but have NOT submitted yet.
     */
    public function getPendingCountAttribute(): int
    {
        return $this->total_assigned_count - $this->submitted_count;
    }

    /**
     * Returns count of interns whose submission has been evaluated
     * (evaluated_at is not null).
     */
    public function getEvaluatedCountAttribute(): int
    {
        return $this->submissions()->whereNotNull('evaluated_at')->count();
    }

    /**
     * Returns a Collection of Intern models who have NOT yet submitted.
     */
    public function getPendingInternsAttribute(): Collection
    {
        $submittedInternIds = $this->submissions()->pluck('intern_id');
        return $this->assigned_interns->whereNotIn('id', $submittedInternIds)->values();
    }

    /**
     * True if due_date is in the past AND the task is still active.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /**
     * Checks if all assigned interns have an 'approved' submission.
     * If yes, sets status = 'completed' and saves.
     * Call this after each evaluation save.
     */
    public function checkAndAutoComplete(): void
    {
        $totalInterns = $this->total_assigned_count;

        if ($totalInterns === 0) {
            return; // Nothing to check
        }

        $approvedCount = $this->submissions()
            ->where('status', 'approved')
            ->count();

        if ($approvedCount >= $totalInterns) {
            $this->status = 'completed';
            $this->saveQuietly(); // saveQuietly() prevents re-triggering activity logs
        }
    }
}
