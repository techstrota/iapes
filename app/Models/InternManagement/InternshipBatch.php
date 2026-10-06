<?php

namespace App\Models\InternManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\TaskManagement\TaskSubmission;
use App\Models\InternManagement\Intern;

class InternshipBatch extends Model
{
    //
    protected $fillable = [
        'batch_name',
        'batch_timing',
        'no_of_interns',
        'team_id',
        'cohort_archive_name',
        'status',
        'is_archived',
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
     * Checks if all interns in this batch are completed/promoted (inactive).
     * If all interns are inactive, automatically marks the batch as archived and sets status to 'completed'.
     * If active interns are present, unarchives it back to active.
     */
    public function checkAndAutoArchive(?string $cycleName = null): void
    {
        $totalInterns = Intern::where('internship_batch_id', $this->id)->count();

        // Do not auto-archive freshly created batches with zero interns
        if ($totalInterns === 0) {
            return;
        }

        $activeInternsCount = Intern::where('internship_batch_id', $this->id)
            ->where('is_active', true)
            ->count();

        if ($activeInternsCount === 0) {
            if (empty($this->cohort_archive_name)) {
                $fallbackCycle = Intern::where('internship_batch_id', $this->id)
                    ->whereNotNull('cohort_archive_name')
                    ->where('cohort_archive_name', '!=', '')
                    ->latest('archived_at')
                    ->value('cohort_archive_name');

                $this->cohort_archive_name = $cycleName ?: ($fallbackCycle ?: 'Completed Cohort');
            } elseif ($cycleName && empty($this->cohort_archive_name)) {
                $this->cohort_archive_name = $cycleName;
            }

            $this->status = 'completed';
            $this->is_archived = true;
            $this->save();
        } elseif ($activeInternsCount > 0 && $this->is_archived) {
            $this->is_archived = false;
            $this->status = 'active';
            $this->save();
        }
    }



    public function interns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // The first argument is the class, the second is the foreign key ON the interns table
        return $this->hasMany(Intern::class, 'internship_batch_id');
    }
    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // This looks for teams where 'internship_batch_id' matches this batch's ID
        return $this->hasMany(InternTeam::class, 'internship_batch_id');
    }
    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Use the full namespace to ensure Laravel finds the class
        return $this->belongsTo(\App\Models\InternManagement\InternTeam::class, 'team_id');
    }
}
