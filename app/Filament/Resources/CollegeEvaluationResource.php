<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollegeEvaluationResource\Pages;
use App\Models\ReportManagement\CollegeEvaluation;
use App\Models\ReportManagement\College;
use App\Models\ReportManagement\CollegeEvaluationIntern;
use App\Models\InternManagement\Intern;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Select, DatePicker, Section, Grid, Textarea, Hidden, Repeater};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn};
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CollegeEvaluationResource extends Resource
{
    protected static ?string $model = CollegeEvaluation::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'College Evaluations';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?int $navigationSort = 23;

    public static function syncDefaultInstructions(Forms\Set $set, Forms\Get $get): void
    {
        $mode = $get('evaluation_mode') ?: 'direct';
        $custom = $get('custom_criteria') ?: [];
        $notes = CollegeEvaluation::buildDefaultInstructions(
            $get('max_attendance_marks') ?? ($mode === 'direct' ? 0 : 20),
            $get('max_task_marks') ?? ($mode === 'direct' ? 0 : 40),
            $get('total_marks') ?? ($mode === 'direct' ? 50 : 100),
            $custom,
            $mode
        );
        $set('instruction_notes', $notes);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(['default' => 1, 'lg' => 12])->schema([

                // ── 1. College & Batch Profile ──
                Section::make('College & Batch Information')
                    ->description('Select the institution and enter the specific internship batch title and duration.')
                    ->icon('heroicon-o-building-library')
                    ->columnSpan(['default' => 12, 'lg' => 7])
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('college_id')
                                ->label('Select College')
                                ->relationship('college', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->createOptionForm([
                                    TextInput::make('name')
                                         ->label('College / Institution Name')
                                         ->required()
                                         ->placeholder('e.g. Silver Oak College of Engineering & Technology'),
                                    TextInput::make('university_name')
                                         ->label('Affiliated University')
                                         ->placeholder('e.g. Gujarat Technological University (GTU)'),
                                    TextInput::make('address')
                                         ->label('Address')
                                         ->placeholder('e.g. Ahmedabad, Gujarat'),
                                ])
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $college = College::find($state);
                                        if ($college) {
                                            $set('college_name', $college->name);
                                            $set('university_name', $college->university_name);
                                        }
                                    }
                                })
                                ->columnSpanFull(),

                            Hidden::make('college_name'),

                            TextInput::make('batch_title')
                                ->label('Internship Batch / Cohort Name')
                                ->required()
                                ->placeholder('e.g. June 2026 Summer Training / Batch A')
                                ->columnSpanFull(),

                            Select::make('duration')
                                ->label('Internship Duration')
                                ->options([
                                    '1 Month'   => '1 Month Internship',
                                    '2 Months'  => '2 Months Internship',
                                    '3 Months'  => '3 Months Internship',
                                    '6 Months'  => '6 Months Internship / Semester Capstone',
                                    '1 Year'    => '1 Year Industrial Fellowship',
                                    'Custom'    => 'Custom Duration',
                                ])
                                ->default('1 Month')
                                ->required()
                                ->native(false),

                            TextInput::make('academic_year')
                                ->label('Academic Session / Year')
                                ->placeholder('e.g. 2025–2026 / Semester VIII')
                                ->default(now()->format('Y') . '–' . (now()->year + 1)),

                            TextInput::make('course_degree')
                                ->label('Course / Degree / Department')
                                ->placeholder('e.g. B.Tech Computer Engineering'),

                            DatePicker::make('evaluation_date')
                                ->label('Evaluation Date')
                                ->default(now())
                                ->native(false)
                                ->displayFormat('d/m/Y'),
                        ]),

                        Grid::make(2)->schema([
                            DatePicker::make('evaluation_period_start')
                                ->label('Batch Period Start Date (Optional)')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->helperText('Attendance & task date filtering start (leave blank for overall duration).'),

                            DatePicker::make('evaluation_period_end')
                                ->label('Batch Period End Date (Optional)')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->afterOrEqual('evaluation_period_start')
                                ->helperText('Attendance & task date filtering end (leave blank for overall duration).'),

                            Forms\Components\Toggle::make('is_archived')
                                ->label('Archive this Batch')
                                ->helperText('Mark this evaluation batch as archived / completed.')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ── 2. Evaluation Mode & Marking Scheme ──
                Section::make('Evaluation Mode & Marking Scheme')
                    ->description('Choose whether to award direct marks (out of 50, 100, etc.) or use criteria breakdown.')
                    ->icon('heroicon-o-calculator')
                    ->columnSpan(['default' => 12, 'lg' => 5])
                    ->schema([
                        Forms\Components\Radio::make('evaluation_mode')
                            ->label('Evaluation Mode')
                            ->options([
                                'direct'   => 'Direct Marks (Single mark entry out of 50, 100, etc. without criteria breakdown)',
                                'criteria' => 'Criteria Breakdown (Attendance + Task Work + Custom Criteria)',
                            ])
                            ->default('direct')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state === 'direct' && ((float)$get('total_marks') === 100.0 || empty($get('total_marks')))) {
                                    $set('total_marks', 50);
                                } elseif ($state === 'criteria' && (float)$get('total_marks') === 50.0) {
                                    $set('total_marks', 100);
                                }
                                static::syncDefaultInstructions($set, $get);
                            })
                            ->columnSpanFull(),

                        TextInput::make('total_marks')
                            ->label(fn (Forms\Get $get) => $get('evaluation_mode') === 'direct' ? 'Maximum Marks (e.g. 50 or 100)' : 'Total Marks')
                            ->numeric()
                            ->required()
                            ->default(50)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::syncDefaultInstructions($set, $get))
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->visible(fn (Forms\Get $get) => $get('evaluation_mode') === 'criteria')
                            ->schema([
                                TextInput::make('max_attendance_marks')
                                    ->label('Max Attendance Marks')
                                    ->numeric()
                                    ->required(fn (Forms\Get $get) => $get('evaluation_mode') === 'criteria')
                                    ->default(20)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::syncDefaultInstructions($set, $get)),

                                TextInput::make('max_task_marks')
                                    ->label('Max Task Marks')
                                    ->numeric()
                                    ->required(fn (Forms\Get $get) => $get('evaluation_mode') === 'criteria')
                                    ->default(40)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::syncDefaultInstructions($set, $get)),
                            ]),

                        Repeater::make('custom_criteria')
                            ->label('Additional Evaluation Criteria (+)')
                            ->visible(fn (Forms\Get $get) => $get('evaluation_mode') === 'criteria')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::syncDefaultInstructions($set, $get))
                            ->schema([
                                TextInput::make('name')
                                    ->label('Criterion Name')
                                    ->placeholder('e.g. Weekly Report, Viva, Presentation, Discipline')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::syncDefaultInstructions($set, $get))
                                    ->columnSpan(['default' => 12, 'md' => 7]),

                                TextInput::make('max_marks')
                                    ->label('Max Marks')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('e.g. 40')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => static::syncDefaultInstructions($set, $get))
                                    ->columnSpan(['default' => 12, 'md' => 5]),
                            ])
                            ->columns(['default' => 1, 'md' => 12])
                            ->addActionLabel('＋ Add Evaluation Criterion')
                            ->default([
                                ['name' => 'Weekly / Monthly Report', 'max_marks' => 40],
                            ])
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['name'] ?? null) ? "{$state['name']} ({$state['max_marks']} M)" : 'Criterion'),
                    ]),

                // ── 3. Table & PDF Column Setup ──
                Section::make('Table & PDF Column Setup')
                    ->description('Customize, reorder, add, or delete any columns for the evaluation table and PDF report.')
                    ->icon('heroicon-o-view-columns')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('columns_config.columns')
                            ->label('Table & PDF Columns (Drag to Reorder, Rename, or Delete)')
                            ->helperText('Arrange columns in the exact sequence you want them to appear in the table and PDF. Click and drag using the handle (⠿) or use the arrow buttons to reorder. Click the trash icon (🗑️) to delete any column you do not need.')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 12])->schema([
                                    Select::make('type')
                                        ->label('Column Type')
                                        ->options([
                                            'sr_no'               => '🔢 Serial Number (Sr. No.)',
                                            'student_id'          => '🆔 Student ID / Enrollment No',
                                            'student_name'        => '👤 Student Full Name',
                                            'custom_text'         => '📝 Custom Text Column (e.g. Course/Module, Project)',
                                            'grade'               => '🏆 Grade (Letter Grade)',
                                            'marks'               => '🎯 Marks (Direct / Total Marks)',
                                            'criteria_attendance' => '📅 Attendance Marks',
                                            'criteria_task'       => '💻 Task Work Marks',
                                            'custom_criterion'    => '📋 Custom Criterion Marks',
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                            $currLabel = $get('label');
                                            $defaults = ['Sr. No.', 'Student ID', 'Student Name', 'Course Name/ Module Name', 'Grade', 'Marks', 'Attendance', 'Task Work', 'Custom Criterion', 'Weekly Report'];
                                            if (blank($currLabel) || in_array($currLabel, $defaults)) {
                                                match ($state) {
                                                    'sr_no'               => $set('label', 'Sr. No.'),
                                                    'student_id'          => $set('label', 'Student ID'),
                                                    'student_name'        => $set('label', 'Student Name'),
                                                    'custom_text'         => $set('label', 'Course Name/ Module Name'),
                                                    'grade'               => $set('label', 'Grade'),
                                                    'marks'               => $set('label', 'Marks'),
                                                    'criteria_attendance' => $set('label', 'Attendance'),
                                                    'criteria_task'       => $set('label', 'Task Work'),
                                                    'custom_criterion'    => $set('label', 'Weekly Report'),
                                                    default               => null,
                                                };
                                            }
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 3]),

                                    TextInput::make('label')
                                        ->label('Column Header Name')
                                        ->placeholder('e.g. Enrollment No, Student Name, etc.')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->columnSpan([
                                            'default' => 12,
                                            'md' => fn (Forms\Get $get) => in_array($get('type'), ['custom_text', 'custom_criterion']) ? 4 : 7,
                                        ]),

                                    TextInput::make('default_value')
                                        ->label('Default Value (Optional)')
                                        ->placeholder('e.g. Web Development Module')
                                        ->visible(fn (Forms\Get $get) => $get('type') === 'custom_text')
                                        ->columnSpan(['default' => 12, 'md' => 3]),

                                    TextInput::make('name')
                                        ->label('Criterion Name (Must match scheme)')
                                        ->placeholder('e.g. Weekly Report')
                                        ->visible(fn (Forms\Get $get) => $get('type') === 'custom_criterion')
                                        ->columnSpan(['default' => 12, 'md' => 3]),

                                    TextInput::make('width')
                                        ->label('Width %')
                                        ->placeholder('Auto')
                                        ->numeric()
                                        ->minValue(3)
                                        ->maxValue(80)
                                        ->columnSpan(['default' => 12, 'md' => 2]),
                                ]),
                            ])
                            ->addActionLabel('＋ Add Column')
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->deletable()
                            ->collapsible()
                            ->default([
                                ['type' => 'sr_no',        'label' => 'Sr. No.'],
                                ['type' => 'student_id',   'label' => 'Student ID'],
                                ['type' => 'student_name', 'label' => 'Student Name'],
                                ['type' => 'custom_text',  'label' => 'Course Name/ Module Name', 'default_value' => ''],
                                ['type' => 'grade',        'label' => 'Grade'],
                                ['type' => 'marks',        'label' => 'Marks'],
                            ])
                            ->itemLabel(fn (array $state): ?string => filled($state['label'] ?? null)
                                ? "{$state['label']}  [" . strtoupper(str_replace('_', ' ', $state['type'] ?? 'column')) . "]"
                                : 'Column'),

                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            Select::make('columns_config.pdf_settings.font_size')
                                ->label('PDF Table Font Size')
                                ->options([
                                    'compact' => 'Compact (10.5px) — For 8+ columns',
                                    'normal'  => 'Standard / Normal (12px) — Recommended',
                                    'large'   => 'Large (13px) — Highly legible',
                                    'xlarge'  => 'Extra Large (14px)',
                                ])
                                ->default('normal')
                                ->native(false)
                                ->helperText('Table data font size. Titles remain bold; row details are regular weight.'),

                            Select::make('columns_config.pdf_settings.spacing')
                                ->label('PDF Table Row Spacing')
                                ->options([
                                    'compact'  => 'Compact (5px padding)',
                                    'normal'   => 'Standard (8px padding) — Recommended',
                                    'spacious' => 'Spacious (10px padding)',
                                ])
                                ->default('normal')
                                ->native(false)
                                ->helperText('Vertical padding of table cells in PDF report.'),
                        ]),
                    ]),

                // ── 4. Target Interns Selection ──
                Section::make('Enrolled Interns for this Batch')
                    ->description('Select the interns who participated in this specific internship batch.')
                    ->icon('heroicon-o-user-group')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('intern_ids')
                            ->label('Select Interns')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateCollegeEvaluation)
                            ->dehydrated(false)
                            ->options(function (Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                $collegeId = $get('college_id');
                                $college = $collegeId ? College::find($collegeId) : null;
                                $collegeName = trim((string) ($college?->name ?? $get('college_name') ?? ''));
                                $univName = trim((string) ($college?->university_name ?? $get('university_name') ?? ''));

                                $allActiveInterns = Intern::active()->with(['batch', 'team'])->orderBy('name')->get();
                                $archivedInterns = Intern::archived()->with(['batch', 'team'])->orderBy('name')->get();

                                $isDirectCollegeMatch = function ($intern) use ($collegeName) {
                                    if ($collegeName === '') {
                                        return false;
                                    }
                                    $iClg  = strtolower(trim((string) ($intern->college ?? '')));
                                    $iUniv = strtolower(trim((string) ($intern->university ?? '')));
                                    $tClg  = strtolower(trim($collegeName));

                                    if ($tClg !== '') {
                                        if ($iClg !== '' && ($iClg === $tClg || str_contains($iClg, $tClg) || str_contains($tClg, $iClg))) {
                                            return true;
                                        }
                                        if ($iClg === '' && $iUniv !== '' && ($iUniv === $tClg || str_contains($iUniv, $tClg) || str_contains($tClg, $iUniv))) {
                                            return true;
                                        }
                                    }

                                    return false;
                                };

                                $options = [];

                                if ($collegeName !== '' || $univName !== '') {
                                    $matchingActive   = $allActiveInterns->filter($isDirectCollegeMatch);
                                    $otherActive      = $allActiveInterns->reject($isDirectCollegeMatch);
                                    $matchingArchived = $archivedInterns->filter($isDirectCollegeMatch);

                                    $targetLabel = $collegeName ?: $univName;

                                    if ($matchingActive->count() > 0) {
                                        $groupTitle = "🟢 Active Interns — {$targetLabel}";
                                        foreach ($matchingActive as $i) {
                                            $code = $i->intern_code ?: "ID-{$i->id}";
                                            $batch = $i->batch?->batch_name ? " • {$i->batch->batch_name}" : '';
                                            $options[$groupTitle][$i->id] = "{$i->name} ({$code}){$batch}";
                                        }
                                    }

                                    if ($otherActive->count() > 0) {
                                        $groupTitle = '👤 Other Current Active Interns';
                                        foreach ($otherActive as $i) {
                                            $code = $i->intern_code ?: "ID-{$i->id}";
                                            $clg = $i->college ? " • {$i->college}" : '';
                                            $options[$groupTitle][$i->id] = "{$i->name} ({$code}){$clg}";
                                        }
                                    }

                                    if ($matchingArchived->count() > 0) {
                                        $groupTitle = "📁 Archived / Past Cohorts — {$targetLabel}";
                                        foreach ($matchingArchived as $i) {
                                            $code = $i->intern_code ?: "ID-{$i->id}";
                                            $cohort = $i->cohort_archive_name ? " • {$i->cohort_archive_name}" : ' • Archived';
                                            $options[$groupTitle][$i->id] = "[Archived] {$i->name} ({$code}){$cohort}";
                                        }
                                    }
                                } else {
                                    $groupTitle = '🟢 Current Active Interns';
                                    foreach ($allActiveInterns as $i) {
                                        $code = $i->intern_code ?: "ID-{$i->id}";
                                        $clg = $i->college ? " • {$i->college}" : '';
                                        $options[$groupTitle][$i->id] = "{$i->name} ({$code}){$clg}";
                                    }

                                    if ($archivedInterns->count() > 0) {
                                        $groupTitle = '📁 Archived Interns';
                                        foreach ($archivedInterns as $i) {
                                            $code = $i->intern_code ?: "ID-{$i->id}";
                                            $cohort = $i->cohort_archive_name ? " • {$i->cohort_archive_name}" : ' • Archived';
                                            $options[$groupTitle][$i->id] = "[Archived] {$i->name} ({$code}){$cohort}";
                                        }
                                    }
                                }

                                // Ensure already enrolled interns on edit are present in options
                                if ($record && method_exists($record, 'evaluationInterns')) {
                                    $enrolledInterns = $record->evaluationInterns()->with('intern')->get();
                                    $flatKnownIds = [];
                                    foreach ($options as $group) {
                                        if (is_array($group)) {
                                            $flatKnownIds = array_merge($flatKnownIds, array_keys($group));
                                        }
                                    }
                                    foreach ($enrolledInterns as $ei) {
                                        if ($ei->intern && !in_array($ei->intern_id, $flatKnownIds)) {
                                            $i = $ei->intern;
                                            $code = $i->intern_code ?: "ID-{$i->id}";
                                            $tag = (!$i->is_active || filled($i->cohort_archive_name)) ? ' [Archived]' : '';
                                            $options['📋 Currently Enrolled in this Evaluation'][$i->id] = "{$i->name} ({$code}){$tag}";
                                        }
                                    }
                                }

                                return $options;
                            })
                            ->helperText('Active interns for this college are grouped at the top. You can also select any other current active intern.'),
                    ]),

                // ── 4. Calculation Instructions & Legend ──
                Section::make('Evaluation Methodology & Instructions (Printed in PDF)')
                    ->description('Explanation printed below the PDF table explaining how marks were calculated.')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('instruction_notes')
                            ->label('Marking & Grading Instructions')
                            ->rows(8)
                            ->default(fn (Forms\Get $get) => CollegeEvaluation::buildDefaultInstructions(
                                $get('max_attendance_marks') ?? 0,
                                $get('max_task_marks') ?? 0,
                                $get('total_marks') ?: 50,
                                $get('custom_criteria') ?: [],
                                $get('evaluation_mode') ?: 'direct'
                            ))
                            ->hintAction(
                                Forms\Components\Actions\Action::make('generate_default_instructions')
                                    ->label('⚡ Reset / Auto-generate from Scheme')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Click to update this text based on your Total, Attendance, Task, and Additional Criteria above.')
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        static::syncDefaultInstructions($set, $get);
                                        Notification::make()
                                            ->title('Instructions Updated')
                                            ->body('Marking and grading instructions have been auto-generated from your criteria scheme.')
                                            ->success()
                                            ->send();
                                    })
                            )
                            ->helperText('Customizable instructions that print at the bottom of the generated PDF. You can edit this text directly or click "Reset / Auto-generate from Scheme" above.'),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_title')
                    ->label('Batch & College')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state ?: 'General Evaluation Batch')
                    ->description(fn (CollegeEvaluation $record) => ($record->college?->name ?? $record->college_name) . ($record->academic_year ? " • {$record->academic_year}" : '')),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ?: '1 Month'),

                TextColumn::make('total_marks')
                    ->label('Scheme')
                    ->formatStateUsing(function (CollegeEvaluation $record) {
                        $tot = rtrim(rtrim(number_format((float)$record->total_marks, 2), '0'), '.');
                        if ($record->isDirectMode()) {
                            return "{$tot} M (Direct Marks)";
                        }
                        $att = rtrim(rtrim(number_format((float)$record->max_attendance_marks, 2), '0'), '.');
                        $task = rtrim(rtrim(number_format((float)$record->max_task_marks, 2), '0'), '.');
                        $parts = ["Att: {$att}", "Task: {$task}"];
                        foreach ($record->getCustomCriteriaList() as $crit) {
                            $cM = rtrim(rtrim(number_format((float)$crit['max_marks'], 2), '0'), '.');
                            $parts[] = "{$crit['name']}: {$cM}";
                        }
                        return "{$tot} M (" . implode(' | ', $parts) . ')';
                    })
                    ->badge()
                    ->color(fn (CollegeEvaluation $record) => $record->isDirectMode() ? 'success' : 'warning'),

                TextColumn::make('interns_count')
                    ->label('Interns')
                    ->counts('evaluationInterns')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('evaluation_date')
                    ->label('Evaluation Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('is_archived')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Archived' : 'Active')
                    ->color(fn (bool $state) => $state ? 'warning' : 'success')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Archive Status')
                    ->placeholder('All Batches')
                    ->trueLabel('Archived Batches Only')
                    ->falseLabel('Active Batches Only'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (CollegeEvaluation $record) => route('college-evaluations.preview-pdf', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (CollegeEvaluation $record) => route('college-evaluations.download-pdf', $record->id)),


                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Archive Evaluation Batch')
                    ->modalDescription('Are you sure you want to move this batch to archive?')
                    ->visible(fn (CollegeEvaluation $record) => !$record->is_archived)
                    ->action(function (CollegeEvaluation $record) {
                        $record->archive();
                        Notification::make()->title('Batch Archived')->body("'{$record->batch_title}' has been moved to Archive.")->info()->send();
                    }),

                Tables\Actions\Action::make('unarchive')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Evaluation Batch')
                    ->modalDescription('Restore this batch back to active cohorts?')
                    ->visible(fn (CollegeEvaluation $record) => (bool) $record->is_archived)
                    ->action(function (CollegeEvaluation $record) {
                        $record->unarchive();
                        Notification::make()->title('Batch Restored')->body("'{$record->batch_title}' has been restored to Active batches.")->success()->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->recordUrl(fn (CollegeEvaluation $record) => static::getUrl('view', ['record' => $record]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Downloads the formatted PDF evaluation report.
     */
    public static function downloadPdf(CollegeEvaluation $record, array $options = []): StreamedResponse|null
    {
        $record->refresh();
        $evaluationInterns = $record->evaluationInterns()->with(['intern.batch', 'intern.team'])->orderBy('sort_order')->orderBy('id')->get();

        if ($evaluationInterns->isEmpty()) {
            Notification::make()
                ->title('No Interns to Export')
                ->body('There are no interns evaluated under this college report yet.')
                ->warning()
                ->send();
            return null;
        }

        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $watermarkPath = public_path('images/TsLogo_watermark_blurred.png');
        $watermarkBase64 = file_exists($watermarkPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath))
            : $logoBase64;

        $pdf = Pdf::loadView('pdf.college-evaluation-report', [
            'evaluation'        => $record,
            'evaluationInterns' => $evaluationInterns,
            'logoBase64'        => $logoBase64,
            'watermarkBase64'   => $watermarkBase64,
            'pdfOptions'        => $options,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $collegeName = $record->college?->name ?? $record->college_name;
        $cleanCollege = str($collegeName)->slug()->toString();
        $cleanBatch = str($record->batch_title ?: 'Evaluation')->slug()->toString();
        $fileName = "Evaluation_{$cleanCollege}_{$cleanBatch}_" . now()->format('Ymd') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Streams the PDF directly inline in the browser (for preview iframe or new tab).
     */
    public static function streamPdf(CollegeEvaluation $record, array $options = []): \Illuminate\Http\Response|StreamedResponse|null
    {
        $record->refresh();
        $evaluationInterns = $record->evaluationInterns()->with(['intern.batch', 'intern.team'])->orderBy('sort_order')->orderBy('id')->get();

        if ($evaluationInterns->isEmpty()) {
            return response('No interns evaluated under this batch yet.', 404);
        }

        $logoPath = public_path('images/TsLogo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $watermarkPath = public_path('images/TsLogo_watermark_blurred.png');
        $watermarkBase64 = file_exists($watermarkPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($watermarkPath))
            : $logoBase64;

        $pdf = Pdf::loadView('pdf.college-evaluation-report', [
            'evaluation'        => $record,
            'evaluationInterns' => $evaluationInterns,
            'logoBase64'        => $logoBase64,
            'watermarkBase64'   => $watermarkBase64,
            'pdfOptions'        => $options,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $collegeName = $record->college?->name ?? $record->college_name;
        $cleanCollege = str($collegeName)->slug()->toString();
        $cleanBatch = str($record->batch_title ?: 'Evaluation')->slug()->toString();
        $fileName = "Evaluation_{$cleanCollege}_{$cleanBatch}_" . now()->format('Ymd') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCollegeEvaluations::route('/'),
            'create' => Pages\CreateCollegeEvaluation::route('/create'),
            'view'   => Pages\ViewCollegeEvaluation::route('/{record}'),
            'edit'   => Pages\EditCollegeEvaluation::route('/{record}/edit'),
        ];
    }
}
