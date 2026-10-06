<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('intern_id')
                    ->relationship('intern', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'leave' => 'Leave',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('intern.intern_code')
                    ->label('Intern ID')
                    ->sortable()
                    ->searchable(),
                // Displays the Intern Name clearly
                TextColumn::make('intern.name')
                    ->label('Intern Name')
                    ->searchable()
                    ->sortable(),

                // 1. BETTER UI: Mark status directly in the table row
                SelectColumn::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'leave' => 'Leave',
                    ])
                    ->selectablePlaceholder(false)
                    ->afterStateUpdated(fn ($record, $state) => 
                        // Optional: add logic here if needed
                        $record->update(['status' => $state])
                    ),

                TextColumn::make('note')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // 2. THE GROUPING UI: This creates the "Batch 1" headers you want
            ->groups([
                Group::make('intern.batch.batch_name')
                    ->label('Intern Batch')
                    ->getTitleFromRecordUsing(function ($record): string {
                        // Access the batch related to the intern
                        $batch = $record->intern?->batch;
                        
                        $name = $batch?->batch_name ?? 'Unknown Batch';
                        $timing = $batch?->batch_timing ?? 'No Timing Set';

                        return "{$name} | Timing: {$timing}";
                    })
                    ->collapsible(), // Allows you to hide/show 5 interns at once
            ])
            ->defaultGroup('intern.batch.batch_name')
            
            ->filters([
                // Automatically focus on today's attendance records
                Tables\Filters\Filter::make('today')
                    ->label('Today Only')
                    ->default()
                    ->query(fn ($query) => $query->whereDate('date', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markPresent')
                        ->label('Mark Selected Present')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'present'])),
                    
                    Tables\Actions\BulkAction::make('markAbsent')
                        ->label('Mark Selected Absent')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'absent'])),
                        
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('intern', function (Builder $query) {
            $query->where('is_active', true);
        });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function canViewAny(): bool
    {
        return true; 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
