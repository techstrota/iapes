<?php

namespace App\Filament\Resources\CandidateManagement;

use App\Filament\Resources\CandidateManagement\InterviewBatchResource\Pages;
use App\Models\InterviewManagement\InterviewBatch;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class InterviewBatchResource extends Resource
{
    protected static ?string $model = InterviewBatch::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Candidate Management';
    protected static ?string $modelLabel = 'Interview Batch';
    protected static ?string $pluralModelLabel = 'Interview Batches';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Batch Details')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('interview_batch_code')
                            ->label('Batch Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated on save'),

                        Forms\Components\TextInput::make('interview_batch_name')
                            ->label('Batch Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. September Batch 1'),

                        Forms\Components\DatePicker::make('interview_date')
                            ->label('Interview Date')
                            ->required()
                            ->native(false)
                            ->minDate(now()->startOfDay())
                            ->displayFormat('d M, Y'),

                        Forms\Components\TextInput::make('interview_location')
                            ->label('Location')
                            ->required()
                            ->default('503, Sterling Centre, R C Dutt Road, Near Fairfield Hotel, Alkapuri, Vadodara, Gujarat, India - 390007')
                            ->maxLength(255)
                            ->placeholder('e.g. Office - Room 3B'),
                    ]),

                Forms\Components\Section::make('Schedule & Capacity')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Start Time')
                            ->default('10:30:00')
                            ->required(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('End Time')
                            ->default('11:30:00')
                            ->required(),

                        Forms\Components\TextInput::make('batch_size')
                            ->label('Maximum Candidates')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(5)
                            ->placeholder('e.g. 15'),

                        Forms\Components\Select::make('capacity_status')
                            ->options([
                                'open' => '🟢 Open',
                                'full' => '🔴 Full',
                            ])
                            ->default('open')
                            ->required(),

                        Forms\Components\Select::make('workflow_status')
                            ->options([
                                'scheduled' => '📅 Scheduled',
                                'completed' => '✅ Completed',
                                'cancelled' => '❌ Cancelled',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['assignments.application']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
                '2xl' => 3,
            ])
            ->recordUrl(fn ($record) => static::getUrl('edit', ['record' => $record]))
            ->poll('5s')
            ->defaultSort('interview_date', 'desc')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // Row 1: Code & Status Badge
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('interview_batch_code')
                            ->weight('bold')
                            ->size('sm')
                            ->color('primary')
                            ->icon('heroicon-m-calendar-days')
                            ->copyable()
                            ->copyMessage('Batch code copied')
                            ->searchable()
                            ->grow(false),

                        Tables\Columns\TextColumn::make('workflow_status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'scheduled' => 'Scheduled',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                default => ucfirst($state),
                            })
                            ->color(fn (string $state) => match ($state) {
                                'scheduled' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->grow(false),
                    ])->extraAttributes(['class' => 'fi-batch-card-header']),

                    // Row 2: Batch Name
                    Tables\Columns\TextColumn::make('interview_batch_name')
                        ->weight('bold')
                        ->size('lg')
                        ->searchable()
                        ->extraAttributes([
                            'style' => 'margin-top: 0.15rem; color: #0f172a;',
                            'class' => 'dark:text-white',
                        ]),

                    // Row 3: Schedule & Location Box
                    Tables\Columns\TextColumn::make('schedule_card')
                        ->html()
                        ->state(fn ($record) => static::getScheduleHtml($record)),

                    // Row 4: Capacity & Progress Meter
                    Tables\Columns\TextColumn::make('capacity_meter')
                        ->html()
                        ->state(fn ($record) => static::getCapacityMeterHtml($record)),

                    // Row 5: Candidate Breakdown Pills
                    Tables\Columns\TextColumn::make('candidate_pills')
                        ->html()
                        ->state(fn ($record) => static::getCandidatePillsHtml($record)),
                ])->space(3),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('capacity_status')
                    ->label('Capacity Status')
                    ->options([
                        'open' => 'Open (Slots available)',
                        'full' => 'Full',
                    ]),
                Tables\Filters\SelectFilter::make('workflow_status')
                    ->label('Workflow Status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])

            ->actions([
                // Corner edit button (like candidate card)
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('gray')
                    ->tooltip('Edit Batch')
                    ->extraAttributes(['class' => 'fi-ta-card-edit-btn']),

                // Quick action to view all candidates assigned to this batch
                Tables\Actions\Action::make('viewCandidates')
                    ->label(fn ($record) => 'Candidates (' . $record->assignments()->count() . ')')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->button()
                    ->modalHeading(fn ($record) => 'Candidates in ' . $record->interview_batch_name)
                    ->modalContent(fn ($record) => view('filament.modals.batch-candidates', [
                        'batch' => $record,
                        'assignments' => $record->assignments()->with('application')->get(),
                    ]))
                    ->modalSubmitAction(false),

                // Jump to Candidates Board with batch pre-filtered
                Tables\Actions\Action::make('openInBoard')
                    ->label('Open Board')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->button()
                    ->color('gray')
                    ->url(fn ($record) => \App\Filament\Resources\CandidateManagement\CandidateResource::getUrl('index', [
                        'tableFilters' => [
                            'interview_batch_id' => [
                                'value' => $record->id,
                            ],
                        ],
                        'activeTab' => 'interview_scheduled',
                    ])),

                // Delete batch action (visible when no candidates) - as a clean icon button
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->color('danger')
                    ->tooltip('Delete Batch')
                    ->visible(fn ($record) => $record->assignments()->count() === 0)
                    ->extraAttributes(['class' => 'fi-batch-delete-btn']),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getScheduleHtml($record): \Illuminate\Support\HtmlString
    {
        $date = $record->scheduled_date ? \Carbon\Carbon::parse($record->scheduled_date) : null;
        $dateFormatted = $date ? $date->format('M d, Y') : 'Date TBD';
        $dayOfWeek = $date ? $date->format('D') : '';

        // Relative badge
        $relativeBadge = '';
        if ($date) {
            if ($date->isToday()) {
                $relativeBadge = '<span class="fi-batch-rel-today">Today</span>';
            } elseif ($date->isTomorrow()) {
                $relativeBadge = '<span class="fi-batch-rel-tomorrow">Tomorrow</span>';
            } elseif ($date->isFuture()) {
                $days = (int) ceil(now()->diffInDays($date, false));
                $relativeBadge = '<span class="fi-batch-rel-future">In ' . ($days <= 1 ? '1 day' : $days . ' days') . '</span>';
            } else {
                $relativeBadge = '<span class="fi-batch-rel-past">Passed</span>';
            }
        }

        $startTime = $record->start_time ? \Carbon\Carbon::parse($record->start_time)->format('h:i A') : '';
        $endTime = $record->end_time ? \Carbon\Carbon::parse($record->end_time)->format('h:i A') : '';
        $timeRange = ($startTime && $endTime) ? "{$startTime} – {$endTime}" : ($startTime ?: 'Time TBD');

        // Duration calculation
        $duration = '';
        if ($record->start_time && $record->end_time) {
            $startC = \Carbon\Carbon::parse($record->start_time);
            $endC = \Carbon\Carbon::parse($record->end_time);
            $diffMins = (int) round(abs($startC->diffInMinutes($endC)));
            if ($diffMins >= 60) {
                $hours = floor($diffMins / 60);
                $rem = $diffMins % 60;
                $duration = $rem > 0 ? "{$hours}h {$rem}m" : "{$hours} hr";
            } elseif ($diffMins > 0) {
                $duration = "{$diffMins} min";
            }
        }

        $rawLocation = $record->interview_location ?: 'Online / Techstrota';
        $isOnline = str_contains(strtolower($rawLocation), 'online') || str_contains(strtolower($rawLocation), 'meet') || str_contains(strtolower($rawLocation), 'zoom');
        $locationIcon = $isOnline
            ? '<svg class="fi-batch-item-icon text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>'
            : '<svg class="fi-batch-item-icon text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>';

        $typeBadge = $isOnline
            ? '<span class="fi-batch-type-online">Online</span>'
            : '<span class="fi-batch-type-onsite">On-site</span>';

        return new \Illuminate\Support\HtmlString('
            <div class="fi-batch-schedule-card">
                <!-- Row 1: Date & Relative Tag -->
                <div class="fi-batch-schedule-item">
                    <svg class="fi-batch-item-icon text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <div class="fi-batch-item-content">
                        <div class="fi-batch-item-title">
                            <span class="font-semibold whitespace-nowrap">' . e($dateFormatted) . '</span>
                            ' . ($dayOfWeek ? '<span class="fi-batch-item-weekday">· ' . e($dayOfWeek) . '</span>' : '') . '
                        </div>
                        ' . $relativeBadge . '
                    </div>
                </div>

                <!-- Row 2: Timing & Duration + Type Badge -->
                <div class="fi-batch-schedule-item">
                    <svg class="fi-batch-item-icon text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <div class="fi-batch-item-content">
                        <div class="fi-batch-time-group">
                            <span class="fi-batch-time-text font-medium">' . e($timeRange) . '</span>
                            ' . ($duration ? '<span class="fi-batch-duration-badge">' . e($duration) . '</span>' : '') . '
                        </div>
                        ' . $typeBadge . '
                    </div>
                </div>

                <!-- Row 3: Venue Full Width with Tooltip and 2-line clamp -->
                <div class="fi-batch-schedule-item fi-batch-location-row">
                    ' . $locationIcon . '
                    <div class="fi-batch-location-wrapper">
                        <span class="fi-batch-location-text" title="' . e($rawLocation) . '">' . e($rawLocation) . '</span>
                    </div>
                </div>
            </div>
        ');
    }

    public static function getCapacityMeterHtml($record): \Illuminate\Support\HtmlString
    {
        $assigned = $record->assignments()->count();
        $total = (int) ($record->batch_size ?: 1);
        $percentage = min(100, round(($assigned / max(1, $total)) * 100));
        $remaining = max(0, $total - $assigned);

        $fillColor = $percentage >= 100 ? '#ef4444' : ($percentage >= 75 ? '#f59e0b' : '#10b981');
        $statusText = $remaining === 0 ? 'Batch Full' : "{$remaining} slots left";
        $statusClass = $remaining === 0 ? 'fi-batch-cap-full' : 'fi-batch-cap-open';

        return new \Illuminate\Support\HtmlString('
            <div class="fi-batch-capacity-box">
                <div class="fi-batch-capacity-header">
                    <div class="fi-batch-capacity-left">
                        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <span class="fi-batch-capacity-title">Capacity:</span>
                        <span class="fi-batch-capacity-nums"><strong>' . $assigned . '</strong> / ' . $total . '</span>
                    </div>
                    <span class="' . $statusClass . '">' . $statusText . ' (' . $percentage . '%)</span>
                </div>
                <div class="fi-batch-progress-track">
                    <div class="fi-batch-progress-bar" style="width: ' . $percentage . '%; background-color: ' . $fillColor . ';"></div>
                </div>
            </div>
        ');
    }

    public static function getCandidatePillsHtml($record): \Illuminate\Support\HtmlString
    {
        $assignments = $record->assignments;
        $total = $assignments->count();

        if ($total === 0) {
            return new \Illuminate\Support\HtmlString('
                <div class="fi-batch-empty-pills">
                    <span class="text-xs text-gray-400 italic">No candidates assigned yet</span>
                </div>
            ');
        }

        $present = $assignments->where('attendance', 'present')->count();
        $absent = $assignments->where('attendance', 'absent')->count();
        $selected = $assignments->where('result', 'selected')->count();
        $rejected = $assignments->where('result', 'rejected')->count();
        $pending = $assignments->whereNull('attendance')->count();

        $pills = [];
        $pills[] = '<span class="fi-batch-pill fi-batch-pill-total" title="Total Assigned Candidates">👥 ' . $total . ' Assigned</span>';

        if ($present > 0) {
            $pills[] = '<span class="fi-batch-pill fi-batch-pill-present" title="Present in Interview">✅ ' . $present . ' Present</span>';
        }
        if ($absent > 0) {
            $pills[] = '<span class="fi-batch-pill fi-batch-pill-absent" title="Absent from Interview">❌ ' . $absent . ' Absent</span>';
        }
        if ($pending > 0) {
            $pills[] = '<span class="fi-batch-pill fi-batch-pill-pending" title="Awaiting Evaluation">⏳ ' . $pending . ' Pending</span>';
        }
        if ($selected > 0) {
            $pills[] = '<span class="fi-batch-pill fi-batch-pill-selected" title="Shortlisted / Selected">⭐ ' . $selected . ' Selected</span>';
        }
        if ($rejected > 0) {
            $pills[] = '<span class="fi-batch-pill fi-batch-pill-rejected" title="Rejected">✗ ' . $rejected . ' Rejected</span>';
        }

        return new \Illuminate\Support\HtmlString('
            <div class="fi-batch-pills-row">
                ' . implode('', $pills) . '
            </div>
        ');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterviewBatches::route('/'),
            'create' => Pages\CreateInterviewBatch::route('/create'),
            'edit' => Pages\EditInterviewBatch::route('/{record}/edit'),
        ];
    }
}
