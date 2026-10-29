<?php

namespace App\Filament\Resources\CollegeEvaluationResource\Pages;

use App\Filament\Resources\CollegeEvaluationResource;
use App\Models\ReportManagement\CollegeEvaluation;
use App\Models\ReportManagement\CollegeEvaluationIntern;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCollegeEvaluation extends ViewRecord
{
    protected static string $resource = CollegeEvaluationResource::class;

    protected static string $view = 'filament.college-evaluation.view-college-evaluation';

    // ── Edit Row State ──
    public bool $showEditModal = false;
    public ?int $editingInternId = null;
    public ?string $editStudentId = null;
    public ?string $editStudentName = null;
    public ?float $editTotalMarks = null;
    public ?float $editAttendanceMarks = null;
    public ?float $editTaskMarks = null;
    public array $editCustomMarks = [];
    public array $editCustomFields = [];
    public ?string $editGrade = null;
    public ?string $editRemarks = null;

    // ── Add New Student Row State ──
    public bool $showAddModal = false;
    public ?string $newStudentId = null;
    public ?string $newStudentName = null;
    public ?float $newTotalMarks = null;
    public ?float $newAttendanceMarks = null;
    public ?float $newTaskMarks = null;
    public array $newCustomMarks = [];
    public array $newCustomFields = [];
    public ?string $newGrade = null;
    public ?string $newRemarks = null;

    // ── Live PDF Preview & Adjust Modal State ──
    public bool $showPdfPreviewModal = false;
    public string $previewViewMode = 'interactive'; // 'interactive' or 'compiled_pdf'
    public string $previewFontSize = 'normal';
    public string $previewSpacing = 'normal';
    public array $columnWidths = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $settings = $this->record->columns_config['pdf_settings'] ?? [];
        $this->previewFontSize = $settings['font_size'] ?? 'normal';
        $this->previewSpacing = $settings['spacing'] ?? 'normal';
        $this->initColumnWidths();
    }

    public function openPdfPreviewModal(): void
    {
        $settings = $this->record->columns_config['pdf_settings'] ?? [];
        $this->previewFontSize = $settings['font_size'] ?? 'normal';
        $this->previewSpacing = $settings['spacing'] ?? 'normal';
        $this->initColumnWidths();
        $this->showPdfPreviewModal = true;
    }

    public function closePdfPreviewModal(): void
    {
        $this->showPdfPreviewModal = false;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingInternId = null;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function setPreviewViewMode(string $mode): void
    {
        $this->previewViewMode = in_array($mode, ['interactive', 'compiled_pdf'], true) ? $mode : 'interactive';
    }

    public function updatedPreviewFontSize($value): void
    {
        $this->persistPdfSettings();
    }

    public function updatedPreviewSpacing($value): void
    {
        $this->persistPdfSettings();
    }

    public function updateColumnWidths(array $widths): void
    {
        foreach ($widths as $key => $val) {
            if (isset($this->columnWidths[$key]) && is_numeric($val)) {
                $this->columnWidths[$key] = round((float)$val, 1);
            }
        }
        $this->persistPdfSettings();
    }

    public function initColumnWidths(): void
    {
        $activeCols = $this->record->getActiveColumns();
        $savedSettings = $this->record->columns_config['pdf_settings']['col_widths'] ?? [];
        $colMap = [];

        foreach ($activeCols as $c) {
            $key = $c['key'];
            if (!empty($savedSettings[$key]) && is_numeric($savedSettings[$key])) {
                $colMap[$key] = (float)$savedSettings[$key];
            } elseif (!empty($c['width']) && is_numeric($c['width'])) {
                $colMap[$key] = (float)$c['width'];
            } else {
                $t = $c['type'] ?? 'custom_text';
                $colMap[$key] = match ($t) {
                    'sr_no'        => 6.0,
                    'student_id'   => 17.0,
                    'student_name' => 24.0,
                    'grade'        => 10.0,
                    'direct_marks', 'total_marks', 'marks' => 10.5,
                    'criteria_attendance', 'criteria_task', 'custom_criterion' => 10.0,
                    default        => 32.5,
                };
            }
        }

        // Normalize sum to 100%
        $sum = max(1.0, array_sum($colMap));
        $normalized = [];
        foreach ($colMap as $k => $w) {
            $normalized[$k] = round(($w / $sum) * 100, 1);
        }
        $this->columnWidths = $normalized;
    }

    public function adjustColumnWidth(string $colKey, float $delta): void
    {
        if (!isset($this->columnWidths[$colKey])) {
            return;
        }

        $current = (float)$this->columnWidths[$colKey];
        $new = max(4.0, min(80.0, round($current + $delta, 1)));
        $this->columnWidths[$colKey] = $new;
        $this->persistPdfSettings();
    }

    public function rebalanceWidths(): void
    {
        $total = array_sum($this->columnWidths);
        if ($total <= 0) return;

        $factor = 100 / $total;
        foreach ($this->columnWidths as $k => $w) {
            $this->columnWidths[$k] = round($w * $factor, 1);
        }
    }

    public function autoFitColumnWidths(): void
    {
        $activeCols = $this->record->getActiveColumns();
        $interns = $this->record->evaluationInterns;
        $scores = [];

        foreach ($activeCols as $c) {
            $k = $c['key'];
            $maxLen = mb_strlen($c['label']);
            foreach ($interns as $item) {
                $val = $item->getCellValue($k, $c);
                $maxLen = max($maxLen, mb_strlen((string)$val));
            }

            $scores[$k] = match ($c['type']) {
                'sr_no'        => 5.0,
                'grade'        => 9.0,
                'direct_marks', 'total_marks', 'marks' => 10.5,
                'student_id'   => max(15.0, min(22.0, $maxLen * 1.4)),
                'student_name' => max(22.0, min(35.0, $maxLen * 1.1)),
                default        => max(25.0, min(45.0, $maxLen * 1.3)),
            };
        }

        $sum = max(1.0, array_sum($scores));
        foreach ($scores as $k => $s) {
            $this->columnWidths[$k] = round(($s / $sum) * 100, 1);
        }
        $this->persistPdfSettings();

        Notification::make()
            ->title('Column Widths Auto-Fitted & Saved')
            ->body('Widths balanced based on content lengths and saved to database.')
            ->info()
            ->send();
    }

    public function resetColumnWidths(): void
    {
        $cfg = $this->record->columns_config ?? [];
        if (isset($cfg['pdf_settings']['col_widths'])) {
            unset($cfg['pdf_settings']['col_widths']);
            $this->record->update(['columns_config' => $cfg]);
        }
        $this->initColumnWidths();
        $this->persistPdfSettings();

        Notification::make()
            ->title('Widths Reset & Saved')
            ->body('Columns reset to default balanced layout.')
            ->info()
            ->send();
    }

    public function persistPdfSettings(): void
    {
        $this->rebalanceWidths();

        $cfg = $this->record->columns_config ?? [];
        $cfg['pdf_settings'] = [
            'font_size'  => $this->previewFontSize,
            'spacing'    => $this->previewSpacing,
            'col_widths' => $this->columnWidths,
        ];

        // Also sync width back into columns_config.columns repeater
        if (isset($cfg['columns']) && is_array($cfg['columns'])) {
            foreach ($cfg['columns'] as &$colItem) {
                $key = $colItem['key'] ?? \Illuminate\Support\Str::slug($colItem['label'] ?? '', '_');
                if (isset($this->columnWidths[$key])) {
                    $colItem['width'] = (float)$this->columnWidths[$key];
                }
            }
        }

        $this->record->update(['columns_config' => $cfg]);
        $this->record->refresh();
    }

    public function savePdfSettings(): void
    {
        $this->persistPdfSettings();

        Notification::make()
            ->title('PDF & Table Layout Saved')
            ->body('Column widths, font size, and spacing saved across all PDF reports.')
            ->success()
            ->send();
    }

    public function saveSingleCellEdit(int $internId, string $colKey, string $value): void
    {
        $evalIntern = CollegeEvaluationIntern::find($internId);
        if (!$evalIntern || $evalIntern->college_evaluation_id !== $this->record->id) {
            return;
        }

        $cleanVal = trim($value);
        if ($colKey === 'student_id') {
            $evalIntern->custom_student_id = $cleanVal;
        } elseif ($colKey === 'student_name') {
            $evalIntern->custom_student_name = $cleanVal;
        } elseif (in_array($colKey, ['marks', 'total_marks', 'direct_marks'])) {
            $evalIntern->total_obtained_marks = (float)$cleanVal;
            if ($this->record->isDirectMode()) {
                $totalMax = (float)$this->record->total_marks > 0 ? (float)$this->record->total_marks : 50.0;
                $pct = min(100.0, max(0.0, round(((float)$cleanVal / $totalMax) * 100, 2)));
                $evalIntern->grade = CollegeEvaluation::computeGrade($pct);
            }
        } elseif ($colKey === 'grade') {
            $evalIntern->grade = strtoupper($cleanVal);
        } else {
            $evalIntern->setCustomValue($colKey, $cleanVal);
        }
        $evalIntern->save();
        $this->record->refresh();
    }

    public function saveInlineCellEdits(array $cellData): void
    {
        foreach ($cellData as $internId => $fields) {
            $evalIntern = CollegeEvaluationIntern::find($internId);
            if ($evalIntern && $evalIntern->college_evaluation_id === $this->record->id) {
                foreach ($fields as $key => $val) {
                    $cleanVal = trim((string)$val);
                    if ($key === 'student_id') {
                        $evalIntern->custom_student_id = $cleanVal;
                    } elseif ($key === 'student_name') {
                        $evalIntern->custom_student_name = $cleanVal;
                    } elseif (in_array($key, ['marks', 'total_marks', 'direct_marks'])) {
                        $evalIntern->total_obtained_marks = (float)$cleanVal;
                        if ($this->record->isDirectMode()) {
                            $totalMax = (float)$this->record->total_marks > 0 ? (float)$this->record->total_marks : 50.0;
                            $pct = min(100.0, max(0.0, round(((float)$cleanVal / $totalMax) * 100, 2)));
                            $evalIntern->grade = CollegeEvaluation::computeGrade($pct);
                        }
                    } elseif ($key === 'grade') {
                        $evalIntern->grade = strtoupper($cleanVal);
                    } else {
                        $evalIntern->setCustomValue($key, $cleanVal);
                    }
                }
                $evalIntern->save();
            }
        }

        $this->record->refresh();

        Notification::make()
            ->title('Changes Saved')
            ->body('Edited cell content updated successfully across entire report.')
            ->success()
            ->send();
    }

    public function updatedEditTotalMarks($value): void
    {
        $totalMax = (float)$this->record->total_marks > 0 ? (float)$this->record->total_marks : 50.0;
        $marks = (float)($value ?? 0);
        $pct = min(100.0, max(0.0, round(($marks / $totalMax) * 100, 2)));
        $this->editGrade = CollegeEvaluation::computeGrade($pct);
    }

    public function updatedNewTotalMarks($value): void
    {
        $totalMax = (float)$this->record->total_marks > 0 ? (float)$this->record->total_marks : 50.0;
        $marks = (float)($value ?? 0);
        $pct = min(100.0, max(0.0, round(($marks / $totalMax) * 100, 2)));
        $this->newGrade = CollegeEvaluation::computeGrade($pct);
    }

    public function getTitle(): string
    {
        return $this->record->college_name . ' — Evaluation Report';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function downloadPdf()
    {
        $this->persistPdfSettings();
        return CollegeEvaluationResource::downloadPdf($this->record);
    }

    public function recalculateAll(): void
    {
        $evaluation = $this->record;
        $count = 0;
        foreach ($evaluation->evaluationInterns as $evalIntern) {
            $evaluation->calculateIntern($evalIntern);
            $count++;
        }

        Notification::make()
            ->title('Marks Recalculated')
            ->body("Successfully re-computed marks for {$count} intern(s).")
            ->success()
            ->send();
    }

    /**
     * Opens modal to edit an existing student row.
     */
    public function openEditInternModal(int $evalInternId): void
    {
        $evalIntern = CollegeEvaluationIntern::find($evalInternId);
        if (!$evalIntern) {
            return;
        }

        $evaluation = $this->record;
        $this->editingInternId = $evalIntern->id;
        $this->editStudentId = $evalIntern->student_id;
        $this->editStudentName = $evalIntern->student_name;
        $this->editTotalMarks = (float)$evalIntern->total_obtained_marks;
        $this->editAttendanceMarks = (float)$evalIntern->attendance_marks;
        $this->editTaskMarks = (float)$evalIntern->task_marks;
        $this->editRemarks = $evalIntern->remarks;

        if ($evaluation->isDirectMode()) {
            $totalMax = (float)$evaluation->total_marks > 0 ? (float)$evaluation->total_marks : 50.0;
            $pct = min(100.0, max(0.0, round(($this->editTotalMarks / $totalMax) * 100, 2)));
            $this->editGrade = (filled($evalIntern->grade) && strtoupper(trim($evalIntern->grade)) !== 'PASS')
                ? $evalIntern->grade
                : CollegeEvaluation::computeGrade($pct);
        } else {
            $this->editGrade = $evalIntern->grade;
        }

        // Custom criteria marks
        $this->editCustomMarks = [];
        foreach ($evaluation->getCustomCriteriaList() as $crit) {
            $cName = $crit['name'];
            $mark = $evalIntern->getCustomMarkByName($cName);
            $this->editCustomMarks[$cName] = $mark !== null ? (float)$mark : 0.0;
        }

        // Custom text columns (e.g. Course Name/ Module Name)
        $this->editCustomFields = [];
        foreach ($evaluation->getCustomColumnsList() as $col) {
            $key = $col['key'];
            $this->editCustomFields[$key] = $evalIntern->getCustomValue($key, $col['default_value'] ?? '');
        }

        $this->showEditModal = true;
    }

    /**
     * Saves changes to an existing student row.
     */
    public function saveInternMarks(): void
    {
        if (!$this->editingInternId) {
            return;
        }

        $evalIntern = CollegeEvaluationIntern::find($this->editingInternId);
        if (!$evalIntern) {
            return;
        }

        $evaluation = $this->record;

        if ($evaluation->isDirectMode()) {
            $totalMax = (float)$evaluation->total_marks > 0 ? (float)$evaluation->total_marks : 50.0;
            $totalObtained = max(0.0, (float)($this->editTotalMarks ?? 0));
            $pct = min(100.0, max(0.0, round(($totalObtained / $totalMax) * 100, 2)));
            $autoGrade = CollegeEvaluation::computeGrade($pct);
            $grade = (filled($this->editGrade) && strtoupper(trim($this->editGrade)) !== 'PASS')
                ? trim((string)$this->editGrade)
                : $autoGrade;

            $evalIntern->update([
                'custom_student_id'   => $this->editStudentId,
                'custom_student_name' => $this->editStudentName,
                'total_obtained_marks'=> $totalObtained,
                'grade'               => $grade,
                'row_data'            => $this->editCustomFields,
                'remarks'             => $this->editRemarks,
            ]);
        } else {
            // Criteria mode
            $maxAtt = (float)$evaluation->max_attendance_marks;
            $maxTask = (float)$evaluation->max_task_marks;

            $att = min($maxAtt, max(0.0, (float)($this->editAttendanceMarks ?? 0)));
            $task = min($maxTask, max(0.0, (float)($this->editTaskMarks ?? 0)));

            $attPct = $maxAtt > 0 ? round(($att / $maxAtt) * 100, 2) : 100.0;
            $taskPct = $maxTask > 0 ? round(($task / $maxTask) * 100, 2) : 100.0;

            $customMarksArray = [];
            $customMarksSum = 0.0;

            foreach ($evaluation->getCustomCriteriaList() as $crit) {
                $cName = $crit['name'];
                $cMax = (float)$crit['max_marks'];
                $entered = isset($this->editCustomMarks[$cName]) && is_numeric($this->editCustomMarks[$cName])
                    ? (float)$this->editCustomMarks[$cName]
                    : 0.0;
                $cMarks = min($cMax, max(0.0, $entered));
                $cPct = $cMax > 0 ? round(($cMarks / $cMax) * 100, 2) : 100.0;
                $customMarksSum += $cMarks;

                $customMarksArray[] = [
                    'name'      => $cName,
                    'max_marks' => $cMax,
                    'marks'     => $cMarks,
                    'pct'       => $cPct,
                ];
            }

            $totalObtained = round($att + $task + $customMarksSum, 2);
            $totalMax = (float)$evaluation->total_marks > 0 ? (float)$evaluation->total_marks : 100.0;
            $pct = min(100.0, round(($totalObtained / $totalMax) * 100, 2));
            $grade = filled($this->editGrade) ? trim((string)$this->editGrade) : CollegeEvaluation::computeGrade($pct);

            $marksPayload = [
                'attendance_marks' => $att,
                'attendance_pct'   => $attPct,
                'task_marks'       => $task,
                'task_pct'         => $taskPct,
                'custom'           => $customMarksArray,
            ];

            $evalIntern->update([
                'custom_student_id'   => $this->editStudentId,
                'custom_student_name' => $this->editStudentName,
                'marks'               => $marksPayload,
                'total_obtained_marks'=> $totalObtained,
                'grade'               => $grade,
                'row_data'            => $this->editCustomFields,
                'remarks'             => $this->editRemarks,
            ]);
        }

        $this->showEditModal = false;
        $this->editingInternId = null;

        Notification::make()
            ->title('Student Evaluation Updated')
            ->body('Scores and row data updated successfully.')
            ->success()
            ->send();
    }

    /**
     * Opens modal to add a brand-new student row.
     */
    public function openAddStudentModal(): void
    {
        $evaluation = $this->record;

        $this->newStudentId = '';
        $this->newStudentName = '';
        $this->newTotalMarks = null;
        $this->newAttendanceMarks = (float)$evaluation->max_attendance_marks;
        $this->newTaskMarks = (float)$evaluation->max_task_marks;
        $this->newGrade = '';
        $this->newRemarks = '';

        $this->newCustomMarks = [];
        foreach ($evaluation->getCustomCriteriaList() as $crit) {
            $this->newCustomMarks[$crit['name']] = (float)$crit['max_marks'];
        }

        $this->newCustomFields = [];
        foreach ($evaluation->getCustomColumnsList() as $col) {
            $this->newCustomFields[$col['key']] = $col['default_value'] ?? '';
        }

        $this->showAddModal = true;
    }

    /**
     * Saves a brand-new student row.
     */
    public function saveNewStudentRow(): void
    {
        $evaluation = $this->record;

        if (blank($this->newStudentName) && blank($this->newStudentId)) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please enter at least Student ID or Student Name.')
                ->danger()
                ->send();
            return;
        }

        if ($evaluation->isDirectMode()) {
            $totalMax = (float)$evaluation->total_marks > 0 ? (float)$evaluation->total_marks : 50.0;
            $totalObtained = max(0.0, (float)($this->newTotalMarks ?? 0));
            $pct = min(100.0, max(0.0, round(($totalObtained / $totalMax) * 100, 2)));
            $autoGrade = CollegeEvaluation::computeGrade($pct);
            $grade = (filled($this->newGrade) && strtoupper(trim($this->newGrade)) !== 'PASS')
                ? trim((string)$this->newGrade)
                : $autoGrade;

            CollegeEvaluationIntern::create([
                'college_evaluation_id' => $evaluation->id,
                'intern_id'             => null,
                'custom_student_id'     => $this->newStudentId,
                'custom_student_name'   => $this->newStudentName,
                'total_obtained_marks'  => $totalObtained,
                'grade'                 => $grade,
                'row_data'              => $this->newCustomFields,
                'remarks'               => $this->newRemarks,
            ]);
        } else {
            $maxAtt = (float)$evaluation->max_attendance_marks;
            $maxTask = (float)$evaluation->max_task_marks;

            $att = min($maxAtt, max(0.0, (float)($this->newAttendanceMarks ?? 0)));
            $task = min($maxTask, max(0.0, (float)($this->newTaskMarks ?? 0)));

            $attPct = $maxAtt > 0 ? round(($att / $maxAtt) * 100, 2) : 100.0;
            $taskPct = $maxTask > 0 ? round(($task / $maxTask) * 100, 2) : 100.0;

            $customMarksArray = [];
            $customMarksSum = 0.0;

            foreach ($evaluation->getCustomCriteriaList() as $crit) {
                $cName = $crit['name'];
                $cMax = (float)$crit['max_marks'];
                $entered = isset($this->newCustomMarks[$cName]) && is_numeric($this->newCustomMarks[$cName])
                    ? (float)$this->newCustomMarks[$cName]
                    : 0.0;
                $cMarks = min($cMax, max(0.0, $entered));
                $cPct = $cMax > 0 ? round(($cMarks / $cMax) * 100, 2) : 100.0;
                $customMarksSum += $cMarks;

                $customMarksArray[] = [
                    'name'      => $cName,
                    'max_marks' => $cMax,
                    'marks'     => $cMarks,
                    'pct'       => $cPct,
                ];
            }

            $totalObtained = round($att + $task + $customMarksSum, 2);
            $totalMax = (float)$evaluation->total_marks > 0 ? (float)$evaluation->total_marks : 100.0;
            $pct = min(100.0, round(($totalObtained / $totalMax) * 100, 2));
            $grade = filled($this->newGrade) ? trim((string)$this->newGrade) : CollegeEvaluation::computeGrade($pct);

            $marksPayload = [
                'attendance_marks' => $att,
                'attendance_pct'   => $attPct,
                'task_marks'       => $task,
                'task_pct'         => $taskPct,
                'custom'           => $customMarksArray,
            ];

            CollegeEvaluationIntern::create([
                'college_evaluation_id' => $evaluation->id,
                'intern_id'             => null,
                'custom_student_id'     => $this->newStudentId,
                'custom_student_name'   => $this->newStudentName,
                'marks'                 => $marksPayload,
                'total_obtained_marks'  => $totalObtained,
                'grade'                 => $grade,
                'row_data'              => $this->newCustomFields,
                'remarks'               => $this->newRemarks,
            ]);
        }

        $this->showAddModal = false;

        Notification::make()
            ->title('New Student Row Added')
            ->body('Student evaluation row added to roster.')
            ->success()
            ->send();
    }

    /**
     * Removes an intern row from the evaluation roster.
     */
    public function deleteInternRow(int $evalInternId): void
    {
        $evalIntern = CollegeEvaluationIntern::find($evalInternId);
        if ($evalIntern && $evalIntern->college_evaluation_id === $this->record->id) {
            $evalIntern->delete();
            Notification::make()
                ->title('Row Removed')
                ->body('Student row has been removed from this evaluation.')
                ->success()
                ->send();
        }
    }
}
