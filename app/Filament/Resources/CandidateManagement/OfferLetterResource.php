<?php

namespace App\Filament\Resources\CandidateManagement;

use App\Filament\Resources\CandidateManagement\OfferLetterResource\Pages;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InterviewManagement\OfferLetterTemplate;
use App\Models\InterviewManagement\Application;
use App\Models\InternManagement\Intern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{Select, DatePicker, TextInput, Textarea, RichEditor, Section};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, BulkAction};
use Filament\Tables\Columns\{TextColumn, ToggleColumn, BadgeColumn, IconColumn};
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Carbon;

use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use App\Mail\InternWelcomeMail;
use Illuminate\Support\Facades\Mail;

class OfferLetterResource extends Resource
{
    protected static ?string $model = OfferLetter::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Candidate Management';
    protected static ?string $modelLabel = 'Offer Letter';
    protected static ?string $pluralModelLabel = 'Offer Letters';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ─── STEP 1: Template & Preset ───────────────────────────────
                Forms\Components\Section::make('Offer Letter Template & Preset')
                    ->schema([
                        Forms\Components\Grid::make(1)->schema([
                            Select::make('offer_letter_template_id')
                                ->label('Saved Template Preset')
                                ->placeholder('Select a saved template preset (optional)...')
                                ->options(fn () => OfferLetterTemplate::orderBy('name')->get()->mapWithKeys(
                                    fn (OfferLetterTemplate $t) => [$t->id => $t->summary_label]
                                ))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->helperText('Select a saved template to auto-fill duration, layout, role, position, dates, working hours, and description.')
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if (!$state) return;
                                    $preset = OfferLetterTemplate::find($state);
                                    if ($preset) {
                                        $set('template', $preset->template ?: 'single_page');
                                        $set('duration', $preset->duration ?: '1 month');
                                        $set('internship_role', $preset->internship_role);
                                        $set('internship_position', $preset->internship_position);
                                        $set('joining_date', $preset->joining_date?->format('Y-m-d'));
                                        $set('completion_date', $preset->completion_date?->format('Y-m-d'));
                                        $set('working_hours', $preset->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday');
                                        $set('offer_issue_date', $preset->offer_issue_date?->format('Y-m-d'));
                                        $set('description', $preset->description);
                                        self::updateCompletionDate($set, $get);
                                    }
                                })
                                ->columnSpanFull(),
                        ]),

                        Forms\Components\Placeholder::make('selected_preset_summary')
                            ->label('Selected Template Preset Details')
                            ->visible(fn (Get $get) => filled($get('offer_letter_template_id')))
                            ->content(function (Get $get) {
                                $preset = OfferLetterTemplate::withCount([
                                    'offerLetters as total_count',
                                    'offerLetters as draft_count' => fn ($q) => $q->where('offer_status', 'draft'),
                                    'offerLetters as accepted_count' => fn ($q) => $q->where('offer_status', 'accepted'),
                                ])->find($get('offer_letter_template_id'));

                                if (!$preset) {
                                    return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-500">Template not found.</span>');
                                }

                                $layout = e($preset->layout_label);
                                $duration = e($preset->duration_label);
                                $role = e($preset->internship_role ?: '—');
                                $pos = e($preset->internship_position ?: '—');
                                $join = $preset->joining_date ? $preset->joining_date->format('d M Y') : '—';
                                $comp = $preset->completion_date ? $preset->completion_date->format('d M Y') : '—';
                                $issue = $preset->offer_issue_date ? $preset->offer_issue_date->format('d M Y') : '—';
                                $hours = e($preset->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday');

                                return new \Illuminate\Support\HtmlString("
                                    <div style='background-color: #0c1c38; border: 1px solid #1d4ed8; border-radius: 10px; padding: 12px 14px; font-size: 12px; display: flex; flex-direction: column; gap: 8px;'>
                                        <div style='display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px;'>
                                            <span style='font-weight: 800; font-size: 13.5px; color: #60a5fa;'>🔖 " . e($preset->name) . "</span>
                                            <div style='display: flex; align-items: center; gap: 6px;'>
                                                <span style='padding: 2px 10px; border-radius: 9999px; background-color: rgba(37, 99, 235, 0.25); border: 1px solid rgba(96, 165, 250, 0.4); color: #93c5fd; font-weight: 700; font-size: 11px;'>⏱️ {$duration}</span>
                                                <span style='padding: 2px 10px; border-radius: 9999px; background-color: rgba(37, 99, 235, 0.25); border: 1px solid rgba(96, 165, 250, 0.4); color: #93c5fd; font-weight: 700; font-size: 11px;'>{$layout}</span>
                                            </div>
                                        </div>
                                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; color: #e2e8f0;'>
                                            <div><strong style='color: #94a3b8;'>Role:</strong> {$role}</div>
                                            <div><strong style='color: #94a3b8;'>Position:</strong> {$pos}</div>
                                            <div><strong style='color: #94a3b8;'>Duration:</strong> <span style='color: #60a5fa; font-weight: 600;'>{$duration}</span></div>
                                            <div><strong style='color: #94a3b8;'>Period:</strong> <span style='color: #60a5fa; font-weight: 600;'>{$join} → {$comp}</span></div>
                                            <div><strong style='color: #94a3b8;'>Issue Date:</strong> {$issue}</div>
                                            <div><strong style='color: #94a3b8;'>Working Hours:</strong> {$hours}</div>
                                        </div>
                                        <div style='padding-top: 6px; border-top: 1px solid rgba(59, 130, 246, 0.25); display: flex; flex-wrap: wrap; align-items: center; gap: 12px; font-size: 11px; color: #94a3b8;'>
                                            <span>📊 <strong style='color: #ffffff;'>{$preset->total_count}</strong> Linked Letters</span>
                                            <span style='color: #fbbf24;'>🟡 <strong>{$preset->draft_count}</strong> Drafts (auto-synced on template update)</span>
                                            <span style='color: #34d399;'>✅ <strong>{$preset->accepted_count}</strong> Accepted</span>
                                        </div>
                                    </div>
                                ");
                            })
                            ->columnSpanFull(),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('save_as_new_preset')
                                ->label('Save Current Form as New Template')
                                ->icon('heroicon-m-bookmark-square')
                                ->color('success')
                                ->size('sm')
                                ->modalHeading('Save Current Form as Template Preset')
                                ->modalDescription('Save the current layout, duration, role, position, dates, working hours, and description as a reusable template preset.')
                                ->form([
                                    TextInput::make('new_preset_name')
                                        ->label('Template Preset Name')
                                        ->placeholder('e.g. 1-Month Web Developer Batch A')
                                        ->required(),
                                ])
                                ->action(function (array $data, Set $set, Get $get) {
                                    $preset = OfferLetterTemplate::create([
                                        'name'                => $data['new_preset_name'],
                                        'template'            => $get('template') ?: 'single_page',
                                        'duration'            => $get('duration') ?: '1 month',
                                        'internship_role'     => $get('internship_role'),
                                        'internship_position' => $get('internship_position'),
                                        'joining_date'        => $get('joining_date'),
                                        'completion_date'     => $get('completion_date'),
                                        'working_hours'       => $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                                        'offer_issue_date'    => $get('offer_issue_date'),
                                        'description'         => $get('description'),
                                    ]);

                                    $set('offer_letter_template_id', $preset->id);

                                    Notification::make()
                                        ->title("Template '{$preset->name}' Saved")
                                        ->body('You can now reuse this template anytime to generate or update offer letters in bulk.')
                                        ->success()
                                        ->send();
                                }),

                            Forms\Components\Actions\Action::make('update_current_preset')
                                ->label('Update Selected Template & Sync Drafts')
                                ->icon('heroicon-m-arrow-path')
                                ->color('warning')
                                ->size('sm')
                                ->visible(fn (Get $get) => filled($get('offer_letter_template_id')))
                                ->modalHeading('Update Template Preset & Sync Draft Letters')
                                ->modalDescription('This will update the saved template with your current form fields and automatically update all Draft offer letters linked to this template.')
                                ->fillForm(function (Get $get) {
                                    $preset = OfferLetterTemplate::find($get('offer_letter_template_id'));
                                    return [
                                        'preset_name' => $preset?->name ?? '',
                                    ];
                                })
                                ->form([
                                    TextInput::make('preset_name')
                                        ->label('Template Preset Name')
                                        ->required(),
                                ])
                                ->action(function (array $data, Set $set, Get $get) {
                                    $presetId = $get('offer_letter_template_id');
                                    $preset = OfferLetterTemplate::find($presetId);
                                    if ($preset) {
                                        $preset->update([
                                            'name'                => $data['preset_name'] ?: $preset->name,
                                            'template'            => $get('template') ?: $preset->template,
                                            'duration'            => $get('duration') ?: $preset->duration,
                                            'internship_role'     => $get('internship_role'),
                                            'internship_position' => $get('internship_position'),
                                            'joining_date'        => $get('joining_date'),
                                            'completion_date'     => $get('completion_date'),
                                            'working_hours'       => $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                                            'offer_issue_date'    => $get('offer_issue_date'),
                                            'description'         => $get('description'),
                                        ]);

                                        $draftCount = $preset->offerLetters()->where('offer_status', 'draft')->count();

                                        Notification::make()
                                            ->title("Template '{$preset->name}' Updated")
                                            ->body("Template saved and synced across {$draftCount} linked draft offer letter(s).")
                                            ->success()
                                            ->send();
                                    }
                                }),

                            Forms\Components\Actions\Action::make('delete_current_preset')
                                ->label('Delete Template')
                                ->icon('heroicon-m-trash')
                                ->color('danger')
                                ->size('sm')
                                ->visible(fn (Get $get) => filled($get('offer_letter_template_id')))
                                ->requiresConfirmation()
                                ->modalHeading('Delete Template Preset')
                                ->modalDescription('Are you sure you want to delete this template preset? Existing offer letters will not be deleted.')
                                ->action(function (Set $set, Get $get) {
                                    $presetId = $get('offer_letter_template_id');
                                    $preset = OfferLetterTemplate::find($presetId);
                                    if ($preset) {
                                        $name = $preset->name;
                                        $preset->delete();
                                        $set('offer_letter_template_id', null);

                                        Notification::make()
                                            ->title("Template '{$name}' Deleted")
                                            ->success()
                                            ->send();
                                    }
                                }),
                        ])->columnSpanFull(),
                    ]),
 
                // ─── SECTION A: Application selector ──
                Section::make('Intern Selection')
                    ->description('Select one or multiple shortlisted candidates to generate/update their offer letters in bulk.')
                    ->schema([
                        Select::make('applications')
                            ->label('Select Interns / Candidates')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(function (Get $get, ?OfferLetter $record) {
                                $query = Application::where('status', 'shortlisted')
                                    ->whereNotNull('name')
                                    ->where('name', '!=', '')
                                    ->whereNotNull('id')
                                    ->with('offerLetter');

                                $query->where(function ($q) use ($record) {
                                    $q->whereDoesntHave('offerLetter')
                                        ->orWhereHas('offerLetter', function ($subQ) use ($record) {
                                            if ($record) {
                                                $subQ->where('id', $record->id);
                                            } else {
                                                // Allow selecting candidates who currently have a draft offer letter so their draft can be updated from template in bulk
                                                $subQ->where('offer_status', 'draft');
                                            }
                                        });
                                });

                                return $query->get()
                                    ->mapWithKeys(function ($app) {
                                        $name    = trim((string) ($app->name    ?? '')) ?: 'Unnamed Intern';
                                        $college = trim((string) ($app->college ?? '')) ?: 'No College Listed';
                                        $degree  = trim((string) ($app->degree  ?? '')) ?: 'No Degree Listed';
                                        $id      = (int) $app->id;
                                        $statusTag = $app->offerLetter ? ' [Has Draft]' : ' [New]';

                                        return [$id => "{$name} — {$college} ({$degree}){$statusTag}"];
                                    })
                                    ->filter(fn ($label, $id) => is_string($label) && $label !== '');
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                return Application::whereIn('id', $values)
                                    ->with('offerLetter')
                                    ->get()
                                    ->mapWithKeys(function ($app) {
                                        $name    = trim((string) ($app->name    ?? '')) ?: 'Unnamed Intern';
                                        $college = trim((string) ($app->college ?? '')) ?: 'No College Listed';
                                        $degree  = trim((string) ($app->degree  ?? '')) ?: 'No Degree Listed';

                                        return [(int) $app->id => "{$name} — {$college} ({$degree})"];
                                    })
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateHydrated(function (Set $set, ?OfferLetter $record, $state) {
                                if ($record) {
                                    $app = $record->application;
                                    $set('name',       $record->name ?: ($app?->name ?? ''));
                                    $set('university', $record->university ?: ($record->college ?: ($app?->college ?? '')));
                                    $set('college',    $record->college ?: ($app?->college ?? ''));
                                    $set('degree',     $record->degree ?: ($app?->degree ?? ''));
                                    $set('email',      $record->email ?: ($app?->email ?? ''));
                                    $set('phone',      $record->phone ?: ($app?->phone ?? ''));
                                } elseif (!empty($state)) {
                                    $app = Application::find(is_array($state) ? $state[0] : $state);
                                    if ($app) {
                                        $set('name',       $app->name);
                                        $set('university', $app->college);
                                        $set('college',    $app->college);
                                        $set('degree',     $app->degree);
                                        $set('email',      $app->email ?? null);
                                        $set('phone',      $app->phone ?? null);
                                    }
                                }
                            })
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (!empty($state) && count($state) === 1) {
                                    $app = Application::find($state[0]);
                                    if ($app) {
                                        $set('name',       $app->name);
                                        $set('university', $app->college);
                                        $set('college',    $app->college);
                                        $set('degree',     $app->degree);
                                        $set('email',      $app->email ?? null);
                                        $set('phone',      $app->phone ?? null);
                                    }
                                } else {
                                    $set('name',       null);
                                    $set('university', null);
                                    $set('college',    null);
                                    $set('degree',     null);
                                    $set('email',      null);
                                    $set('phone',      null);
                                }
                                self::updateCompletionDate($set, $get);
                            }),
                    ]),

                // ─── SECTION B: Intern details ──
                Section::make('Intern Details')
                    ->description(fn (Get $get) => count($get('applications') ?? []) > 1
                        ? 'Bulk candidate generation mode active.'
                        : 'Pre-filled from the selected application. All fields are editable.')
                    ->schema([
                        Forms\Components\Placeholder::make('bulk_candidates_notice')
                            ->label('Bulk Candidate Mode')
                            ->content(function (Get $get) {
                                $count = count($get('applications') ?? []);
                                return "✅ **{$count} Candidates Selected**: Each candidate will receive their own individual offer letter with their personal details (Name, College, University, Degree, Email, Phone) pre-filled from their application. The Internship Details and Letter Body below will be applied to all of them.";
                            })
                            ->visible(fn (Get $get) => count($get('applications') ?? []) > 1)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->visible(fn (Get $get) => count($get('applications') ?? []) <= 1)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Intern Full Name')
                                    ->dehydrated(true)
                                    ->placeholder('e.g. Rahul Sharma')
                                    ->required(fn (Get $get) => count($get('applications') ?? []) <= 1)
                                    ->live(debounce: 250)
                                    ->helperText('Pre-fills from the selected intern but can be changed.')
                                    ->regex('/^[a-zA-Z\s\.]+$/')
                                    ->validationMessages([
                                        'regex' => 'The name must only contain letters, spaces, and dots.',
                                    ]),

                                TextInput::make('college')
                                    ->label('College / Institution')
                                    ->dehydrated(true)
                                    ->live(debounce: 250)
                                    ->placeholder('e.g. M B Patel College of Engineering'),

                                TextInput::make('university')
                                    ->label('University')
                                    ->dehydrated(true)
                                    ->live(debounce: 250)
                                    ->placeholder('e.g. GTU'),

                                TextInput::make('degree')
                                    ->label('Degree')
                                    ->dehydrated(true)
                                    ->live(debounce: 250)
                                    ->placeholder('e.g. B.Tech / B.C.A.'),

                                TextInput::make('email')
                                    ->label('Intern Email')
                                    ->dehydrated(true)
                                    ->email()
                                    ->live(debounce: 250)
                                    ->placeholder('intern@example.com'),

                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->dehydrated(true)
                                    ->tel()
                                    ->maxLength(15)
                                    ->live(debounce: 250)
                                    ->placeholder('+91 XXXXX XXXXX')
                                    ->regex('/^\+?[0-9\s\-]+$/')
                                    ->validationMessages([
                                        'regex' => 'The phone number must contain only digits and an optional leading +.',
                                        'max'   => 'The phone number cannot exceed 15 characters.',
                                    ]),
                            ]),
                    ]),
 
                // ─── INTERNSHIP DETAILS ──────────────
                Forms\Components\Section::make('Internship Details')
                    ->columns(2)
                    ->schema([
                        Select::make('duration')
                            ->label('Internship Duration')
                            ->options(OfferLetter::DURATION_SUGGESTIONS)
                            ->searchable()
                            ->createOptionUsing(fn ($val) => $val)
                            ->placeholder('Select or type duration (e.g. 1 month, 21 days)...')
                            ->default('1 month')
                            ->required()
                            ->live()
                            ->helperText('Select standard duration or type any custom duration.')
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::updateCompletionDate($set, $get);
                            }),

                        DatePicker::make('joining_date')
                            ->label('Joining Date')
                            ->required()
                            ->native(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::updateCompletionDate($set, $get);
                            })
                            ->minDate(now()->subYear())
                            ->maxDate(now()->addYears(2)),

                        DatePicker::make('completion_date')
                            ->label('Completion Date')
                            ->required()
                            ->native(true)
                            ->live(debounce: 250)
                            ->helperText('Auto-calculated from joining date & duration; editable as needed.')
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($get('template') === 'multi_page') {
                                    $currentDesc = $get('description');
                                    if (empty(trim(strip_tags($currentDesc ?? '')))) {
                                        $set('description', OfferLetter::defaultDescription(
                                            $get('joining_date'),
                                            $state,
                                            $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                                        ));
                                    }
                                }
                            })
                            ->minDate(fn (Get $get) => $get('joining_date') ?? now()->subYear())
                            ->maxDate(now()->addYears(2))
                            ->validationMessages([
                                'after' => 'The completion date must be a date after the joining date.',
                            ]),

                        TextInput::make('internship_role')
                            ->label('Internship Role')
                            ->placeholder('e.g. Web Developer')
                            ->live(debounce: 250)
                            ->required(),

                        TextInput::make('internship_position')
                            ->label('Internship Position')
                            ->placeholder('e.g. Junior Developer Intern')
                            ->live(debounce: 250)
                            ->required(),

                        TextInput::make('working_hours')
                            ->label('Working Hours')
                            ->placeholder('e.g. 11:00 AM to 4:00 PM, Monday to Saturday')
                            ->default('11:00 AM to 4:00 PM, Monday to Saturday')
                            ->live(debounce: 250)
                            ->required(),
                        
                        DatePicker::make('offer_issue_date')
                            ->label('Offer Letter Issue on:')
                            ->required()
                            ->native(true)
                            ->live(debounce: 250)
                            ->maxDate(fn (Get $get) => $get('joining_date')
                                ? \Illuminate\Support\Carbon::parse($get('joining_date'))->subDay() 
                                : null)
                            ->validationMessages([
                                'before' => 'The offer issue date must be at least one day before the joining date.',
                            ]),
                    ]),

                // ─── OFFER LETTER LAYOUT & CUSTOM BODY ──────────────
                Forms\Components\Section::make('Offer Letter Layout & Custom Body')
                    ->description('Select layout and provide custom description for multi-page offer letters.')
                    ->schema([
                        Select::make('template')
                            ->label('Offer Letter Layout')
                            ->options(OfferLetterTemplate::LAYOUT_OPTIONS)
                            ->default('single_page')
                            ->required()
                            ->live()
                            ->placeholder('Select layout…')
                            ->helperText('Select "Single Page Offer Letter" (1 page) or "Multi Page Offer Letter" (2 pages).')
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state === 'multi_page') {
                                    $currentDesc = $get('description');
                                    if (empty(trim(strip_tags($currentDesc ?? '')))) {
                                        $set('description', OfferLetter::defaultDescription(
                                            $get('joining_date'),
                                            $get('completion_date'),
                                            $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                                        ));
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->dehydrated(true)
                            ->label('Custom Description / Body (Page 2)')
                            ->live(debounce: 500)
                            ->visible(fn (Get $get) => $get('template') === 'multi_page')
                            ->default(function (Get $get) {
                                if ($get('template') === 'multi_page') {
                                    return OfferLetter::defaultDescription(
                                        $get('joining_date'),
                                        $get('completion_date'),
                                        $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                                    );
                                }
                                return null;
                            })
                            ->helperText('This formatted content appears on the second page of the multi-page offer letter.')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline',
                                'bulletList', 'orderedList',
                                'h2', 'h3',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),
 
                Forms\Components\Placeholder::make('note')
                    ->content('The Offer Letter Code will be generated automatically upon saving.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['application', 'templatePreset']))
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\View::make('filament.offer-letters.offer-letter-card')
                    ->components([
                        TextColumn::make('offer_letter_code')->searchable(),
                        TextColumn::make('name')->searchable(),
                        TextColumn::make('email')->searchable(),
                        TextColumn::make('phone')->searchable(),
                        TextColumn::make('college')->searchable(),
                        TextColumn::make('degree')->searchable(),
                        TextColumn::make('internship_role')->searchable(),
                        TextColumn::make('internship_position')->searchable(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('offer_status')
                    ->label('Offer Status')
                    ->options([
                        'draft'    => '🟡 Draft',
                        'accepted' => '✅ Accepted',
                        'rejected' => '❌ Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('template')
                    ->label('Offer Letter Layout')
                    ->options(OfferLetterTemplate::LAYOUT_OPTIONS),

                Tables\Filters\SelectFilter::make('duration')
                    ->label('Duration')
                    ->options(OfferLetter::DURATION_SUGGESTIONS),

                Tables\Filters\SelectFilter::make('offer_letter_template_id')
                    ->label('Saved Template Preset')
                    ->options(fn () => OfferLetterTemplate::orderBy('name')->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                // View Offer PDF in New Tab
                Tables\Actions\Action::make('view_pdf')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn ($record) => route('view-offer-pdf', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                // Download PDF
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function ($record) {
                        $template = $record->template ?? 'general';
                        $pdf = Pdf::loadView("offerletter.$template", [
                            'offers' => collect([$record])
                        ]);
                        $fileName = str_replace('/', '-', $record->offer_letter_code);
                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $fileName . '.pdf'
                        );
                    }),

                // Edit
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->color('info'),

                // Accept Action
                Tables\Actions\Action::make('accept_offer')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Accept Offer & Email Credentials')
                    ->modalDescription('Mark this offer letter as accepted, generate the Intern account, and immediately email the login User ID and Password to the candidate.')
                    ->visible(fn ($record) => $record->offer_status === 'draft')
                    ->action(function ($record) {
                        $record->update(['offer_status' => 'accepted', 'is_accepted' => true]);

                        if (!$record->intern_id && !$record->intern) {
                            try {
                                $year = now()->format('y');
                                $prefix = "TS{$year}/WD/";
                                $lastIntern = Intern::where('intern_code', 'like', "{$prefix}%")->latest('id')->first();
                                $sequence = $lastIntern
                                    ? (int) str($lastIntern->intern_code)->afterLast('/')->toString() + 1
                                    : 1;
                                $paddedSequence = str_pad($sequence, 3, '0', STR_PAD_LEFT);
                                $generatedCode  = $prefix . $paddedSequence;
                                $username       = "ts{$year}{$paddedSequence}@user.com";
                                $plainPassword  = "ts{$year}{$paddedSequence}";

                                $intern = Intern::create([
                                    'application_id'             => $record->application_id,
                                    'offer_letter_id'            => $record->id,
                                    'intern_code'                => $generatedCode,
                                    'username'                   => $username,
                                    'password'                   => \Illuminate\Support\Facades\Hash::make($plainPassword),
                                    'plain_password'             => $plainPassword,
                                    'name'                       => $record->name ?: ($record->application?->name ?: 'Intern ' . $sequence),
                                    'email'                      => $record->email ?: ($record->application?->email ?: "intern{$sequence}@example.com"),
                                    'phone'                      => $record->phone ?: $record->application?->phone,
                                    'college'                    => $record->college ?: $record->application?->college,
                                    'degree'                     => $record->degree ?: $record->application?->degree,
                                    'university'                 => $record->university ?: ($record->college ?: $record->application?->college),
                                    'academic_year'              => $record->application?->year,
                                    'cgpa'                       => $record->application?->cgpa,
                                    'domain'                     => $record->application?->domain,
                                    'skills'                     => $record->application?->skills,
                                    'internship_role'            => $record->internship_role ?: ($record->application?->domain ?: 'Intern'),
                                    'internship_position'        => $record->internship_position ?: ($record->internship_role ? $record->internship_role . ' Intern' : 'Intern'),
                                    'working_hours'              => $record->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                                    'joining_date'               => $record->joining_date,
                                    'completion_date'            => $record->completion_date,
                                    'completion_letter_template' => null,
                                    'cert_token'                 => null,
                                    'is_active'                  => true,
                                ]);

                                // ✉️ Send User ID & Password email automatically
                                try {
                                    Mail::to($intern->email)->send(new InternWelcomeMail($intern));
                                } catch (\Throwable $mailErr) {
                                    \Illuminate\Support\Facades\Log::error("Failed sending welcome mail to {$intern->email}: " . $mailErr->getMessage());
                                }

                                Notification::make()
                                    ->title('Offer Accepted — Credentials Emailed')
                                    ->body("Account **{$generatedCode}** created. User ID & Password have been emailed directly to **{$intern->email}**.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                $record->update(['offer_status' => 'draft', 'is_accepted' => false]);
                                Notification::make()
                                    ->title('Error Creating Intern Account')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            $intern = $record->intern;
                            if ($intern) {
                                // Sync all details from offer letter and application to existing intern
                                $intern->update(array_filter([
                                    'name'                => $record->name ?: $record->application?->name,
                                    'email'               => $record->email ?: $record->application?->email,
                                    'phone'               => $record->phone ?: $record->application?->phone,
                                    'college'             => $record->college ?: $record->application?->college,
                                    'degree'              => $record->degree ?: $record->application?->degree,
                                    'university'          => $record->university ?: ($record->college ?: $record->application?->college),
                                    'academic_year'       => $record->application?->year,
                                    'cgpa'                => $record->application?->cgpa,
                                    'domain'              => $record->application?->domain,
                                    'skills'              => $record->application?->skills,
                                    'internship_role'     => $record->internship_role ?: $record->application?->domain,
                                    'internship_position' => $record->internship_position,
                                    'working_hours'       => $record->working_hours,
                                    'joining_date'        => $record->joining_date,
                                    'completion_date'     => $record->completion_date,
                                    'is_active'           => true,
                                ], fn ($val) => !is_null($val)));

                                try {
                                    Mail::to($intern->email)->send(new InternWelcomeMail($intern));
                                } catch (\Throwable $mailErr) {
                                    \Illuminate\Support\Facades\Log::error("Failed sending welcome mail to {$intern->email}: " . $mailErr->getMessage());
                                }
                            }

                            Notification::make()
                                ->title('Offer Accepted — Credentials Emailed')
                                ->body("Login User ID & Password have been emailed to **" . ($intern?->email ?? $record->email) . "**.")
                                ->success()
                                ->send();
                        }
                    }),

                // Reject Action
                Tables\Actions\Action::make('reject_offer')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Reject Offer Letter')
                    ->modalDescription('Mark this offer letter as rejected.')
                    ->visible(fn ($record) => $record->offer_status === 'draft')
                    ->action(function ($record) {
                        $record->update(['offer_status' => 'rejected', 'is_accepted' => false]);
                        Notification::make()
                            ->title('Offer Rejected')
                            ->danger()
                            ->send();
                    }),

                // Optional 1-Click Resend Credentials Action for Accepted Cards
                Tables\Actions\Action::make('resend_credentials')
                    ->label('Resend Credentials')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->visible(fn ($record) => $record->offer_status === 'accepted' && $record->intern)
                    ->requiresConfirmation()
                    ->modalHeading('Resend Login Credentials')
                    ->modalDescription(fn ($record) => "Resend login User ID and Password email to {$record->intern?->email}?")
                    ->action(function ($record) {
                        if ($record->intern) {
                            try {
                                Mail::to($record->intern->email)->send(new InternWelcomeMail($record->intern));
                                Notification::make()
                                    ->title('Credentials Resent')
                                    ->body("User ID and password resent to **{$record->intern->email}**.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Email Sending Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_download')
                    ->label('Bulk Download / Print')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->modalHeading('Download / Print Bulk Offer Letters')
                    ->modalDescription('Choose whether to generate a single combined PDF for all selected candidates or a separate PDF for each candidate.')
                    ->modalSubmitActionLabel('Download / Print')
                    ->form([
                        Forms\Components\Radio::make('export_mode')
                            ->label('Select Output Option')
                            ->options([
                                'single_pdf'    => 'Single PDF (All candidates combined in 1 PDF)',
                                'separate_pdfs' => 'Different PDF for Each Candidate (Downloaded as a ZIP file)',
                            ])
                            ->descriptions([
                                'single_pdf'    => 'Best for printing all selected offer letters together at once.',
                                'separate_pdfs' => 'Generates an individual PDF file for each candidate inside a ZIP archive.',
                            ])
                            ->default('single_pdf')
                            ->required(),
                    ])
                    ->action(function ($records, array $data) {
                        $mode = $data['export_mode'] ?? 'single_pdf';

                        if ($mode === 'single_pdf') {
                            $template = $records->first()?->template ?? 'general';
                            $pdf = Pdf::loadView("offerletter.$template", [
                                'offers' => $records
                            ]);

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'offer_letters.pdf'
                            );
                        }

                        // Separate PDFs for each candidate in a ZIP archive
                        $zipFileName = 'offer_letters_' . now()->format('Ymd_His') . '.zip';
                        $zipPath = storage_path($zipFileName);
                        $zip = new ZipArchive;

                        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                            foreach ($records as $offer) {
                                $template = $offer->template ?? 'general';
                                $pdf = Pdf::loadView("offerletter.$template", [
                                    'offers' => collect([$offer])
                                ]);
                                $candidateName = trim((string) ($offer->application?->name ?? $offer->getRawOriginal('name') ?? 'Candidate'));
                                $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $candidateName);
                                $safeCode = str_replace(['/', '\\'], '-', $offer->offer_letter_code ?: $offer->id);
                                $fileName = "{$safeName}_{$safeCode}.pdf";
                                $zip->addFromString($fileName, $pdf->output());
                            }
                            $zip->close();
                        }

                        return response()->download($zipPath)->deleteFileAfterSend(true);
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('bulk_update_drafts')
                    ->label('Update Drafts / Apply Template')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading('Update Selected Draft Offer Letters')
                    ->modalDescription('Update internship details for all selected draft offer letters. Choose a saved template preset to auto-fill or enter custom details. (Only draft offer letters will be modified; accepted/rejected letters are preserved).')
                    ->modalSubmitActionLabel('Update Selected Drafts')
                    ->form([
                        Forms\Components\Select::make('template_preset_id')
                            ->label('Apply Saved Template Preset')
                            ->options(fn () => OfferLetterTemplate::pluck('name', 'id'))
                            ->placeholder('Choose a preset to auto-fill fields...')
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (!$state) return;
                                $preset = OfferLetterTemplate::find($state);
                                if ($preset) {
                                    $set('template', $preset->template ?: 'single_page');
                                    $set('duration', $preset->duration ?: '1 month');
                                    $set('internship_role', $preset->internship_role);
                                    $set('internship_position', $preset->internship_position);
                                    $set('joining_date', $preset->joining_date?->format('Y-m-d'));
                                    $set('completion_date', $preset->completion_date?->format('Y-m-d'));
                                    $set('working_hours', $preset->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday');
                                    $set('offer_issue_date', $preset->offer_issue_date?->format('Y-m-d'));
                                    $set('description', $preset->description);
                                }
                            }),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('duration')
                                ->label('Internship Duration')
                                ->options(OfferLetter::DURATION_SUGGESTIONS)
                                ->searchable()
                                ->createOptionUsing(fn ($val) => $val)
                                ->placeholder('Select or type duration...')
                                ->default('1 month')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $join = $get('joining_date');
                                    if ($join && $state) {
                                        $computed = OfferLetter::calculateCompletionDate($join, $state);
                                        if ($computed) {
                                            $set('completion_date', $computed);
                                        }
                                    }
                                }),

                            Forms\Components\DatePicker::make('joining_date')
                                ->label('Joining Date')
                                ->required()
                                ->native(true)
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $duration = $get('duration');
                                    if ($state && $duration) {
                                        $computed = OfferLetter::calculateCompletionDate($state, $duration);
                                        if ($computed) {
                                            $set('completion_date', $computed);
                                        }
                                    }
                                }),

                            Forms\Components\DatePicker::make('completion_date')
                                ->label('Completion Date')
                                ->required()
                                ->native(true)
                                ->live()
                                ->helperText('Auto-calculated; editable as needed.'),

                            Forms\Components\TextInput::make('internship_role')
                                ->label('Internship Role')
                                ->placeholder('e.g. Web Developer')
                                ->required(),

                            Forms\Components\TextInput::make('internship_position')
                                ->label('Internship Position')
                                ->placeholder('e.g. Junior Developer Intern')
                                ->required(),

                            Forms\Components\TextInput::make('working_hours')
                                ->label('Working Hours')
                                ->default('11:00 AM to 4:00 PM, Monday to Saturday')
                                ->required(),

                            Forms\Components\DatePicker::make('offer_issue_date')
                                ->label('Offer Letter Issue on:')
                                ->required()
                                ->native(true),
                        ]),

                        Forms\Components\Select::make('template')
                            ->label('Offer Letter Layout')
                            ->options(OfferLetterTemplate::LAYOUT_OPTIONS)
                            ->default('single_page')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state === 'multi_page') {
                                    $currentDesc = $get('description');
                                    if (empty(trim(strip_tags($currentDesc ?? '')))) {
                                        $set('description', OfferLetter::defaultDescription(
                                            $get('joining_date'),
                                            $get('completion_date'),
                                            $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                                        ));
                                    }
                                }
                            }),

                        Forms\Components\RichEditor::make('description')
                            ->label('Custom Description / Body (Page 2)')
                            ->helperText('Custom body for page 2 of multi-page offer letters.')
                            ->visible(fn (Get $get) => $get('template') === 'multi_page')
                            ->columnSpanFull(),
                    ])
                    ->action(function ($records, array $data) {
                        $drafts = $records->filter(fn ($r) => $r->offer_status === 'draft');
                        $skipped = $records->count() - $drafts->count();

                        if ($drafts->isEmpty()) {
                            Notification::make()
                                ->title('No Drafts Updated')
                                ->body('None of the selected offer letters are in draft status.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $presetId = $data['template_preset_id'] ?? null;
                        $template = $data['template'] ?? 'single_page';
                        $description = $template === 'multi_page'
                            ? (!empty(trim(strip_tags($data['description'] ?? '')))
                                ? $data['description']
                                : OfferLetter::defaultDescription($data['joining_date'], $data['completion_date'], $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday'))
                            : null;

                        foreach ($drafts as $draft) {
                            $draft->update([
                                'template'                 => $template,
                                'duration'                 => $data['duration'] ?? $draft->duration,
                                'offer_letter_template_id' => $presetId ?: $draft->offer_letter_template_id,
                                'internship_role'          => $data['internship_role'],
                                'internship_position'      => $data['internship_position'],
                                'joining_date'             => $data['joining_date'],
                                'completion_date'          => $data['completion_date'],
                                'working_hours'            => $data['working_hours'],
                                'offer_issue_date'         => $data['offer_issue_date'],
                                'description'              => $description,
                            ]);
                        }

                        $msg = "Successfully updated {$drafts->count()} draft offer letter(s).";
                        if ($skipped > 0) {
                            $msg .= " ({$skipped} accepted/rejected offer letters were skipped).";
                        }

                        Notification::make()
                            ->title('Draft Offer Letters Updated')
                            ->body($msg)
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OfferLetterResource\Widgets\OfferLetterStatsOverview::class,
            OfferLetterResource\Widgets\OfferLetterTemplatesWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferLetters::route('/'),
            'create' => Pages\CreateOfferLetter::route('/create'),
            'edit' => Pages\EditOfferLetter::route('/{record}/edit'),
        ];
    }

    public static function updateCompletionDate(Set $set, Get $get)
    {
        $joiningDate = $get('joining_date');
        $duration = $get('duration');

        // Fallback: If duration is empty, look at selected candidate's application duration
        if (!$duration) {
            $selectedIds = $get('applications');
            if (!empty($selectedIds)) {
                $application = Application::find(collect($selectedIds)->first());
                if ($application && $application->duration && $application->duration_unit) {
                    $duration = (int) $application->duration . ' ' . $application->duration_unit;
                    $set('duration', $duration);
                }
            }
        }

        if ($joiningDate && $duration) {
            $computedCompletion = OfferLetter::calculateCompletionDate($joiningDate, $duration);
            if ($computedCompletion) {
                $set('completion_date', $computedCompletion);

                if ($get('template') === 'multi_page') {
                    $currentDesc = $get('description');
                    if (empty(trim(strip_tags($currentDesc ?? '')))) {
                        $set('description', OfferLetter::defaultDescription(
                            $joiningDate,
                            $computedCompletion,
                            $get('working_hours') ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                        ));
                    }
                }
            }
        }
    }
}
