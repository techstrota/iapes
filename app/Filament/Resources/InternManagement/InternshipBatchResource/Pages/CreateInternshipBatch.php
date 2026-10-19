<?php

namespace App\Filament\Resources\InternManagement\InternshipBatchResource\Pages;

use App\Filament\Resources\InternManagement\InternshipBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInternshipBatch extends CreateRecord
{
    protected static string $resource = InternshipBatchResource::class;

    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['start_hour']) && !empty($data['end_hour'])) {
            $sH = $data['start_hour'];
            $sM = $data['start_minute'] ?? '00';
            $sA = $data['start_meridiem'] ?? 'AM';

            $eH = $data['end_hour'];
            $eM = $data['end_minute'] ?? '00';
            $eA = $data['end_meridiem'] ?? 'PM';

            $start = \Illuminate\Support\Carbon::parse("{$sH}:{$sM} {$sA}")->format('g:i A');
            $end = \Illuminate\Support\Carbon::parse("{$eH}:{$eM} {$eA}")->format('g:i A');

            $data['batch_timing'] = "{$start} To {$end}";
        } elseif (!empty($data['start_time']) && !empty($data['end_time'])) {
            $start = \Illuminate\Support\Carbon::parse($data['start_time'])->format('g:i A');
            $end = \Illuminate\Support\Carbon::parse($data['end_time'])->format('g:i A');

            $data['batch_timing'] = "{$start} To {$end}";
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $internIds = $this->data['interns'] ?? [];

        if (!empty($internIds)) {
            \App\Models\InternManagement\Intern::whereIn('id', $internIds)
                ->update(['internship_batch_id' => $this->record->id]);
        }
    }
}
