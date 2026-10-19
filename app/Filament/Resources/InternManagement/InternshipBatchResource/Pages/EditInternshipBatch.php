<?php

namespace App\Filament\Resources\InternManagement\InternshipBatchResource\Pages;

use App\Filament\Resources\InternManagement\InternshipBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon; // <--- Add this lin

class EditInternshipBatch extends EditRecord
{
    protected static string $resource = InternshipBatchResource::class;

    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (\App\Models\InternManagement\InternshipBatch $record) {
                    $record->interns()->update(['internship_batch_id' => null]);
                    $record->teams()->update(['internship_batch_id' => null]);
                }),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->formatBatchTiming($data);
    }

    protected function formatBatchTiming(array $data): array
    {
        if (!empty($data['start_hour']) && !empty($data['end_hour'])) {
            $sH = $data['start_hour'];
            $sM = $data['start_minute'] ?? '00';
            $sA = $data['start_meridiem'] ?? 'AM';

            $eH = $data['end_hour'];
            $eM = $data['end_minute'] ?? '00';
            $eA = $data['end_meridiem'] ?? 'PM';

            $start = Carbon::parse("{$sH}:{$sM} {$sA}")->format('g:i A');
            $end = Carbon::parse("{$eH}:{$eM} {$eA}")->format('g:i A');

            $data['batch_timing'] = "{$start} To {$end}";
        } elseif (!empty($data['start_time']) && !empty($data['end_time'])) {
            // Fallback: Parse the times and format as 12-hour with AM/PM
            $start = Carbon::parse($data['start_time'])->format('g:i A');
            $end = Carbon::parse($data['end_time'])->format('g:i A');

            $data['batch_timing'] = "{$start} To {$end}";
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $internIds = $this->data['interns'] ?? [];
        \App\Models\InternManagement\Intern::where('internship_batch_id', $this->record->id)
            ->update(['internship_batch_id' => null]);

        if (!empty($internIds)) {
            \App\Models\InternManagement\Intern::whereIn('id', $internIds)
                ->update(['internship_batch_id' => $this->record->id]);
        }
    }

    // For Displaying batch timing when editing Internship Batch
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Check if batch_timing exists and split it back into split 12-hour components
        if (!empty($data['batch_timing'])) {
            $times = preg_split('/(\s+to\s+|\s*-\s*)/i', trim($data['batch_timing']));
            
            if (count($times) === 2) {
                try {
                    $startCarbon = Carbon::parse($times[0]);
                    $endCarbon   = Carbon::parse($times[1]);

                    $data['start_hour']     = $startCarbon->format('h');
                    $data['start_minute']   = $startCarbon->format('i');
                    $data['start_meridiem'] = $startCarbon->format('A');

                    $data['end_hour']       = $endCarbon->format('h');
                    $data['end_minute']     = $endCarbon->format('i');
                    $data['end_meridiem']   = $endCarbon->format('A');
                } catch (\Exception $e) {
                    // Fallback
                }
            }
        }

        return $data;
    }
}
