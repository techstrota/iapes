<?php

namespace App\Filament\Resources\InternManagement;

use App\Filament\Resources\InternManagement\InternshipBatchResource\Pages;
use App\Filament\Resources\InternManagement\InternshipBatchResource\RelationManagers;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\Intern;
use App\Models\InterviewManagement\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, TextArea, FileUpload, Select, DatePicker, TimePicker, Section, Grid};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, BulkAction};
use Filament\Tables\Columns\{TextColumn, ToggleColumn, BadgeColumn, IconColumn};
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class InternshipBatchResource extends Resource
{
    protected static ?string $model = InternshipBatch::class;
    protected static ?string $navigationLabel = 'Intern Batches';
    public static function getPluralLabel(): ?string
    {
        return 'Intern Batches';
    }
    public static function getModelLabel(): string
    {
        return 'Intern Batch';
    }
    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('batch_name')
                    ->required() // This ensures the user fills it out
                    ->maxLength(255),
                TextInput::make('capacity')
                    ->label('Batch Capacity (Seats)')
                    ->numeric()
                    ->default(50)
                    ->minValue(1)
                    ->required()
                    ->helperText('Maximum number of intern seats allocated for this batch.')
                    ->live(),
                Select::make('interns')
                    ->label('Select Interns')
                    ->multiple()
                    ->relationship('interns', 'name', modifyQueryUsing: function ($query, $record) {
                        return $query->where(function ($q) use ($record) {
                            // Include interns who don't have a batch assigned yet
                            $q->whereNull('internship_batch_id')
                              ->where('is_active', true);

                            // If you are EDITING an existing batch, keep the interns already in it
                            if ($record) {
                                $q->orWhere('internship_batch_id', $record->id);
                            }
                        });
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $college = ($record->college ?? $record->application?->college) ?? 'N/A';
                        return "{$record->name} ({$college})";
                    })
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, $set) => $set('no_of_interns', count($state)))
                    ->required()
                    ->rules([
                        static function (Forms\Get $get, $record): \Closure {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                $newCount = count($value);
                                $batchCapacity = (int) ($get('capacity') ?: ($record?->capacity ?? 50));

                                if ($newCount > $batchCapacity) {
                                    $fail("Selected {$newCount} interns exceeds this batch's capacity limit of {$batchCapacity} seats.");
                                    return;
                                }

                                $newStart = $get('start_time');
                                $newEnd = $get('end_time');

                                if (!$newStart || !$newEnd) return;

                                // Fetch all batches to check for timing overlaps
                                $allBatches = \App\Models\InternManagement\InternshipBatch::query()
                                    ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                    ->where('is_archived', false)
                                    ->get();

                                $currentOccupancy = 0;
                                foreach ($allBatches as $batch) {
                                    if (!$batch->batch_timing) continue;
                                    
                                    $times = explode(' - ', $batch->batch_timing);
                                    if (count($times) !== 2) continue;

                                    $existingStart = \Illuminate\Support\Carbon::parse($times[0])->format('H:i');
                                    $existingEnd = \Illuminate\Support\Carbon::parse($times[1])->format('H:i');
                                    
                                    if ($existingStart < $newEnd && $existingEnd > $newStart) {
                                        $currentOccupancy += $batch->no_of_interns;
                                    }
                                }

                                if (($currentOccupancy + $newCount) > $batchCapacity) {
                                    $fail("Capacity Exceeded! The overlapping batches already occupy {$currentOccupancy} seats. This batch capacity is {$batchCapacity}.");
                                }
                            };
                        },
                    ]),
                Section::make('Batch Schedule')
                    ->schema([
                        Grid::make(2)->schema([
                            TimePicker::make('start_time')
                                ->label('Batch Start')
                                ->withoutSeconds()
                                ->required()
                                ->live(), // Keep live() for validation and sync

                            TimePicker::make('end_time')
                                ->label('Batch End')
                                ->withoutSeconds()
                                ->required()
                                ->after('start_time')
                                ->live(),
                        ]),
                    ]),

                TextInput::make('no_of_interns')
                    ->numeric()
                    ->label('Number of Interns')
                    ->readonly(),

                Section::make('Batch Status & Archival')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Toggle::make('is_archived')
                                ->label('Archived Cohort')
                                ->helperText('Mark this batch as archived / completed.')
                                ->live(),

                            TextInput::make('cohort_archive_name')
                                ->label('Archive Cycle Name')
                                ->placeholder('e.g. 2025-2026 Batch, Summer 2026')
                                ->visible(fn (Forms\Get $get) => (bool) $get('is_archived')),
                        ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('15s')
            ->contentGrid([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
                'xl' => 2,
                '2xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\View::make('filament.intern-management.batch-card'),
            ])
            ->actions([])
            ->bulkActions([])
            ->filters([
                Tables\Filters\SelectFilter::make('cohort_archive_name')
                    ->label('Archive Cycle')
                    ->options(function () {
                        return InternshipBatch::whereNotNull('cohort_archive_name')
                            ->where('cohort_archive_name', '!=', '')
                            ->distinct()
                            ->pluck('cohort_archive_name', 'cohort_archive_name')
                            ->toArray();
                    }),

                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Archive Status')
                    ->placeholder('All Statuses')
                    ->trueLabel('Archived Batches Only')
                    ->falseLabel('Active Batches Only'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['team', 'interns']); 
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternshipBatches::route('/'),
            'create' => Pages\CreateInternshipBatch::route('/create'),
            'edit' => Pages\EditInternshipBatch::route('/{record}/edit'),
        ];
    }
}
