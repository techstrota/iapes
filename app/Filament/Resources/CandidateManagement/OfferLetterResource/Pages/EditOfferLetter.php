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
        // The Select field expects an array for 'multiple()'. 
        // We wrap the single ID into an array so it shows up in the dropdown.
        $data['applications'] = [$data['application_id']];
        $data['intern_name'] = $data['internship_position'] ?? '';
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Before saving the edit, take the first value from the array 
        // and put it back into the single 'application_id' field.
        if (!empty($data['applications'])) {
            $data['application_id'] = $data['applications'][0];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Check if this offer letter already has an associated intern
        $intern = Intern::where('offer_letter_id', $record->id)->first();

        if ($intern) {
            // Update the intern record with the new data from the offer letter
            $intern->update([
                'name' => $record->name,
               // 'email' => $record->email
                // You can add other fields here like phone or college if needed
            ]);
        }
    }
}
