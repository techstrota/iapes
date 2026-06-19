<?php

namespace App\Filament\Resources\InterviewManagement\ApplicationResource\Pages;

use App\Filament\Resources\InterviewManagement\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

     // To redirect on the page in resource
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return static::getModel()::create($data);
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
