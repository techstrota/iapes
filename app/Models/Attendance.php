<?php

namespace App\Models;

use App\Models\InternManagement\Intern;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'intern_id',
        'course_registration_id',
        'date',
        'status',
        'start_time',
        'end_time',
        'late_minutes',
        'note',
    ];

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function getFormattedLateDurationAttribute(): ?string
    {
        if (!$this->late_minutes || $this->late_minutes <= 0) {
            return null;
        }

        $hours = floor($this->late_minutes / 60);
        $mins = $this->late_minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }

        return "{$mins}m";
    }
}
