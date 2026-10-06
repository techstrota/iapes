<?php

namespace App\Filament\Intern\Resources;

use App\Filament\Intern\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Carbon\Carbon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'My Attendance';
    protected static ?string $navigationGroup = 'Work & Tasks';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool 
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
    
    // Global Scope: Show ONLY the logged-in intern's data
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('intern_id', auth()->id()); 
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->defaultSort('date', 'desc')
            ->contentGrid([
                'default' => 1,
                'sm'      => 1,
                'md'      => 2,
                'lg'      => 3,
                'xl'      => 3,
                '2xl'     => 4,
            ])
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                Tables\Columns\Layout\View::make('filament.intern.attendance.intern-attendance-card'),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Filter by Month')
                    ->options([
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $date): Builder => $query->whereMonth('date', $date),
                        );
                    }),

                Filter::make('week')
                    ->label('Filter by Timeframe')
                    ->form([
                        Select::make('week_offset')
                            ->label('Time Window')
                            ->options([
                                '0' => 'This Week',
                                '1' => 'Last Week',
                                '2' => '2 Weeks Ago',
                                '3' => 'Last 30 Days',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['week_offset'] !== null,
                            function (Builder $query) use ($data): Builder {
                                if ($data['week_offset'] === '3') {
                                    return $query->where('date', '>=', now()->subDays(30));
                                }
                                $startOfWeek = Carbon::now()->subWeeks((int) $data['week_offset'])->startOfWeek();
                                $endOfWeek = Carbon::now()->subWeeks((int) $data['week_offset'])->endOfWeek();
                                return $query->whereBetween('date', [$startOfWeek, $endOfWeek]);
                            },
                        );
                    }),
            ])
            ->filtersFormColumns(2)
            ->emptyStateHeading('No Attendance Records Found')
            ->emptyStateDescription('There are currently no attendance logs matching your filter or tab criteria.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
        ];
    }
}
