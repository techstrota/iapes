<?php

namespace App\Models\ReportManagement;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\InternManagement\DailyLogbook;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'week_start_date',
        'week_end_date',
        'selection_mode',
        'batch_id',
        'team_id',
        'created_by',
        'status',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date'   => 'date',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InternshipBatch::class, 'batch_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(InternTeam::class, 'team_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reportInterns(): HasMany
    {
        return $this->hasMany(ProgressReportIntern::class, 'progress_report_id');
    }

    public function interns(): BelongsToMany
    {
        return $this->belongsToMany(Intern::class, 'progress_report_interns')
            ->withPivot(['weekly_summary', 'supervisor_remarks', 'is_evaluated', 'evaluated_at', 'evaluated_by'])
            ->withTimestamps();
    }

    public function getTotalInternsCountAttribute(): int
    {
        return $this->reportInterns()->count();
    }

    public function getEvaluatedCountAttribute(): int
    {
        return $this->reportInterns()->where('is_evaluated', true)->count();
    }

    public function getPendingCountAttribute(): int
    {
        return $this->reportInterns()->where('is_evaluated', false)->count();
    }

    public function getFormattedDateRangeAttribute(): string
    {
        if (!$this->week_start_date || !$this->week_end_date) {
            return '';
        }

        return Carbon::parse($this->week_start_date)->format('d M Y') . ' — ' . Carbon::parse($this->week_end_date)->format('d M Y');
    }

    public function getTotalDailyLogsCountAttribute(): int
    {
        $internIds = $this->reportInterns()->pluck('intern_id')->toArray();
        if (empty($internIds) || !$this->week_start_date || !$this->week_end_date) {
            return 0;
        }

        return DailyLogbook::whereIn('intern_id', $internIds)
            ->whereBetween('date', [$this->week_start_date->toDateString(), $this->week_end_date->toDateString()])
            ->count();
    }
}
