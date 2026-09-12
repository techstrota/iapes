<?php

namespace App\Filament\Resources\TaskManagement;

use App\Filament\Resources\TaskManagement\TaskResource\Pages;
use App\Filament\Resources\TaskManagement\TaskResource\RelationManagers;
use App\Models\TaskManagement\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Textarea, FileUpload, Select, DatePicker, Section, Grid};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-s-list-bullet';
    protected static ?string $navigationLabel = 'Tasks';
    protected static ?string $navigationGroup = 'Task And Evaluation Management';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Task Details')->schema([
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                Grid::make(2)->schema([
                    DatePicker::make('due_date'),
                    Select::make('priority')
                        ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                        ->default('medium')
                        ->native(false),
                ]),
                FileUpload::make('attachment')
                    ->directory('task-files')
                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, callable $get): string {
                        $title = Str::slug($get('title') ?: 'task');
                        return $title . '-' . str()->random(4) . '.' . $file->getClientOriginalExtension();
                    }),
            ]),
            Section::make('Task Assignment')->schema([
                Select::make('assigned_type')->label('Assign To')
                    ->options(['intern' => 'Intern', 'team' => 'Team', 'batch' => 'Batch'])
                    ->live()->required()->native(false)->dehydrated(false),
                Select::make('intern_id')->label('Select Intern')
                    ->options(\App\Models\InternManagement\Intern::pluck('name', 'id'))
                    ->searchable()->preload()->visible(fn ($get) => $get('assigned_type') === 'intern')->dehydrated(false),
                Select::make('team_id')->label('Select Team')
                    ->options(\App\Models\InternManagement\InternTeam::pluck('team_name', 'id'))
                    ->searchable()->preload()->visible(fn ($get) => $get('assigned_type') === 'team')->dehydrated(false),
                Select::make('batch_id')->label('Select Batch')
                    ->options(\App\Models\InternManagement\InternshipBatch::pluck('batch_name', 'id'))
                    ->searchable()->preload()->visible(fn ($get) => $get('assigned_type') === 'batch')->dehydrated(false),
            ]),
            Section::make('Task Status')
                ->schema([
                    Select::make('status')
                        ->options(['active' => 'Active', 'completed' => 'Completed'])
                        ->default('active')->native(false)->required(),
                ])->hiddenOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('title')
                            ->weight('bold')
                            ->size('lg')
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'active' => 'primary',
                                default => 'gray',
                            }),
                    ]),
                    
                    Tables\Columns\TextColumn::make('description')
                        ->limit(80)
                        ->color('gray')
                        ->size('sm'),
                        
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('priority')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'high' => 'danger',
                                'medium' => 'warning',
                                'low' => 'success',
                                default => 'gray',
                            })
                            ->icon(fn (string $state): string => match ($state) {
                                'high' => 'heroicon-o-chevron-double-up',
                                'medium' => 'heroicon-o-minus',
                                'low' => 'heroicon-o-chevron-down',
                                default => 'heroicon-o-minus',
                            }),
                            
                        Tables\Columns\TextColumn::make('due_date')
                            ->date('M d, Y')
                            ->icon('heroicon-o-calendar')
                            ->color(fn ($record) => $record->is_overdue ? 'danger' : 'gray')
                            ->weight(fn ($record) => $record->is_overdue ? 'bold' : 'regular'),
                    ])->extraAttributes(['class' => 'mt-4']),

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('total_assigned_count')
                            ->label('Progress')
                            ->formatStateUsing(fn ($record) => "{$record->submitted_count} / {$record->total_assigned_count} Submitted")
                            ->icon('heroicon-o-document-check')
                            ->size('sm')
                            ->color('gray'),
                    ])->extraAttributes(['class' => 'mt-2']),
                ])->space(3)
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->button(),
                Tables\Actions\EditAction::make()->button()->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Task Overview')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('title')
                                        ->weight('bold')
                                        ->size('xl'),
                                    Infolists\Components\TextEntry::make('status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'completed' => 'success',
                                            'active' => 'primary',
                                            default => 'gray',
                                        }),
                                    Infolists\Components\TextEntry::make('description')
                                        ->columnSpanFull()
                                        ->prose(),
                                    Infolists\Components\TextEntry::make('priority')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'high' => 'danger',
                                            'medium' => 'warning',
                                            'low' => 'success',
                                            default => 'gray',
                                        }),
                                    Infolists\Components\TextEntry::make('due_date')
                                        ->date('F d, Y')
                                        ->color(fn ($record) => $record->is_overdue ? 'danger' : 'gray'),
                                ]),
                            Infolists\Components\Grid::make(1)
                                ->schema([
                                    Infolists\Components\TextEntry::make('assigned_info')
                                        ->label('Assigned To')
                                        ->icon('heroicon-o-user-group')
                                        ->state(function (Task $record) {
                                            $assignment = $record->assignments->first();
                                            if (!$assignment) return 'Unassigned';
                                            return match ($assignment->assigned_type) {
                                                'intern' => 'Intern: ' . ($assignment->intern?->name ?? 'N/A'),
                                                'team'   => 'Team: '   . ($assignment->team?->team_name ?? 'N/A'),
                                                'batch'  => 'Batch: '  . ($assignment->batch?->batch_name ?? 'N/A'),
                                                default  => 'Unassigned',
                                            };
                                        }),
                                    Infolists\Components\TextEntry::make('attachment')
                                        ->label('Attachment')
                                        ->icon('heroicon-o-paper-clip')
                                        ->formatStateUsing(fn ($state) => $state ? 'Download File' : 'No attachment')
                                        ->url(fn ($state) => $state ? asset('storage/' . $state) : null)
                                        ->openUrlInNewTab()
                                        ->color(fn ($state) => $state ? 'primary' : 'gray'),
                                ])->grow(false),
                        ])
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view'   => Pages\ViewTask::route('/{record}'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
