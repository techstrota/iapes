<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Pages;

use App\Filament\Resources\CandidateManagement\OfferLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\InternManagement\Intern;

class EditOfferLetter extends EditRecord
{
    protected static string $resource = OfferLetterResource::class;
    protected static string $view = 'filament.offer-letters.split-pane';

    // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;
        $app = $record?->application;

        $data['applications'] = !empty($data['application_id']) ? [(int) $data['application_id']] : [];
        $data['template'] = !empty($data['template']) ? $data['template'] : 'single_page';
        $data['duration'] = !empty($data['duration']) ? $data['duration'] : '1 month';
        $data['name'] = $data['name'] ?: ($app?->name ?? '');
        $data['college'] = $data['college'] ?: ($app?->college ?? '');
        $data['university'] = $data['university'] ?: ($data['college'] ?: ($app?->college ?? ''));
        $data['degree'] = $data['degree'] ?: ($app?->degree ?? '');
        $data['email'] = $data['email'] ?: ($app?->email ?? '');
        $data['phone'] = $data['phone'] ?: ($app?->phone ?? '');
        $data['intern_name'] = $data['internship_position'] ?? '';

        if (empty($data['working_hours'])) {
            $data['working_hours'] = '11:00 AM to 4:00 PM, Monday to Saturday';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['applications']) && is_array($data['applications'])) {
            $firstAppId = collect($data['applications'])->filter()->first();
            $data['application_id'] = $firstAppId ?: ($this->record?->application_id);
        }

        if (empty($data['template'])) {
            $data['template'] = 'single_page';
        }

        if (empty($data['duration'])) {
            $data['duration'] = '1 month';
        }

        if ($data['template'] !== 'multi_page') {
            $data['description'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Check if this offer letter already has an associated intern
        $intern = Intern::where('offer_letter_id', $record->id)->first();

        if ($intern) {
            // Update the intern record with the complete data from the offer letter
            $intern->update(array_filter([
                'name'                => $record->name,
                'email'               => $record->email,
                'phone'               => $record->phone,
                'college'             => $record->college,
                'degree'              => $record->degree,
                'university'          => $record->university ?: $record->college,
                'internship_role'     => $record->internship_role,
                'internship_position' => $record->internship_position,
                'joining_date'        => $record->joining_date,
                'completion_date'     => $record->completion_date,
                'working_hours'       => $record->working_hours,
            ], fn ($val) => !is_null($val)));
        }
    }
}
