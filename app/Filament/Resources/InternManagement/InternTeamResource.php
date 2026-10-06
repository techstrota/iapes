<?php

namespace App\Filament\Resources\InternManagement;

use App\Filament\Resources\InternManagement\InternTeamResource\Pages;
use App\Filament\Resources\InternManagement\InternTeamResource\RelationManagers;
use App\Models\InternManagement\InternTeam;
use App\Models\InternManagement\Intern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class InternTeamResource extends Resource
{
    protected static ?string $model = InternTeam::class;
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationLabel = 'Project Teams';
    protected static ?string $modelLabel = 'Project Team';
    protected static ?string $pluralModelLabel = 'Project Teams';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Identity & Scope')
                    ->description('Specify project name, domain track, and deliverables.')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('team_name')
                                ->label('Project Title / Squad Name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            Forms\Components\Select::make('track')
                                ->label('Domain Track')
                                ->options([
                                    'Full Stack' => 'Full Stack',
                                    'Engineering' => 'Engineering',
                                    'Product & Design' => 'Product & Design',
                                    'AI Research' => 'AI Research',
                                    'Cloud & DevOps' => 'Cloud & DevOps',
                                ])
                                ->default('Full Stack')
                                ->required(),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('status')
                                ->label('Project Health / Status')
                                ->options([
                                    'on_track' => 'On Track',
                                    'attention' => 'Attention Needed',
                                    'completed' => 'Completed',
                                ])
                                ->default('on_track')
                                ->required(),

                            Forms\Components\TextInput::make('cohort_archive_name')
                                ->label('Cohort / Archive Cycle (Optional)')
                                ->placeholder('e.g. 2025-2026 Batch, Summer 2025')
                                ->maxLength(255),
                        ]),

                        Forms\Components\RichEditor::make('project_description')
                            ->label('Project Deliverables & Summary')
                            ->placeholder('Describe objectives, architectural scope, and key deliverables...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Lead Mentor & Supervision')
                    ->description('Mentor or engineering lead guiding this project team.')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('mentor_name')
                                ->label('Lead Mentor Name')
                                ->placeholder('e.g. David Kim, Dr. Sarah Lin')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('mentor_title')
                                ->label('Mentor Designation / Role')
                                ->placeholder('e.g. Staff Software Engineer, Principal AI Scientist')
                                ->maxLength(255),
                        ]),
                    ]),

                Forms\Components\Section::make('Assigned Interns (Project Squad)')
                    ->description('Assign individual interns to this project. A project squad can have any single (solo), double (pair), triple (trio), or group of interns (1 to N), independent of batch timing.')
                    ->extraAttributes([
                        'style' => 'overflow: visible !important; position: relative !important; z-index: 25;',
                    ])
                    ->schema([
                        Forms\Components\Select::make('interns')
                            ->label('Squad Members')
                            ->multiple()
                            ->relationship(
                                name: 'interns',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, $record) {
                                    return $query->where(function ($q) use ($record) {
                                        $q->whereNull('intern_team_id')
                                            ->where('is_active', true);
                                        if ($record) {
                                            $q->orWhere('intern_team_id', $record->id);
                                        }
                                    });
                                }
                            )
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $code = $record->intern_code ? " ({$record->intern_code})" : "";
                                $college = $record->college ? " - {$record->college}" : "";
                                return "{$record->name}{$code}{$college}";
                            })
                            ->preload()
                            ->searchable()
                            ->required()
                            ->extraAttributes([
                                'style' => 'position: relative; z-index: 30;',
                            ]),
                    ]),

                
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
                Tables\Columns\Layout\View::make('filament.intern-management.project-team-card')
                    ->components([
                        Tables\Columns\TextColumn::make('team_name')->searchable(),
                        Tables\Columns\TextColumn::make('track')->searchable(),
                        Tables\Columns\TextColumn::make('mentor_name')->searchable(),
                        Tables\Columns\TextColumn::make('cohort_archive_name')->searchable(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('track')
                    ->label('Domain Track')
                    ->options([
                        'Full Stack' => 'Full Stack',
                        'Engineering' => 'Engineering',
                        'Product & Design' => 'Product & Design',
                        'AI Research' => 'AI Research',
                        'Cloud & DevOps' => 'Cloud & DevOps',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'on_track' => 'On Track',
                        'attention' => 'Attention Needed',
                        'completed' => 'Completed',
                    ])
                    ->label('Project Status'),

                Tables\Filters\SelectFilter::make('cohort_archive_name')
                    ->label('Archive Cycle')
                    ->options(function () {
                        return InternTeam::whereNotNull('cohort_archive_name')
                            ->where('cohort_archive_name', '!=', '')
                            ->distinct()
                            ->pluck('cohort_archive_name', 'cohort_archive_name')
                            ->toArray();
                    }),

                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Archive Status')
                    ->placeholder('Active Projects')
                    ->trueLabel('Archived Projects Only')
                    ->falseLabel('Active Projects Only'),
            ])
            ->actions([])
            ->bulkActions([]);
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
            ->with(['interns', 'batch']); // Pre-loads batch info to avoid extra queries
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternTeams::route('/'),
            'create' => Pages\CreateInternTeam::route('/create'),
            'edit' => Pages\EditInternTeam::route('/{record}/edit'),
        ];
    }
}
