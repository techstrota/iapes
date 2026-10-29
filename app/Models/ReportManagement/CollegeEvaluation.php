<?php

namespace App\Models\ReportManagement;

use App\Models\InternManagement\Intern;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollegeEvaluation extends Model
{
    use HasFactory;

    protected $table = 'college_evaluations';

    protected $fillable = [
        'college_id',
        'batch_title',
        'duration',
        'evaluation_mode',
        'college_name',
        'university_name',
        'academic_year',
        'course_degree',
        'evaluation_date',
        'evaluation_period_start',
        'evaluation_period_end',
        'total_marks',
        'max_attendance_marks',
        'max_task_marks',
        'custom_criteria',
        'columns_config',
        'instruction_notes',
        'is_archived',
        'archived_at',
        'created_by',
    ];

    protected $casts = [
        'evaluation_date'         => 'date',
        'evaluation_period_start' => 'date',
        'evaluation_period_end'   => 'date',
        'total_marks'             => 'decimal:2',
        'max_attendance_marks'    => 'decimal:2',
        'max_task_marks'          => 'decimal:2',
        'custom_criteria'         => 'array',
        'columns_config'          => 'array',
        'is_archived'             => 'boolean',
        'archived_at'             => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function archive(): void
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);
    }

    public function unarchive(): void
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function evaluationInterns(): HasMany
    {
        return $this->hasMany(CollegeEvaluationIntern::class, 'college_evaluation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Checks if this evaluation uses direct marks mode.
     */
    public function isDirectMode(): bool
    {
        return ($this->evaluation_mode ?? 'criteria') === 'direct';
    }

    /**
     * Checks if this evaluation uses criteria breakdown mode.
     */
    public function isCriteriaMode(): bool
    {
        return !$this->isDirectMode();
    }

    /**
     * Returns sanitized column configuration with safe defaults.
     */
    public function getColumnsConfig(): array
    {
        $cfg = is_array($this->columns_config) ? $this->columns_config : [];
        return [
            'columns'            => is_array($cfg['columns'] ?? null) ? $cfg['columns'] : null,
            'student_id_label'   => !empty($cfg['student_id_label']) ? trim($cfg['student_id_label']) : 'Student ID',
            'student_name_label' => !empty($cfg['student_name_label']) ? trim($cfg['student_name_label']) : 'Student Name',
            'show_grade'         => array_key_exists('show_grade', $cfg) ? (bool)$cfg['show_grade'] : true,
            'grade_label'        => !empty($cfg['grade_label']) ? trim($cfg['grade_label']) : 'Grade',
            'marks_label'        => !empty($cfg['marks_label']) ? trim($cfg['marks_label']) : 'Marks',
            'marks_grade_pos'    => !empty($cfg['marks_grade_pos']) ? $cfg['marks_grade_pos'] : ($this->isDirectMode() ? 'grade_then_marks' : 'marks_then_grade'),
            'custom_columns'     => is_array($cfg['custom_columns'] ?? null) ? $cfg['custom_columns'] : [],
        ];
    }

    /**
     * Returns the normalized array of column items, preserving exact order.
     * Backwards-compatible with legacy columns_config configurations.
     */
    public function getNormalizedColumns(): array
    {
        $cfg = is_array($this->columns_config) ? $this->columns_config : [];

        // If new unified columns repeater is present and non-empty, use it directly
        if (isset($cfg['columns']) && is_array($cfg['columns']) && !empty($cfg['columns'])) {
            return $cfg['columns'];
        }

        // Fallback: build normalized array from legacy config
        $cols = [];
        $cols[] = ['type' => 'sr_no', 'label' => 'Sr. No.'];
        $cols[] = [
            'type'  => 'student_id',
            'label' => !empty($cfg['student_id_label']) ? trim($cfg['student_id_label']) : 'Student ID',
        ];
        $cols[] = [
            'type'  => 'student_name',
            'label' => !empty($cfg['student_name_label']) ? trim($cfg['student_name_label']) : 'Student Name',
        ];

        if (!empty($cfg['custom_columns']) && is_array($cfg['custom_columns'])) {
            foreach ($cfg['custom_columns'] as $col) {
                if (is_array($col) && filled($col['label'] ?? null)) {
                    $cols[] = [
                        'type'          => 'custom_text',
                        'label'         => trim((string)$col['label']),
                        'key'           => !empty($col['key']) ? trim((string)$col['key']) : \Illuminate\Support\Str::slug(trim((string)$col['label']), '_'),
                        'default_value' => $col['default_value'] ?? null,
                    ];
                }
            }
        }

        $pos = $cfg['marks_grade_pos'] ?? ($this->isDirectMode() ? 'grade_then_marks' : 'marks_then_grade');
        $showGrade = array_key_exists('show_grade', $cfg) ? (bool)$cfg['show_grade'] : true;
        $gradeCol = ['type' => 'grade', 'label' => !empty($cfg['grade_label']) ? trim($cfg['grade_label']) : 'Grade'];
        $marksCol = ['type' => 'marks', 'label' => !empty($cfg['marks_label']) ? trim($cfg['marks_label']) : 'Marks'];

        if ($this->isDirectMode()) {
            if ($pos === 'grade_then_marks') {
                if ($showGrade) {
                    $cols[] = $gradeCol;
                }
                $cols[] = $marksCol;
            } else {
                $cols[] = $marksCol;
                if ($showGrade) {
                    $cols[] = $gradeCol;
                }
            }
        } else {
            if ((float)$this->max_attendance_marks > 0) {
                $cols[] = ['type' => 'criteria_attendance', 'label' => 'Attendance'];
            }
            if ((float)$this->max_task_marks > 0) {
                $cols[] = ['type' => 'criteria_task', 'label' => 'Task Work'];
            }
            foreach ($this->getCustomCriteriaList() as $crit) {
                $cols[] = ['type' => 'custom_criterion', 'name' => $crit['name'], 'label' => $crit['name']];
            }
            $cols[] = ['type' => 'marks', 'label' => !empty($cfg['marks_label']) ? trim($cfg['marks_label']) : 'Total'];
            if ($showGrade) {
                $cols[] = $gradeCol;
            }
        }

        return $cols;
    }

    /**
     * Returns sanitized list of custom text columns (e.g. Course/Module Name).
     */
    public function getCustomColumnsList(): array
    {
        $rawCols = $this->getNormalizedColumns();
        $list = [];
        $seenKeys = [];

        foreach ($rawCols as $col) {
            $type = $col['type'] ?? 'custom_text';
            if ($type === 'custom_text' && filled($col['label'] ?? null)) {
                $label = trim((string)$col['label']);
                $key = !empty($col['key']) ? trim((string)$col['key']) : \Illuminate\Support\Str::slug($label, '_');
                if (isset($seenKeys[$key])) {
                    $seenKeys[$key]++;
                    $key = $key . '_' . $seenKeys[$key];
                } else {
                    $seenKeys[$key] = 1;
                }
                $list[] = [
                    'key'           => $key,
                    'label'         => $label,
                    'default_value' => $col['default_value'] ?? null,
                ];
            }
        }
        return $list;
    }

    /**
     * Returns the ordered list of all active columns for Table and PDF.
     * The order, labels, and presence of columns strictly follow user configuration.
     */
    public function getActiveColumns(): array
    {
        $rawCols = $this->getNormalizedColumns();
        $totFormatted = rtrim(rtrim(number_format((float)($this->total_marks ?: 100), 2), '0'), '.');
        $attFormatted = rtrim(rtrim(number_format((float)$this->max_attendance_marks, 2), '0'), '.');
        $taskFormatted = rtrim(rtrim(number_format((float)$this->max_task_marks, 2), '0'), '.');

        $cols = [];
        $seenKeys = [];

        foreach ($rawCols as $index => $col) {
            $type = $col['type'] ?? 'custom_text';
            $label = trim((string)($col['label'] ?? ''));
            if ($label === '') {
                $label = match ($type) {
                    'sr_no'               => 'Sr. No.',
                    'student_id'          => 'Student ID',
                    'student_name'        => 'Student Name',
                    'grade'               => 'Grade',
                    'marks', 'direct_marks', 'total_marks' => 'Marks',
                    'criteria_attendance' => 'Attendance',
                    'criteria_task'       => 'Task Work',
                    'custom_criterion'    => $col['name'] ?? 'Criterion',
                    default               => 'Column ' . ($index + 1),
                };
            }

            $baseKey = match ($type) {
                'sr_no'               => 'sr_no',
                'student_id'          => 'student_id',
                'student_name'        => 'student_name',
                'grade'               => 'grade',
                'marks', 'direct_marks', 'total_marks' => 'marks',
                'criteria_attendance' => 'attendance',
                'criteria_task'       => 'task',
                'custom_criterion'    => 'crit_' . \Illuminate\Support\Str::slug($col['name'] ?? $label, '_'),
                default               => !empty($col['key']) ? trim((string)$col['key']) : \Illuminate\Support\Str::slug($label, '_'),
            };

            // Uniqueness check for keys
            $key = $baseKey;
            if (isset($seenKeys[$key])) {
                $seenKeys[$key]++;
                $key = $baseKey . '_' . $seenKeys[$key];
            } else {
                $seenKeys[$key] = 1;
            }

            $align = match ($type) {
                'student_id', 'student_name', 'custom_text' => 'left',
                default => 'center',
            };

            $subLabel = null;
            if (in_array($type, ['marks', 'direct_marks', 'total_marks'])) {
                $subLabel = "(/$totFormatted)";
            } elseif ($type === 'criteria_attendance' && (float)$this->max_attendance_marks > 0) {
                $subLabel = "(/$attFormatted)";
            } elseif ($type === 'criteria_task' && (float)$this->max_task_marks > 0) {
                $subLabel = "(/$taskFormatted)";
            } elseif ($type === 'custom_criterion') {
                $cName = $col['name'] ?? $label;
                foreach ($this->getCustomCriteriaList() as $crit) {
                    if (strcasecmp($crit['name'], $cName) === 0) {
                        $cMaxFormatted = rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.');
                        $subLabel = "(/$cMaxFormatted)";
                        break;
                    }
                }
            }

            $cols[] = [
                'key'           => $key,
                'label'         => $label,
                'sub_label'     => $subLabel,
                'type'          => $type,
                'align'         => $align,
                'default_value' => $col['default_value'] ?? null,
                'name'          => $col['name'] ?? $label,
                'width'         => !empty($col['width']) ? (float)$col['width'] : null,
            ];
        }

        return $cols;
    }

    /**
     * Returns sanitized list of custom criteria from JSON.
     */
    public function getCustomCriteriaList(): array
    {
        if (empty($this->custom_criteria) || !is_array($this->custom_criteria)) {
            return [];
        }

        $list = [];
        foreach ($this->custom_criteria as $item) {
            if (is_array($item) && filled($item['name'] ?? null) && (float)($item['max_marks'] ?? 0) > 0) {
                $list[] = [
                    'name'      => trim($item['name']),
                    'max_marks' => (float)$item['max_marks'],
                ];
            }
        }
        return $list;
    }

    /**
     * Checks if any custom criteria are defined.
     */
    public function hasCustomCriteria(): bool
    {
        return count($this->getCustomCriteriaList()) > 0;
    }

    /**
     * Alias for backward compatibility.
     */
    public function hasThirdComponent(): bool
    {
        return $this->hasCustomCriteria();
    }

    /**
     * Returns all evaluation criteria combined (fixed attendance & task + all custom criteria).
     */
    public function getAllCriteria(): array
    {
        $list = [];

        if ((float)$this->max_attendance_marks > 0) {
            $list[] = [
                'key'       => 'attendance',
                'name'      => 'Attendance',
                'max_marks' => (float)$this->max_attendance_marks,
                'is_fixed'  => true,
            ];
        }

        if ((float)$this->max_task_marks > 0) {
            $list[] = [
                'key'       => 'task',
                'name'      => 'Task Deliverables',
                'max_marks' => (float)$this->max_task_marks,
                'is_fixed'  => true,
            ];
        }

        foreach ($this->getCustomCriteriaList() as $idx => $crit) {
            $list[] = [
                'key'       => 'custom_' . $idx,
                'name'      => $crit['name'],
                'max_marks' => (float)$crit['max_marks'],
                'is_fixed'  => false,
            ];
        }

        return $list;
    }

    /**
     * Helper to compute Grade from percentage score.
     */
    public static function computeGrade(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'O'; // Outstanding (>= 90%)
        }
        if ($percentage >= 70) {
            return 'A'; // Excellent / Very Good (70% - 89%, e.g. 41, 43 out of 50)
        }
        if ($percentage >= 60) {
            return 'B+'; // Good (60% - 69%)
        }
        if ($percentage >= 50) {
            return 'B'; // Satisfactory (50% - 59%)
        }
        if ($percentage >= 40) {
            return 'C'; // Pass (40% - 49%)
        }
        return 'Needs Improvement';
    }

    /**
     * Builds default instructions given marks, custom criteria, and evaluation mode.
     */
    public static function buildDefaultInstructions(
        float|int|string|null $attMax = 20,
        float|int|string|null $taskMax = 40,
        float|int|string|null $totalMax = 100,
        array $customCriteria = [],
        string $mode = 'criteria'
    ): string {
        $totalFormatted = rtrim(rtrim(number_format((float)($totalMax ?: 100), 2), '0'), '.');

        if ($mode === 'direct') {
            $lines = [];
            $lines[] = "1. Evaluation Scheme (Max {$totalFormatted} Marks):";
            $lines[] = "Overall student evaluation is assessed directly based on assigned coursework, project modules, technical deliverables, practical proficiency, and periodic mentor reviews out of {$totalFormatted} marks.";
            $lines[] = "";
            $lines[] = "2. Grading Scale:";
            $lines[] = "Grades are assigned based on percentage achieved out of {$totalFormatted} marks:";
            $lines[] = "90–100%: O (Outstanding) | 80–89%: A+ (Excellent) | 70–79%: A (Very Good) | 60–69%: B+ (Good) | 50–59%: B (Satisfactory) | 40–49%: Pass | Below 40%: Needs Improvement.";
            return implode("\n", $lines);
        }

        $attVal = (float)($attMax ?? 0);
        $taskVal = (float)($taskMax ?? 0);
        $attFormatted = rtrim(rtrim(number_format($attVal, 2), '0'), '.');
        $taskFormatted = rtrim(rtrim(number_format($taskVal, 2), '0'), '.');

        $lines = [];
        $step = 1;

        if ($attVal > 0) {
            $lines[] = "{$step}. Attendance & Punctuality (Max {$attFormatted} Marks):";
            $lines[] = "Calculated from daily portal records, check-ins, and scheduled working sessions. Unexcused absences and late check-ins deduct credit accordingly.";
            $lines[] = "";
            $step++;
        }

        if ($taskVal > 0) {
            $lines[] = "{$step}. Task Allocation & Deliverables (Max {$taskFormatted} Marks):";
            $lines[] = "Evaluated based on sprint tasks assigned, on-time technical deliverables, repository commits, and mentor submission evaluations.";
            $lines[] = "";
            $step++;
        }

        if (is_array($customCriteria)) {
            foreach ($customCriteria as $crit) {
                if (!is_array($crit) || empty($crit['name']) || (float)($crit['max_marks'] ?? 0) <= 0) {
                    continue;
                }
                $cName = trim((string)$crit['name']);
                $cMax = rtrim(rtrim(number_format((float)($crit['max_marks'] ?? 0), 2), '0'), '.');
                $lower = strtolower($cName);

                $desc = match (true) {
                    str_contains($lower, 'viva') || str_contains($lower, 'oral') || str_contains($lower, 'presentation') =>
                        "Evaluated based on conceptual clarity, technical defense, presentation delivery, and mentor/faculty viva questions.",
                    str_contains($lower, 'report') || str_contains($lower, 'logbook') || str_contains($lower, 'documentation') =>
                        "Continuous assessment of periodic progress reports, documentation quality, repository commits, and submitted logbooks.",
                    str_contains($lower, 'discipline') || str_contains($lower, 'conduct') || str_contains($lower, 'attitude') =>
                        "Evaluated on professional workplace conduct, timeliness, peer collaboration, and adherence to company code of ethics.",
                    str_contains($lower, 'project') || str_contains($lower, 'module') || str_contains($lower, 'code') =>
                        "Evaluated on codebase architecture, project milestone completion, bug fixes, and live deployment demonstration.",
                    default =>
                        "Continuous evaluation based on {$cName} performance, technical quality, practical assignments, and periodic mentor reviews.",
                };

                $lines[] = "{$step}. {$cName} (Max {$cMax} Marks):";
                $lines[] = $desc;
                $lines[] = "";
                $step++;
            }
        }

        $lines[] = "{$step}. Total Marks & Grading Scale (Max {$totalFormatted} Marks):";
        $lines[] = "Total Obtained Marks is the sum of all evaluated criteria. Grades: 90-100%: O (Outstanding) | 80-89%: A+ (Excellent) | 70-79%: A (Very Good) | 60-69%: B+ (Good) | 50-59%: B (Satisfactory) | 40-49%: C (Pass) | Below 40%: Needs Improvement.";

        return implode("\n", $lines);
    }

    /**
     * Generates default instructions formatted for PDF footer and previews.
     */
    public function generateDefaultInstructions(): string
    {
        return static::buildDefaultInstructions(
            $this->max_attendance_marks,
            $this->max_task_marks,
            $this->total_marks,
            $this->getCustomCriteriaList(),
            $this->evaluation_mode ?? 'criteria'
        );
    }

    /**
     * Automatically calculates marks for a single enrolled intern.
     */
    public function calculateIntern(CollegeEvaluationIntern $evalIntern): void
    {
        // In Direct Marks Mode, marks are entered directly (out of total_marks)
        if ($this->isDirectMode()) {
            $totalMax = (float)$this->total_marks > 0 ? (float)$this->total_marks : 50.0;
            $obtained = (float)($evalIntern->total_obtained_marks ?? 0);
            $pct = min(100.0, max(0.0, round(($obtained / $totalMax) * 100, 2)));
            $grade = (filled($evalIntern->grade) && strtoupper(trim($evalIntern->grade)) !== 'PASS')
                ? $evalIntern->grade
                : self::computeGrade($pct);

            $evalIntern->update([
                'total_obtained_marks' => $obtained,
                'grade'                => $grade,
            ]);
            return;
        }

        $intern = $evalIntern->intern;
        if (!$intern) {
            return;
        }

        $startDate = $this->evaluation_period_start ? Carbon::parse($this->evaluation_period_start)->startOfDay() : null;
        $endDate = $this->evaluation_period_end ? Carbon::parse($this->evaluation_period_end)->endOfDay() : null;

        // ── 1. Attendance Calculation ──
        $attQuery = $intern->attendances();
        if ($startDate && $endDate) {
            $attQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        } elseif ($startDate) {
            $attQuery->where('date', '>=', $startDate->toDateString());
        } elseif ($endDate) {
            $attQuery->where('date', '<=', $endDate->toDateString());
        }

        $attendances = $attQuery->get();
        $present = $attendances->where('status', 'present')->count();
        $wfh = $attendances->where('status', 'wfh')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $leave = $attendances->where('status', 'leave')->count();

        $workingDays = $present + $wfh + $late + $absent + $leave;
        $attendedDays = $present + $wfh + ($late * 0.8);

        $attendancePct = $workingDays > 0 ? min(100.0, round(($attendedDays / $workingDays) * 100, 2)) : 100.0;
        $maxAtt = (float)$this->max_attendance_marks;
        $attendanceMarks = round(($attendancePct / 100.0) * $maxAtt, 2);

        // ── 2. Task Performance Calculation ──
        $taskStats = $intern->getTaskStats();
        $totalAssigned = $taskStats['total'] ?? 0;
        $completed = $taskStats['completed'] ?? 0;

        $taskCompPct = $totalAssigned > 0 ? min(100.0, round(($completed / $totalAssigned) * 100, 2)) : 100.0;

        $submissions = $intern->submissions ?? collect();
        $gradedSubs = $submissions->whereNotNull('marks')->where('marks', '>', 0);
        if ($gradedSubs->count() > 0) {
            $avgMarks = $gradedSubs->avg('marks');
            $avgMarksPct = $avgMarks <= 10 ? ($avgMarks * 10) : min(100.0, $avgMarks);
            $taskScorePct = round(($taskCompPct * 0.5) + ($avgMarksPct * 0.5), 2);
        } else {
            $taskScorePct = $taskCompPct;
        }

        $maxTask = (float)$this->max_task_marks;
        $taskMarks = round(($taskScorePct / 100.0) * $maxTask, 2);

        // ── 3. Dynamic Custom Criteria Calculations ──
        $existingMarks = $evalIntern->marks ?? [];
        $existingCustom = $existingMarks['custom'] ?? [];
        $existingCustomMap = [];

        if (is_array($existingCustom)) {
            foreach ($existingCustom as $ec) {
                if (isset($ec['name'])) {
                    $existingCustomMap[$ec['name']] = $ec;
                }
            }
        }

        // Base engagement percentage for defaulting custom components
        $baseEngagementPct = round(($attendancePct * 0.4) + ($taskScorePct * 0.6), 2);

        $calculatedCustom = [];
        $customMarksSum = 0;

        foreach ($this->getCustomCriteriaList() as $crit) {
            $cName = $crit['name'];
            $cMax = (float)$crit['max_marks'];

            // If manual score already entered for this criterion, preserve it
            if (isset($existingCustomMap[$cName]['marks']) && is_numeric($existingCustomMap[$cName]['marks'])) {
                $cMarks = min($cMax, (float)$existingCustomMap[$cName]['marks']);
                $cPct = $cMax > 0 ? round(($cMarks / $cMax) * 100, 2) : 100.0;
            } else {
                // Check if this is a report / logbook component
                $lowerName = strtolower($cName);
                if (str_contains($lowerName, 'report') || str_contains($lowerName, 'logbook')) {
                    $logbooksQuery = $intern->logbooks();
                    if ($startDate && $endDate) {
                        $logbooksQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    }
                    $logbookCount = $logbooksQuery->count();
                    $expectedLogs = $workingDays > 0 ? max(1, $workingDays) : 20;
                    $logbookPct = min(100.0, round(($logbookCount / $expectedLogs) * 100, 2));

                    // Factor in progress report ratings if present
                    $progInterns = ProgressReportIntern::where('intern_id', $intern->id)->where('is_evaluated', true)->get();
                    if ($progInterns->count() > 0) {
                        $ratings = [];
                        foreach ($progInterns as $pi) {
                            if (is_array($pi->criteria_ratings)) {
                                foreach ($pi->criteria_ratings as $r) {
                                    if (isset($r['rating']) && is_numeric($r['rating'])) {
                                        $ratings[] = (float)$r['rating'];
                                    }
                                }
                            }
                        }
                        if (!empty($ratings)) {
                            $avgRating = array_sum($ratings) / count($ratings);
                            $ratingPct = ($avgRating / 5.0) * 100;
                            $cPct = round(($logbookPct * 0.5) + ($ratingPct * 0.5), 2);
                        } else {
                            $cPct = $logbookPct;
                        }
                    } else {
                        $cPct = $logbookPct;
                    }
                } else {
                    // For viva / presentation / discipline, default to base engagement score
                    $cPct = $baseEngagementPct;
                }

                $cMarks = round(($cPct / 100.0) * $cMax, 2);
            }

            $customMarksSum += $cMarks;
            $calculatedCustom[] = [
                'name'      => $cName,
                'max_marks' => $cMax,
                'marks'     => $cMarks,
                'pct'       => $cPct,
            ];
        }

        // ── 4. Total & Grade ──
        $totalObtained = round($attendanceMarks + $taskMarks + $customMarksSum, 2);
        $totalMax = (float)$this->total_marks > 0 ? (float)$this->total_marks : 100.0;
        $overallPct = min(100.0, round(($totalObtained / $totalMax) * 100, 2));
        $grade = self::computeGrade($overallPct);

        $marksPayload = [
            'attendance_marks' => $attendanceMarks,
            'attendance_pct'   => $attendancePct,
            'task_marks'       => $taskMarks,
            'task_pct'         => $taskScorePct,
            'custom'           => $calculatedCustom,
        ];

        $evalIntern->update([
            'marks'                => $marksPayload,
            'total_obtained_marks' => $totalObtained,
            'grade'                => $grade,
        ]);
    }
}
