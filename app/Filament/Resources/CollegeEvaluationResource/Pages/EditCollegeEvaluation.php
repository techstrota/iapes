<?php

namespace App\Filament\Resources\CollegeEvaluationResource\Pages;

use App\Filament\Resources\CollegeEvaluationResource;
use App\Models\ReportManagement\CollegeEvaluationIntern;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCollegeEvaluation extends EditRecord
{
    protected static string $resource = CollegeEvaluationResource::class;

    public function getTitle(): string
    {
        return 'Edit Batch: ' . ($this->record->batch_title ?: 'Evaluation Batch');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_college')
                ->label('Back to College')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => $this->record->college_id
                    ? \App\Filament\Resources\CollegeResource::getUrl('view', ['record' => $this->record->college_id])
                    : \App\Filament\Resources\CollegeResource::getUrl('index')),

            Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('college-evaluations.preview-pdf', $this->record->id))
                ->openUrlInNewTab(),

            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('college-evaluations.download-pdf', $this->record->id)),



            Actions\Action::make('recalculate_all')
                ->label('Recalculate Marks')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Recalculate All Intern Marks')
                ->modalDescription('This will refresh and recompute attendance, task, and report scores for all enrolled interns according to the current marking scheme.')
                ->action(function () {
                    $evaluation = $this->record;
                    $count = 0;
                    foreach ($evaluation->evaluationInterns as $evalIntern) {
                        $evaluation->calculateIntern($evalIntern);
                        $count++;
                    }
                    Notification::make()
                        ->title('Marks Recalculated')
                        ->body("Recalculated scores for {$count} intern(s).")
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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

        if (empty($data['instruction_notes'])) {
            $data['instruction_notes'] = $this->record->generateDefaultInstructions();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $evaluation = $this->record;
        $data = $this->form->getRawState();

        if (isset($data['intern_ids'])) {
            $internIds = array_map('intval', $data['intern_ids']);

            // Add newly selected
            foreach ($internIds as $internId) {
                $evalIntern = CollegeEvaluationIntern::firstOrCreate([
                    'college_evaluation_id' => $evaluation->id,
                    'intern_id'             => $internId,
                ]);
            }

            // Remove unselected system interns (preserve custom rows where intern_id is null)
            $evaluation->evaluationInterns()->whereNotNull('intern_id')->whereNotIn('intern_id', $internIds)->delete();
        }

        // Recalculate remaining interns with updated scheme
        foreach ($evaluation->evaluationInterns as $evalIntern) {
            $evaluation->calculateIntern($evalIntern);
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['intern_ids'] = $this->record->evaluationInterns()->pluck('intern_id')->toArray();

        // Hydrate unified columns repeater for existing/legacy batches
        $data['columns_config'] = [
            'columns' => $this->record->getNormalizedColumns(),
        ];

        if (empty($data['instruction_notes'])) {
            $data['instruction_notes'] = $this->record->generateDefaultInstructions();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
