<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationReportResource\Pages;
use Filament\Resources\Resource;

class ApplicationReportResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Application Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 21;
    protected static ?string $slug = 'reports/applications';

    // Standalone custom page resource for Application Report
    protected static ?string $model = null;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ApplicationReport::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
