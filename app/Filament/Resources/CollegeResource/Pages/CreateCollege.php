<?php

namespace App\Filament\Resources\CollegeResource\Pages;

use App\Filament\Resources\CollegeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollege extends CreateRecord
{
    protected static string $resource = CollegeResource::class;

    protected static ?string $title = 'Register College or University';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
