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
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(['default' => 1, 'lg' => 12])->schema([
                // ── LEFT COLUMN: Task Specifications & Deliverables (7 cols) ──
                Section::make('1. Task Specifications & Deliverables')
                    ->description('Define deliverable criteria, SLAs, and attach project assets.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(['default' => 12, 'lg' => 7])
                    ->schema([
                        TextInput::make('title')
                            ->label('Task Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Build Responsive Analytics Dashboard & Export Engine')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            Select::make('priority')
                                ->label('Priority Level & SLA')
                                ->options([
                                    'high'   => '🔥 High Priority (Strict SLA • 48h)',
                                    'medium' => '⚡ Medium Priority (Standard Milestone)',
                                    'low'    => '🟢 Low Priority (Flexible / Self-paced)',
                                ])
                                ->default('medium')
                                ->required()
                                ->native(false),

                            DatePicker::make('due_date')
                                ->label('Deliverable Due Date')
                                ->native(false)
                                ->displayFormat('M d, Y')
                                ->prefixIcon('heroicon-o-calendar')
                                ->placeholder('Select deadline'),
                        ]),

                        Select::make('status')
                            ->label('Task Lifecycle Status')
                            ->options([
                                'active'    => 'Active (Open for Submissions)',
                                'completed' => 'Completed (Evaluated & Closed)',
                            ])
                            ->default('active')
                            ->native(false)
                            ->required()
                            ->visibleOn('edit'),

                        Textarea::make('description')
                            ->label('Acceptance Criteria & Task Instructions')
                            ->placeholder("### Acceptance Criteria:\n1. Implement deliverable checkpoints\n2. Maintain code standards & test coverage\n3. Attach documentation or repository link")
                            ->rows(6)
                            ->helperText('Markdown formatting supported. Detail instructions and evaluation rubrics for interns.')
                            ->columnSpanFull(),

                        FileUpload::make('attachment')
                            ->label('Project Brief & Starter Assets')
                            ->directory('task-files')
                            ->disk('public')
                            ->helperText('Drop project specifications, PDF brief, or ZIP starter templates (Max 50MB)')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, callable $get): string {
                                $title = Str::slug($get('title') ?: 'task');
                                return $title . '-' . str()->random(4) . '.' . $file->getClientOriginalExtension();
                            })
                            ->columnSpanFull(),
                    ]),

                // ── RIGHT COLUMN: Multi-Target Assignment Engine (5 cols) ──
                Section::make('2. Target Selection Engine')
                    ->description('Target interns directly, or select by Batch/Project Team. Tasks will always be individually assigned to each intern.')
                    ->icon('heroicon-o-user-group')
                    ->columnSpan(['default' => 12, 'lg' => 5])
                    ->schema([
                        Select::make('assigned_type')
                            ->label('Selection Mode')
                            ->options([
                                'intern' => '👥 Specific Interns (Direct Selection)',
                                'batch'  => '📦 Internship Batch (Bulk Select Interns)',
                                'team'   => '🚀 Project Team / Squad (Bulk Select Interns)',
                            ])
                            ->default('intern')
                            ->live()
                            ->required()
                            ->native(false)
                            ->dehydrated(false)
                            ->helperText('Choose how you want to select recipients. Tasks are always assigned individually to each intern.'),

                        // Multi-select Interns
                        Select::make('intern_ids')
                            ->label('Select Interns (Multi-Select)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn ($get) => $get('assigned_type') === 'intern')
                            ->required(fn ($get) => $get('assigned_type') === 'intern')
                            ->dehydrated(false)
                            ->default(function () {
                                $param = request()->query('intern_ids');
                                if (!$param) return [];
                                $ids = is_array($param) ? $param : explode(',', (string) $param);
                                return array_values(array_filter(array_map('intval', $ids)));
                            })
                            ->options(function () {
                                return \App\Models\InternManagement\Intern::where('is_active', true)
                                    ->with(['batch', 'team'])
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function ($intern) {
                                        $details = array_filter([
                                            $intern->intern_code,
                                            $intern->domain,
                                            $intern->batch?->batch_name,
                                        ]);
                                        $suffix = !empty($details) ? ' (' . implode(' • ', $details) . ')' : '';
                                        return [$intern->id => $intern->name . $suffix];
                                    });
                            })
                            ->helperText('Search and select one or multiple individual interns who will receive this task.'),

                        // Multi-select Teams (Projects)
                        Select::make('team_ids')
                            ->label('Select Project Teams (Multi-Select)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn ($get) => $get('assigned_type') === 'team')
                            ->required(fn ($get) => $get('assigned_type') === 'team')
                            ->dehydrated(false)
                            ->options(function () {
                                return \App\Models\InternManagement\InternTeam::withCount(['interns' => fn($q) => $q->where('is_active', true)])
                                    ->orderBy('team_name')
                                    ->get()
                                    ->mapWithKeys(function ($team) {
                                        $track = $team->track ? " • {$team->track}" : '';
                                        return [$team->id => "{$team->team_name} ({$team->interns_count} Active Interns{$track})"];
                                    });
                            })
                            ->helperText('All active members belonging to the selected project teams will be individually assigned this task.'),

                        // Multi-select Batches
                        Select::make('batch_ids')
                            ->label('Select Internship Batches (Multi-Select)')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn ($get) => $get('assigned_type') === 'batch')
                            ->required(fn ($get) => $get('assigned_type') === 'batch')
                            ->dehydrated(false)
                            ->options(function () {
                                return \App\Models\InternManagement\InternshipBatch::withCount(['interns' => fn($q) => $q->where('is_active', true)])
                                    ->orderBy('batch_name')
                                    ->get()
                                    ->mapWithKeys(function ($batch) {
                                        return [$batch->id => "{$batch->batch_name} ({$batch->interns_count} Active Interns)"];
                                    });
                            })
                            ->helperText('All active interns enrolled in the selected batches will be individually assigned this task.'),

                        // Live Audience Reach Telemetry Card
                        Forms\Components\Placeholder::make('audience_reach_preview')
                            ->label('Live Audience Reach')
                            ->content(function ($get) {
                                $type = $get('assigned_type');
                                $count = 0;
                                $detail = '';

                                if ($type === 'intern') {
                                    $ids = (array) ($get('intern_ids') ?? []);
                                    $count = count(array_filter($ids));
                                    $detail = "{$count} individual " . ($count === 1 ? 'intern' : 'interns') . ' targeted';
                                } elseif ($type === 'team') {
                                    $ids = array_filter((array) ($get('team_ids') ?? []));
                                    $internCount = \App\Models\InternManagement\Intern::whereIn('intern_team_id', $ids)
                                        ->where('is_active', true)
                                        ->count();
                                    $teamCount = count($ids);
                                    $count = $internCount;
                                    $detail = "{$count} active interns across {$teamCount} project " . ($teamCount === 1 ? 'team' : 'teams');
                                } elseif ($type === 'batch') {
                                    $ids = array_filter((array) ($get('batch_ids') ?? []));
                                    $internCount = \App\Models\InternManagement\Intern::whereIn('internship_batch_id', $ids)
                                        ->where('is_active', true)
                                        ->count();
                                    $batchCount = count($ids);
                                    $count = $internCount;
                                    $detail = "{$count} active interns enrolled across {$batchCount} " . ($batchCount === 1 ? 'batch' : 'batches');
                                }

                                if ($count === 0) {
                                    return new \Illuminate\Support\HtmlString(
                                        "<div style='padding: 12px 14px; border-radius: 10px; background-color: #090e1c; border: 1px dashed #222a3d; color: #94a3b8; font-size: 12px;'>
                                            ⚡ No recipients selected yet. Choose one or more interns, batches, or teams above.
                                        </div>"
                                    );
                                }

                                return new \Illuminate\Support\HtmlString(
                                    "<div style='padding: 14px 16px; border-radius: 12px; background-color: #090e1c; border: 1.5px solid rgba(59, 130, 246, 0.4); display: flex; flex-direction: column; gap: 6px;'>
                                        <div style='display: flex; align-items: center; justify-content: space-between;'>
                                            <span style='font-size: 11px; font-weight: 700; color: #b8c4ff; text-transform: uppercase; letter-spacing: 0.05em;'>Live Audience Reach</span>
                                            <span style='font-size: 10px; font-weight: 700; color: #4edea3; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 6px; border-radius: 4px;'>INDIVIDUAL ASSIGNMENT</span>
                                        </div>
                                        <div style='font-size: 17px; font-weight: 700; color: #ffffff;'>{$count} " . ($count === 1 ? 'Intern' : 'Interns') . " Targeted</div>
                                        <div style='font-size: 12px; color: #94a3b8;'>{$detail}</div>
                                        <div style='margin-top: 4px; font-size: 11px; color: #4edea3; display: flex; align-items: center; gap: 4px;'>
                                            <span>✓ Tasks will be individually assigned to each of the {$count} interns upon publishing</span>
                                        </div>
                                    </div>"
                                );
                            }),
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
                Tables\Columns\Layout\View::make('filament.task-management.task-card')
                    ->components([
                        Tables\Columns\TextColumn::make('title')->searchable(),
                        Tables\Columns\TextColumn::make('description')->searchable(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priority')
                    ->options([
                        'high' => 'High Priority',
                        'medium' => 'Medium Priority',
                        'low' => 'Low Priority',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Task Status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('batch_id')
                    ->label('Internship Batch')
                    ->options(fn () => \App\Models\InternManagement\InternshipBatch::pluck('batch_name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('assignments.intern', fn ($q) => $q->where('internship_batch_id', $data['value']));
                        }
                    }),

                Tables\Filters\SelectFilter::make('team_id')
                    ->label('Project Team')
                    ->options(fn () => \App\Models\InternManagement\InternTeam::pluck('team_name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('assignments.intern', fn ($q) => $q->where('intern_team_id', $data['value']));
                        }
                    }),

                Tables\Filters\SelectFilter::make('assignment_scope')
                    ->label('Assignment Scope')
                    ->options([
                        'solo'  => '👤 Solo Tasks (1 Intern)',
                        'group' => '👥 Group Tasks (Multiple Interns)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (($data['value'] ?? null) === 'solo') {
                            $query->has('assignments', '=', 1);
                        } elseif (($data['value'] ?? null) === 'group') {
                            $query->has('assignments', '>', 1);
                        }
                    }),
            ])
            ->emptyStateHeading('No Tasks in this Filter')
            ->emptyStateDescription('There are currently no tasks matching the selected criteria. Switch tabs above or create a new milestone task.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\Action::make('create_task')
                    ->label('+ Create New Task')
                    ->url(static::getUrl('create'))
                    ->color('primary')
                    ->button(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Task Specifications & Details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('title')
                                        ->label('Task Milestone Title')
                                        ->weight('bold')
                                        ->size('lg'),

                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Milestone Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'completed' => 'success',
                                            'active' => 'primary',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn ($state) => ucfirst($state)),

                                    Infolists\Components\TextEntry::make('description')
                                        ->label('Deliverable Description & Guidelines')
                                        ->columnSpanFull()
                                        ->prose(),

                                    Infolists\Components\TextEntry::make('priority')
                                        ->label('Priority Level')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'high' => 'danger',
                                            'medium' => 'warning',
                                            'low' => 'success',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn ($state) => ucfirst($state) . ' Priority'),

                                    Infolists\Components\TextEntry::make('due_date')
                                        ->label('Submission Deadline')
                                        ->badge()
                                        ->state(fn ($record) => $record->due_date ? $record->due_date->format('F d, Y') . ($record->is_overdue ? ' (Overdue)' : '') : 'No Deadline Set')
                                        ->color(fn ($record) => $record->is_overdue ? 'danger' : 'info'),
                                ]),

                            Infolists\Components\Grid::make(1)
                                ->schema([
                                     Infolists\Components\TextEntry::make('assigned_info')
                                         ->label('Assigned Cohort Members')
                                         ->icon('heroicon-o-user-group')
                                         ->iconColor('primary')
                                         ->state(function (Task $record) {
                                             $interns = $record->assigned_interns;
                                             if ($interns->isEmpty()) return 'Unassigned';

                                             if ($interns->count() === 1) {
                                                 return 'Solo Intern: ' . $interns->first()->name;
                                             }

                                             $names = $interns->pluck('name')->filter()->values();
                                             return $names->count() . ' Interns: ' . $names->take(4)->implode(', ') . ($names->count() > 4 ? ' (+' . ($names->count() - 4) . ' more)' : '');
                                         }),

                                    Infolists\Components\TextEntry::make('attachment')
                                        ->label('Task Guidelines File')
                                        ->icon('heroicon-o-paper-clip')
                                        ->iconColor('primary')
                                        ->formatStateUsing(fn ($state) => $state ? 'Download Attachment' : 'No attachment uploaded')
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
