<?php

namespace App\Filament\Resources\InternManagement;

use App\Filament\Resources\InternManagement\InternshipBatchResource\Pages;
use App\Filament\Resources\InternManagement\InternshipBatchResource\RelationManagers;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\Intern;
use App\Models\InterviewManagement\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, TextArea, FileUpload, Select, DatePicker, TimePicker, Section, Grid, Fieldset, ToggleButtons, Placeholder};
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

                                $sH = $get('start_hour');
                                $sM = $get('start_minute') ?? '00';
                                $sA = $get('start_meridiem') ?? 'AM';
                                $eH = $get('end_hour');
                                $eM = $get('end_minute') ?? '00';
                                $eA = $get('end_meridiem') ?? 'PM';

                                if (!$sH || !$eH) return;

                                try {
                                    $newStart = \Illuminate\Support\Carbon::parse("{$sH}:{$sM} {$sA}")->format('H:i');
                                    $newEnd = \Illuminate\Support\Carbon::parse("{$eH}:{$eM} {$eA}")->format('H:i');
                                } catch (\Exception $e) {
                                    return;
                                }

                                // Fetch all batches to check for timing overlaps
                                $allBatches = \App\Models\InternManagement\InternshipBatch::query()
                                    ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                    ->where('is_archived', false)
                                    ->get();

                                $currentOccupancy = 0;
                                foreach ($allBatches as $batch) {
                                    if (!$batch->batch_timing) continue;
                                    
                                    $times = preg_split('/(\s+to\s+|\s*-\s*)/i', trim($batch->batch_timing));
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
                    ->icon('heroicon-o-clock')
                    ->description('Set the 12-hour daily shift timing for this batch.')
                    ->schema([
                        Grid::make(2)->schema([
                            Fieldset::make('Batch Start Time')
                                ->schema([
                                    Select::make('start_hour')
                                        ->label('Hour')
                                        ->options(static::getHourOptions())
                                        ->default('09')
                                        ->required()
                                        ->live(),

                                    Select::make('start_minute')
                                        ->label('Minute')
                                        ->options(static::getMinuteOptions())
                                        ->default('00')
                                        ->required()
                                        ->live(),

                                    ToggleButtons::make('start_meridiem')
                                        ->label('AM / PM')
                                        ->options([
                                            'AM' => 'AM',
                                            'PM' => 'PM',
                                        ])
                                        ->colors([
                                            'AM' => 'info',
                                            'PM' => 'warning',
                                        ])
                                        ->default('AM')
                                        ->grouped()
                                        ->required()
                                        ->live(),
                                ])
                                ->columns(3),

                            Fieldset::make('Batch End Time')
                                ->schema([
                                    Select::make('end_hour')
                                        ->label('Hour')
                                        ->options(static::getHourOptions())
                                        ->default('01')
                                        ->required()
                                        ->live(),

                                    Select::make('end_minute')
                                        ->label('Minute')
                                        ->options(static::getMinuteOptions())
                                        ->default('30')
                                        ->required()
                                        ->live(),

                                    ToggleButtons::make('end_meridiem')
                                        ->label('AM / PM')
                                        ->options([
                                            'AM' => 'AM',
                                            'PM' => 'PM',
                                        ])
                                        ->colors([
                                            'AM' => 'info',
                                            'PM' => 'warning',
                                        ])
                                        ->default('PM')
                                        ->grouped()
                                        ->required()
                                        ->live()
                                        ->rules([
                                            static function (Forms\Get $get): \Closure {
                                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $sH = $get('start_hour');
                                                    $sM = $get('start_minute') ?? '00';
                                                    $sA = $get('start_meridiem') ?? 'AM';
                                                    $eH = $get('end_hour');
                                                    $eM = $get('end_minute') ?? '00';
                                                    $eA = $value ?? $get('end_meridiem') ?? 'PM';

                                                    if ($sH && $eH) {
                                                        try {
                                                            $startCarbon = \Illuminate\Support\Carbon::parse("{$sH}:{$sM} {$sA}");
                                                            $endCarbon   = \Illuminate\Support\Carbon::parse("{$eH}:{$eM} {$eA}");

                                                            if ($endCarbon->lte($startCarbon)) {
                                                                $fail('Batch End Time must be later than Batch Start Time.');
                                                            }
                                                        } catch (\Exception $e) {}
                                                    }
                                                };
                                            },
                                        ]),
                                ])
                                ->columns(3),
                        ]),

                        Placeholder::make('schedule_preview')
                            ->label('Batch Schedule Summary')
                            ->content(function (Forms\Get $get) {
                                $sH = $get('start_hour');
                                $sM = $get('start_minute') ?? '00';
                                $sA = $get('start_meridiem') ?? 'AM';
                                $eH = $get('end_hour');
                                $eM = $get('end_minute') ?? '00';
                                $eA = $get('end_meridiem') ?? 'PM';

                                if (!$sH || !$eH) {
                                    return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-400">Select start and end times above to preview batch schedule.</span>');
                                }

                                try {
                                    $startCarbon = \Illuminate\Support\Carbon::parse("{$sH}:{$sM} {$sA}");
                                    $endCarbon   = \Illuminate\Support\Carbon::parse("{$eH}:{$eM} {$eA}");

                                    $startStr = $startCarbon->format('g:i A');
                                    $endStr   = $endCarbon->format('g:i A');

                                    if ($endCarbon->lte($startCarbon)) {
                                        return new \Illuminate\Support\HtmlString("
                                            <div class='inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-danger-50 text-danger-700 dark:bg-danger-950/50 dark:text-danger-300 border border-danger-200 dark:border-danger-800 text-sm font-semibold'>
                                                <svg class='w-4 h-4 text-danger-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path></svg>
                                                <span>Batch End Time ({$endStr}) must be after Batch Start Time ({$startStr})</span>
                                            </div>
                                        ");
                                    }

                                    $diffMinutes = $startCarbon->diffInMinutes($endCarbon);
                                    $hours = floor($diffMinutes / 60);
                                    $mins = $diffMinutes % 60;
                                    $durationFormatted = $hours . ' hr' . ($hours > 1 ? 's' : '') . ($mins > 0 ? " {$mins} min" : '');

                                    return new \Illuminate\Support\HtmlString("
                                        <div class='inline-flex items-center gap-2.5 px-3.5 py-2 rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800 text-sm font-medium'>
                                            <svg class='w-4 h-4 text-primary-600 dark:text-primary-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                                            <span>Scheduled: <strong class='font-semibold text-gray-900 dark:text-white'>{$startStr}</strong> &nbsp;To&nbsp; <strong class='font-semibold text-gray-900 dark:text-white'>{$endStr}</strong> &nbsp;•&nbsp; <span class='inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900/60 dark:text-primary-200'>{$durationFormatted} shift</span></span>
                                        </div>
                                    ");
                                } catch (\Exception $e) {
                                    return '';
                                }
                            }),
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
    public static function getHourOptions(): array
    {
        return [
            '01' => '01',
            '02' => '02',
            '03' => '03',
            '04' => '04',
            '05' => '05',
            '06' => '06',
            '07' => '07',
            '08' => '08',
            '09' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12',
        ];
    }

    public static function getMinuteOptions(): array
    {
        return [
            '00' => '00',
            '05' => '05',
            '10' => '10',
            '15' => '15',
            '20' => '20',
            '25' => '25',
            '30' => '30',
            '35' => '35',
            '40' => '40',
            '45' => '45',
            '50' => '50',
            '55' => '55',
        ];
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
