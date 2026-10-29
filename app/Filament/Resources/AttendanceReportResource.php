<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceReportResource\Pages;
use Filament\Resources\Resource;

class AttendanceReportResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Attendance Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 25;
    protected static ?string $slug = 'reports/attendance';

    // Standalone custom page resource for Comprehensive Attendance Intelligence & Audit Report
    protected static ?string $model = null;

    public static function getPages(): array
    {
        return [
            'index' => Pages\AttendanceReport::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
