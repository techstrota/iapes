<?php

namespace App\Models\InternManagement;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLogbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'date',
        'title',
        'tasks_completed',
        'learnings',
        'challenges',
        'hours_worked',
        'status',
        'supervisor_remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:1',
    ];

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    /**
     * Checks whether a given calendar date is eligible for daily logbook submission.
     * Logs are prohibited on Sundays (weekly off) and dates marked as a Holiday in attendance.
     *
     * @return array{allowed: bool, reason: ?string}
     */
    public static function isDateEligibleForLog(string|Carbon $date, ?int $internId = null): array
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $dateStr = $carbon->toDateString();

        // 1. Sunday Week-Off Rule
        if ($carbon->isSunday()) {
            return [
                'allowed' => false,
                'eligible' => false,
                'reason' => 'Daily logbook entries are not available on Sundays (Scheduled Week-Off).',
            ];
        }

        // 2. Official Holiday Rule
        // Check if this date has been marked as a holiday either for this specific intern or across active interns
        $isHoliday = Attendance::whereDate('date', $dateStr)
            ->where('status', 'holiday')
            ->when($internId, function ($query) use ($internId) {
                $query->where(function ($q) use ($internId) {
                    $q->where('intern_id', $internId)
                      ->orWhereNull('intern_id');
                });
            })
            ->exists();

        if ($isHoliday) {
            // Retrieve holiday title/note if available
            $holidayNote = Attendance::whereDate('date', $dateStr)
                ->where('status', 'holiday')
                ->whereNotNull('note')
                ->value('note');

            $reason = 'Daily logbook entries are disabled on official holidays';
            if ($holidayNote) {
                $reason .= " ({$holidayNote})";
            }
            $reason .= '.';

            return [
                'allowed' => false,
                'eligible' => false,
                'reason' => $reason,
            ];
        }

        // 3. Absent Attendance Rule
        $resolvedInternId = $internId ?: (auth()->check() ? auth()->id() : null);
        if ($resolvedInternId) {
            $isAbsent = Attendance::whereDate('date', $dateStr)
                ->where('intern_id', $resolvedInternId)
                ->where('status', 'absent')
                ->exists();

            if ($isAbsent) {
                return [
                    'allowed' => false,
                    'eligible' => false,
                    'reason' => 'Attendance for ' . $carbon->format('d M Y') . ' is marked as Absent. Daily work logbook submission is disabled for absent dates.',
                ];
            }
        }

        return [
            'allowed' => true,
            'eligible' => true,
            'reason' => null,
        ];
    }
}
