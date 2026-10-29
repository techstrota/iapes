<?php

namespace App\Models\ReportManagement;

use App\Models\InternManagement\Intern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollegeEvaluationIntern extends Model
{
    use HasFactory;

    protected $table = 'college_evaluation_interns';

    protected $fillable = [
        'college_evaluation_id',
        'intern_id',
        'custom_student_id',
        'custom_student_name',
        'marks',
        'row_data',
        'total_obtained_marks',
        'grade',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'marks'                => 'array',
        'row_data'             => 'array',
        'total_obtained_marks' => 'decimal:2',
    ];

    public function collegeEvaluation(): BelongsTo
    {
        return $this->belongsTo(CollegeEvaluation::class, 'college_evaluation_id');
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }

    /**
     * Resolves student identifier (custom student ID or system intern code).
     */
    public function getStudentIdAttribute(): string
    {
        if (filled($this->custom_student_id)) {
            return $this->custom_student_id;
        }
        return $this->intern?->intern_code ?: ($this->intern_id ? 'INT-' . str_pad($this->intern_id, 4, '0', STR_PAD_LEFT) : '—');
    }

    /**
     * Resolves student name (custom name or system intern name).
     */
    public function getStudentNameAttribute(): string
    {
        if (filled($this->custom_student_name)) {
            return $this->custom_student_name;
        }
        return $this->intern?->name ?? ($this->intern_id ? 'Intern #' . $this->intern_id : 'Student');
    }

    /**
     * Get a custom column value from row_data.
     */
    public function getCustomValue(string $key, mixed $default = null): mixed
    {
        return $this->row_data[$key] ?? $default;
    }

    /**
     * Set a custom column value in row_data.
     */
    public function setCustomValue(string $key, mixed $value): void
    {
        $data = $this->row_data ?? [];
        $data[$key] = $value;
        $this->row_data = $data;
    }

    /**
     * Formats cell content for any configured column.
     */
    public function getCellValue(string $key, array $colDef = []): string
    {
        $type = $colDef['type'] ?? 'custom_text';

        return match ($type) {
            'student_id'   => $this->student_id,
            'student_name' => $this->student_name,
            'grade'        => strtoupper($this->grade ?: 'PASS'),
            'direct_marks', 'total_marks', 'marks' => rtrim(rtrim(number_format((float)$this->total_obtained_marks, 2), '0'), '.'),
            'criteria_attendance' => rtrim(rtrim(number_format((float)$this->attendance_marks, 2), '0'), '.'),
            'criteria_task'       => rtrim(rtrim(number_format((float)$this->task_marks, 2), '0'), '.'),
            'custom_criterion'    => ($critMark = $this->getCustomMarkByName($colDef['name'] ?? '')) !== null
                                        ? rtrim(rtrim(number_format((float)$critMark, 2), '0'), '.')
                                        : '0',
            default => (string)($this->getCustomValue($key) ?? $colDef['default_value'] ?? ''),
        };
    }

    /**
     * Attendance score accessor.
     */
    public function getAttendanceMarksAttribute(): float
    {
        return (float)($this->marks['attendance_marks'] ?? 0);
    }

    /**
     * Attendance percentage accessor.
     */
    public function getAttendancePctAttribute(): ?float
    {
        return isset($this->marks['attendance_pct']) && $this->marks['attendance_pct'] !== null
            ? (float)$this->marks['attendance_pct']
            : null;
    }

    /**
     * Task score accessor.
     */
    public function getTaskMarksAttribute(): float
    {
        return (float)($this->marks['task_marks'] ?? 0);
    }

    /**
     * Task completion percentage accessor.
     */
    public function getTaskCompletionPctAttribute(): ?float
    {
        return isset($this->marks['task_pct']) && $this->marks['task_pct'] !== null
            ? (float)$this->marks['task_pct']
            : null;
    }

    /**
     * Custom criteria marks list accessor.
     */
    public function getCustomMarksAttribute(): array
    {
        return $this->marks['custom'] ?? [];
    }

    /**
     * Get mark for a specific custom criterion by name.
     */
    public function getCustomMarkByName(string $name): ?float
    {
        $custom = $this->getCustomMarksAttribute();
        foreach ($custom as $item) {
            if (isset($item['name']) && strcasecmp(trim($item['name']), trim($name)) === 0) {
                return (float)($item['marks'] ?? 0);
            }
        }
        return null;
    }

    /**
     * Backward-compatibility accessor for third_component_marks.
     */
    public function getThirdComponentMarksAttribute(): ?float
    {
        $custom = $this->getCustomMarksAttribute();
        return !empty($custom) && isset($custom[0]['marks']) ? (float)$custom[0]['marks'] : null;
    }

    /**
     * Backward-compatibility accessor for third_component_pct.
     */
    public function getThirdComponentPctAttribute(): ?float
    {
        $custom = $this->getCustomMarksAttribute();
        return !empty($custom) && isset($custom[0]['pct']) ? (float)$custom[0]['pct'] : null;
    }
}
