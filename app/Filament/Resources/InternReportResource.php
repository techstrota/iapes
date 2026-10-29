<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternReportResource\Pages;
use Filament\Resources\Resource;

class InternReportResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Interns Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 23;
    protected static ?string $slug = 'reports/interns';

    // Standalone custom page resource for Comprehensive Interns Report
    protected static ?string $model = null;

    public static function getPages(): array
    {
        return [
            'index' => Pages\InternReport::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
