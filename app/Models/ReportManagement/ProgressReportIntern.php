<?php

namespace App\Models\ReportManagement;

use App\Models\InternManagement\Intern;
use App\Models\InternManagement\DailyLogbook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ProgressReportIntern extends Model
{
    use HasFactory;

    protected $table = 'progress_report_interns';

    protected $fillable = [
        'progress_report_id',
        'intern_id',
        'weekly_summary',
        'supervisor_remarks',
        'criteria_ratings',
        'mentor_final_comments',
        'is_evaluated',
        'evaluated_at',
        'evaluated_by',
    ];

    protected $casts = [
        'is_evaluated'     => 'boolean',
        'evaluated_at'     => 'datetime',
        'criteria_ratings' => 'array',
    ];

    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class, 'progress_report_id');
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Fetch daily logbooks submitted by this intern during the report week.
     */
    public function getDailyLogbooksAttribute(): Collection
    {
        if (!$this->progressReport || !$this->progressReport->week_start_date || !$this->progressReport->week_end_date) {
            return collect();
        }

        return DailyLogbook::where('intern_id', $this->intern_id)
            ->whereBetween('date', [
                $this->progressReport->week_start_date->toDateString(),
                $this->progressReport->week_end_date->toDateString()
            ])
            ->orderBy('date', 'asc')
            ->get();
    }
}
