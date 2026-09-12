<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\Pages;

use App\Filament\Resources\CandidateManagement\CandidateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;

    // After saving, go back to the View page for this candidate
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $record->update($data);
            return $record;
        } catch (\Illuminate\Database\QueryException $e) {
            \Filament\Notifications\Notification::make()
                ->title('Database Error')
                ->body('An error occurred while saving to the database. Please check your inputs. (' . $e->errorInfo[2] . ')')
                ->danger()
                ->send();

            throw new \Filament\Support\Exceptions\Halt();
        }
    }
}
