<?php

namespace App\Filament\Resources\CandidateManagement\CandidateResource\Pages;

use App\Filament\Resources\CandidateManagement\CandidateResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;

    protected static ?string $title = 'Add Candidate';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['status'])) {
            $data['status'] = 'applied';
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Candidate Created')
            ->body('Candidate has been added successfully.');
    }
}
