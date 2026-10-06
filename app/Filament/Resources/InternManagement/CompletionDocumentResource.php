<?php

namespace App\Filament\Resources\InternManagement;

use App\Filament\Resources\InternManagement\CompletionDocumentResource\Pages;
use App\Models\InternManagement\Intern;
use Filament\Resources\Resource;

class CompletionDocumentResource extends Resource
{
    protected static ?string $model = Intern::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-check';
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?string $navigationLabel = 'Completion Certificate & Letters';
    protected static ?string $modelLabel = 'Completion Certificate & Letter';
    protected static ?string $pluralModelLabel = 'Completion Certificate & Letters';
    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function can(string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompletionDocuments::route('/'),
        ];
    }
}
