<?php

namespace App\Models\InternManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\Intern;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InternTeam extends Model
{
    protected $fillable = [
        'internship_batch_id',
        'team_name',
        'project_description',
        'track',
        'status',
        'cohort_archive_name',
        'archive_note',
        'is_archived',
        'mentor_name',
        'mentor_title',
        'mentor_avatar',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeInCycle($query, ?string $cycleName)
    {
        if (!$cycleName) {
            return $query;
        }
        return $query->where('cohort_archive_name', $cycleName);
    }

    /**
     * Checks if all interns in this squad/team are completed/promoted (inactive).
     * If all interns are inactive, automatically marks the squad as archived and sets status to 'completed'.
     * If active interns are present, unarchives it back to active.
     */
    public function checkAndAutoArchive(?string $cycleName = null, ?string $note = null): void
    {
        $totalMembers = Intern::where('intern_team_id', $this->id)->count();

        // Do not auto-archive empty teams
        if ($totalMembers === 0) {
            return;
        }

        $activeMembersCount = Intern::where('intern_team_id', $this->id)
            ->where('is_active', true)
            ->count();

        if ($activeMembersCount === 0) {
            if (empty($this->cohort_archive_name)) {
                $fallbackCycle = Intern::where('intern_team_id', $this->id)
                    ->whereNotNull('cohort_archive_name')
                    ->where('cohort_archive_name', '!=', '')
                    ->latest('archived_at')
                    ->value('cohort_archive_name');

                $this->cohort_archive_name = $cycleName ?: ($fallbackCycle ?: 'Completed Project');
            }
            if (empty($this->archive_note) && !empty($note)) {
                $this->archive_note = $note;
            }

            $this->status = 'completed';
            $this->is_archived = true;
            $this->save();
        } elseif ($activeMembersCount > 0 && $this->is_archived) {
            $this->is_archived = false;
            $this->status = 'on_track';
            $this->save();
        }
    }

    public function getSquadBadgeAttribute(): string
    {
        $count = $this->interns->count();
        if ($count === 0) return 'No Members';
        if ($count === 1) return 'Solo (1 Intern)';
        if ($count === 2) return 'Pair (2 Interns)';
        if ($count === 3) return 'Trio (3 Interns)';
        return "Squad ({$count} Interns)";
    }

    public function getProjectNameAttribute(): ?string
    {
        return $this->team_name;
    }

    public function setProjectNameAttribute(?string $value): void
    {
        $this->attributes['team_name'] = $value;
    }
    public function getRouteKeyName(): string
    {
        return 'id';
    }
    
    public function batch()
    {
        return $this->belongsTo(InternshipBatch::class, 'internship_batch_id');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class, 'team_id');
    }
    // To get the students in this team
    public function interns()
    {
        // Use 'intern_team_id' to match the column in your Intern model
        return $this->hasMany(Intern::class, 'intern_team_id');
    }
}
