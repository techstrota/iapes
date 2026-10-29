<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskReportResource\Pages;
use Filament\Resources\Resource;

class TaskReportResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Task Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 24;
    protected static ?string $slug = 'reports/tasks';

    // Standalone custom page resource for Task Intelligence & Performance Report
    protected static ?string $model = null;

    public static function getPages(): array
    {
        return [
            'index' => Pages\TaskReport::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
