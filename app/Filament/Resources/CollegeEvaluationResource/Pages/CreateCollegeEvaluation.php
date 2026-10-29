<?php

namespace App\Filament\Resources\CollegeEvaluationResource\Pages;

use App\Filament\Resources\CollegeEvaluationResource;
use App\Models\ReportManagement\CollegeEvaluationIntern;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCollegeEvaluation extends CreateRecord
{
    protected static string $resource = CollegeEvaluationResource::class;

    public function getTitle(): string
    {
        $collegeId = request()->query('college_id');
        if ($collegeId && $college = \App\Models\ReportManagement\College::find($collegeId)) {
            return 'Create Batch for ' . $college->name;
        }
        return 'Create College Evaluation Batch';
    }

    public function mount(): void
    {
        parent::mount();

        $collegeId = request()->query('college_id');
        $college = $collegeId ? \App\Models\ReportManagement\College::find($collegeId) : null;

        $customCriteria = [
            ['name' => 'Weekly / Monthly Report', 'max_marks' => 40],
        ];

        $defaultInstructions = \App\Models\ReportManagement\CollegeEvaluation::buildDefaultInstructions(20, 40, 50, [], 'direct');

        $defaultColumns = [
            ['type' => 'sr_no',        'label' => 'Sr. No.'],
            ['type' => 'student_id',   'label' => 'Student ID'],
            ['type' => 'student_name', 'label' => 'Student Name'],
            ['type' => 'custom_text',  'label' => 'Course Name/ Module Name', 'default_value' => ''],
            ['type' => 'grade',        'label' => 'Grade'],
            ['type' => 'marks',        'label' => 'Marks'],
        ];

        $fillData = [
            'evaluation_mode'           => 'direct',
            'total_marks'               => 50,
            'max_attendance_marks'      => 20,
            'max_task_marks'            => 40,
            'custom_criteria'           => $customCriteria,
            'columns_config'            => [
                'columns' => $defaultColumns,
            ],
            'duration'                  => '1 Month',
            'academic_year'             => now()->format('Y') . '–' . (now()->year + 1),
            'instruction_notes'         => $defaultInstructions,
        ];

        if ($college) {
            $fillData['college_id'] = $college->id;
            $fillData['college_name'] = $college->name;
            $fillData['university_name'] = $college->university_name;
        }

        $this->form->fill($fillData);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        if (!empty($data['college_id'])) {
            $college = \App\Models\ReportManagement\College::find($data['college_id']);
            if ($college) {
                $data['college_name'] = $college->name;
                if (empty($data['university_name'])) {
                    $data['university_name'] = $college->university_name;
                }
            }
        }

        $mode = $data['evaluation_mode'] ?? 'direct';

        if ($mode === 'direct') {
            $data['max_attendance_marks'] = 0;
            $data['max_task_marks'] = 0;
            $data['custom_criteria'] = null;
        } else {
            // Clean up custom_criteria
            if (isset($data['custom_criteria']) && is_array($data['custom_criteria'])) {
                $data['custom_criteria'] = array_values(array_filter($data['custom_criteria'], function ($item) {
                    return filled($item['name'] ?? null) && (float)($item['max_marks'] ?? 0) > 0;
                }));
                if (empty($data['custom_criteria'])) {
                    $data['custom_criteria'] = null;
                }
            }
        }

        // Clean up columns_config columns
        if (isset($data['columns_config']['columns']) && is_array($data['columns_config']['columns'])) {
            $cleaned = [];
            foreach ($data['columns_config']['columns'] as $c) {
                if (is_array($c) && filled($c['type'] ?? null)) {
                    $label = trim((string)($c['label'] ?? ''));
                    $type = trim((string)$c['type']);
                    $cleaned[] = [
                        'type'          => $type,
                        'label'         => $label ?: ucfirst(str_replace('_', ' ', $type)),
                        'key'           => !empty($c['key']) ? trim((string)$c['key']) : \Illuminate\Support\Str::slug($label, '_'),
                        'default_value' => $c['default_value'] ?? null,
                        'name'          => $c['name'] ?? null,
                        'width'         => !empty($c['width']) ? (float)$c['width'] : null,
                    ];
                }
            }
            $data['columns_config']['columns'] = $cleaned;
        }

        if (!isset($data['columns_config']['pdf_settings'])) {
            $data['columns_config']['pdf_settings'] = [
                'font_size' => 'normal',
                'spacing'   => 'normal',
            ];
        }

        if (empty($data['instruction_notes'])) {
            $data['instruction_notes'] = \App\Models\ReportManagement\CollegeEvaluation::buildDefaultInstructions(
                $data['max_attendance_marks'] ?? 20,
                $data['max_task_marks'] ?? 40,
                $data['total_marks'] ?? ($mode === 'direct' ? 50 : 100),
                $data['custom_criteria'] ?? [],
                $mode
            );
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $evaluation = $this->record;
        $data = $this->form->getRawState();

        // Auto generate instruction notes if left empty
        if (empty($evaluation->instruction_notes)) {
            $evaluation->update([
                'instruction_notes' => $evaluation->generateDefaultInstructions(),
            ]);
        }

        $internIds = array_map('intval', $data['intern_ids'] ?? []);
        $validInterns = Intern::whereIn('id', $internIds)->get();

        $defaults = [];
        foreach ($evaluation->getCustomColumnsList() as $col) {
            if (filled($col['default_value'] ?? null)) {
                $defaults[$col['key']] = $col['default_value'];
            }
        }

        $enrolledCount = 0;
        foreach ($validInterns as $intern) {
            $evalIntern = CollegeEvaluationIntern::firstOrCreate([
                'college_evaluation_id' => $evaluation->id,
                'intern_id'             => $intern->id,
            ]);

            if (!empty($defaults) && empty($evalIntern->row_data)) {
                $evalIntern->update(['row_data' => $defaults]);
            }

            // Automatically compute marks for each enrolled intern
            $evaluation->calculateIntern($evalIntern);
            $enrolledCount++;
        }

        Notification::make()
            ->title('Evaluation Generated Successfully')
            ->body("Evaluated and calculated marks for {$enrolledCount} intern(s).")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
