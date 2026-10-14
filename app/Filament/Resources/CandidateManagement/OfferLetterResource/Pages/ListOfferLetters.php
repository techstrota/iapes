<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Pages;

use App\Filament\Resources\CandidateManagement\OfferLetterResource;
use App\Filament\Resources\CandidateManagement\OfferLetterResource\Widgets\OfferLetterStatsOverview;
use App\Filament\Resources\CandidateManagement\OfferLetterResource\Widgets\OfferLetterTemplatesWidget;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InterviewManagement\OfferLetterTemplate;
use App\Models\InterviewManagement\Application;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListOfferLetters extends ListRecords
{
    protected static string $resource = OfferLetterResource::class;

    protected $listeners = [
        'offer-templates-updated' => '$refresh',
    ];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_templates')
                ->label('Manage Templates')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->modalWidth('4xl')
                ->modalHeading('Offer Letter Templates Manager')
                ->modalDescription('Create a new template, or edit/delete an existing template. Updating a template automatically syncs all Draft offer letters linked to it.')
                ->modalSubmitActionLabel('Save Template & Sync Drafts')
                ->form([
                    Forms\Components\Placeholder::make('saved_templates_overview')
                        ->label('Saved Templates Overview')
                        ->content(function () {
                            $templates = OfferLetterTemplate::withCount([
                                'offerLetters as total_count',
                                'offerLetters as draft_count' => fn ($q) => $q->where('offer_status', 'draft'),
                                'offerLetters as accepted_count' => fn ($q) => $q->where('offer_status', 'accepted'),
                            ])->orderBy('name')->get();

                            if ($templates->isEmpty()) {
                                return new HtmlString('<div style="padding: 10px 14px; border-radius: 10px; background-color: #131b2e; border: 1px solid #222a3d; font-size: 12px; color: #94a3b8;">No saved templates yet. Use the form below to create your first Offer Letter Template.</div>');
                            }

                            $rows = '';
                            foreach ($templates as $t) {
                                $name = e($t->name);
                                $layout = e($t->layout_label);
                                $role = e($t->internship_role ?: '—');
                                $pos = e($t->internship_position ?: '—');
                                $join = $t->joining_date ? $t->joining_date->format('d M Y') : '—';
                                $comp = $t->completion_date ? $t->completion_date->format('d M Y') : '—';
                                $issue = $t->offer_issue_date ? $t->offer_issue_date->format('d M Y') : '—';
                                $hours = e($t->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday');

                                $rows .= "
                                    <div style='padding: 10px 12px; border-radius: 10px; border: 1px solid #222a3d; background-color: #0b1326; display: flex; flex-direction: column; gap: 5px; font-size: 11.5px;'>
                                        <div style='display: flex; align-items: center; justify-content: space-between; gap: 6px; flex-wrap: wrap;'>
                                            <span style='font-weight: 800; font-size: 13px; color: #ffffff;'>🔖 {$name}</span>
                                            <div style='display: flex; align-items: center; gap: 5px;'>
                                                <span style='padding: 2px 7px; border-radius: 6px; background-color: rgba(37, 99, 235, 0.2); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; font-weight: 700; font-size: 10px;'>⏱️ " . e($t->duration_label) . "</span>
                                                <span style='padding: 2px 8px; border-radius: 6px; background-color: #0c1c38; border: 1px solid #1d4ed8; color: #60a5fa; font-weight: 700; font-size: 10.5px;'>{$layout}</span>
                                            </div>
                                        </div>
                                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 3px 12px; color: #cbd5e1;'>
                                            <div><strong style='color: #8e909f;'>Role:</strong> {$role}</div>
                                            <div><strong style='color: #8e909f;'>Position:</strong> {$pos}</div>
                                            <div><strong style='color: #8e909f;'>Tenure:</strong> <span style='color: #60a5fa; font-weight: 600;'>{$join} → {$comp}</span></div>
                                            <div><strong style='color: #8e909f;'>Issued:</strong> {$issue}</div>
                                            <div style='grid-column: span 2;'><strong style='color: #8e909f;'>Hours:</strong> {$hours}</div>
                                        </div>
                                        <div style='padding-top: 5px; border-top: 1px solid rgba(255, 255, 255, 0.06); display: flex; align-items: center; gap: 12px; font-size: 11px; color: #94a3b8;'>
                                            <span>Linked: <strong style='color: #ffffff;'>{$t->total_count}</strong></span>
                                            <span style='color: #fbbf24;'>🟡 Drafts: <strong>{$t->draft_count}</strong></span>
                                            <span style='color: #34d399;'>✅ Accepted: <strong>{$t->accepted_count}</strong></span>
                                        </div>
                                    </div>
                                ";
                            }

                            return new HtmlString("<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 8px; max-height: 170px; overflow-y: auto; padding-right: 4px;'>{$rows}</div>");
                        }),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Radio::make('mode')
                            ->label('Action Mode')
                            ->options([
                                'create' => '➕ Create a New Template',
                                'edit'   => '✏️ Edit an Existing Template (Auto-syncs Drafts)',
                                'delete' => '🗑️ Delete an Existing Template',
                            ])
                            ->default('create')
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state === 'create') {
                                    $set('template_id', null);
                                    $set('name', null);
                                    $set('template', 'single_page');
                                    $set('duration', '1 month');
                                    $set('internship_role', null);
                                    $set('internship_position', null);
                                    $set('joining_date', null);
                                    $set('completion_date', null);
                                    $set('working_hours', '11:00 AM to 4:00 PM, Monday to Saturday');
                                    $set('offer_issue_date', now()->toDateString());
                                    $set('description', null);
                                    return;
                                }

                                // Auto-select first template if none selected yet
                                $currentId = $get('template_id') ?: OfferLetterTemplate::orderBy('name')->value('id');
                                if ($currentId) {
                                    $set('template_id', $currentId);
                                    $preset = OfferLetterTemplate::find($currentId);
                                    if ($preset) {
                                        $set('name', $preset->name);
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
                                }
                            }),

                        Forms\Components\Select::make('template_id')
                            ->label('Select Template to Edit / Delete')
                            ->options(fn () => OfferLetterTemplate::orderBy('name')->get()->mapWithKeys(
                                fn (OfferLetterTemplate $t) => [$t->id => $t->summary_label]
                            ))
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => in_array($get('mode'), ['edit', 'delete']))
                            ->required(fn (Get $get) => in_array($get('mode'), ['edit', 'delete']))
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (!$state) return;
                                $preset = OfferLetterTemplate::find($state);
                                if ($preset) {
                                    $set('name', $preset->name);
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
                    ]),

                    Forms\Components\Grid::make(2)
                        ->visible(fn (Get $get) => $get('mode') !== 'delete')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Template Name')
                                ->placeholder('e.g. 1-Month Web Developer Batch')
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),

                            Forms\Components\Select::make('duration')
                                ->label('Internship Duration')
                                ->options(OfferLetter::DURATION_SUGGESTIONS)
                                ->searchable()
                                ->createOptionUsing(fn ($val) => $val)
                                ->placeholder('Select or type duration...')
                                ->default('1 month')
                                ->required(fn (Get $get) => $get('mode') !== 'delete')
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $join = $get('joining_date');
                                    if ($join && $state) {
                                        $comp = OfferLetter::calculateCompletionDate($join, $state);
                                        if ($comp) $set('completion_date', $comp);
                                    }
                                }),

                            Forms\Components\DatePicker::make('joining_date')
                                ->label('Joining Date')
                                ->native(true)
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $dur = $get('duration');
                                    if ($state && $dur) {
                                        $comp = OfferLetter::calculateCompletionDate($state, $dur);
                                        if ($comp) $set('completion_date', $comp);
                                    }
                                })
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),

                            Forms\Components\DatePicker::make('completion_date')
                                ->label('Completion Date')
                                ->native(true)
                                ->live()
                                ->helperText('Auto-calculated; editable as needed.')
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),

                            Forms\Components\TextInput::make('internship_role')
                                ->label('Internship Role')
                                ->placeholder('e.g. Web Developer')
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),

                            Forms\Components\TextInput::make('internship_position')
                                ->label('Internship Position')
                                ->placeholder('e.g. Junior Developer Intern')
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),

                            Forms\Components\TextInput::make('working_hours')
                                ->label('Working Hours')
                                ->default('11:00 AM to 4:00 PM, Monday to Saturday')
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),

                            Forms\Components\DatePicker::make('offer_issue_date')
                                ->label('Offer Letter Issue on:')
                                ->default(now()->toDateString())
                                ->native(true)
                                ->required(fn (Get $get) => $get('mode') !== 'delete'),
                        ]),

                    Forms\Components\Select::make('template')
                        ->label('Offer Letter Layout')
                        ->options(OfferLetterTemplate::LAYOUT_OPTIONS)
                        ->default('single_page')
                        ->required(fn (Get $get) => $get('mode') !== 'delete')
                        ->visible(fn (Get $get) => $get('mode') !== 'delete')
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
                        ->visible(fn (Get $get) => $get('mode') !== 'delete' && $get('template') === 'multi_page')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $mode = $data['mode'] ?? 'create';

                    if ($mode === 'delete') {
                        $preset = OfferLetterTemplate::find($data['template_id'] ?? null);
                        if ($preset) {
                            $name = $preset->name;
                            $preset->delete();
                            Notification::make()
                                ->title("Template '{$name}' Deleted")
                                ->success()
                                ->send();
                            $this->dispatch('offer-templates-updated');
                        }
                        return;
                    }

                    $template = $data['template'] ?: 'single_page';
                    $payload = [
                        'name'                => $data['name'],
                        'template'            => $template,
                        'duration'            => $data['duration'] ?: '1 month',
                        'internship_role'     => $data['internship_role'],
                        'internship_position' => $data['internship_position'],
                        'joining_date'        => $data['joining_date'],
                        'completion_date'     => $data['completion_date'],
                        'working_hours'       => $data['working_hours'] ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                        'offer_issue_date'    => $data['offer_issue_date'],
                        'description'         => $template === 'multi_page' ? ($data['description'] ?? null) : null,
                    ];

                    if ($mode === 'edit') {
                        $preset = OfferLetterTemplate::find($data['template_id'] ?? null);
                        if ($preset) {
                            $preset->update($payload);
                            $draftCount = $preset->offerLetters()->where('offer_status', 'draft')->count();
                            Notification::make()
                                ->title("Template '{$preset->name}' Updated")
                                ->body("Synced updated template details across {$draftCount} linked draft offer letter(s).")
                                ->success()
                                ->send();
                            $this->dispatch('offer-templates-updated');
                        }
                        return;
                    }

                    $preset = OfferLetterTemplate::create($payload);
                    Notification::make()
                        ->title("Template '{$preset->name}' Created")
                        ->body('You can now select this template to generate or update candidate offer letters in bulk.')
                        ->success()
                        ->send();
                    $this->dispatch('offer-templates-updated');
                }),

            Actions\Action::make('bulk_generate_from_template')
                ->label('Bulk Generate from Template')
                ->icon('heroicon-o-document-duplicate')
                ->color('warning')
                ->modalWidth('3xl')
                ->modalHeading('Generate / Update Offer Letters in Bulk from Template')
                ->modalDescription('Select a saved template preset and pick one or more shortlisted candidates to generate or update their draft offer letters all at once.')
                ->modalSubmitActionLabel('Generate / Update Offer Letters')
                ->form([
                    Forms\Components\Select::make('offer_letter_template_id')
                        ->label('Select Template Preset')
                        ->options(fn () => OfferLetterTemplate::orderBy('name')->get()->mapWithKeys(
                            fn (OfferLetterTemplate $t) => [$t->id => $t->summary_label]
                        ))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->helperText('Selecting a template loads all its details below. You can also adjust them before generating.')
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

                    Forms\Components\Select::make('applications')
                        ->label('Select Shortlisted Candidates')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(function () {
                            return Application::where('status', 'shortlisted')
                                ->whereNotNull('name')
                                ->where('name', '!=', '')
                                ->where(function ($q) {
                                    $q->whereDoesntHave('offerLetter')
                                        ->orWhereHas('offerLetter', fn ($subQ) => $subQ->where('offer_status', 'draft'));
                                })
                                ->with('offerLetter')
                                ->get()
                                ->mapWithKeys(function ($app) {
                                    $name    = trim((string) ($app->name ?? '')) ?: 'Unnamed Intern';
                                    $college = trim((string) ($app->college ?? '')) ?: 'No College Listed';
                                    $degree  = trim((string) ($app->degree ?? '')) ?: 'No Degree Listed';
                                    $tag     = $app->offerLetter ? ' [Has Draft]' : ' [New]';
                                    return [(int) $app->id => "{$name} — {$college} ({$degree}){$tag}"];
                                });
                        })
                        ->helperText('Candidates with [New] will have a draft offer letter created; candidates with [Has Draft] will have their draft updated with this template.'),

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
                                    $comp = OfferLetter::calculateCompletionDate($join, $state);
                                    if ($comp) $set('completion_date', $comp);
                                }
                            }),

                        Forms\Components\DatePicker::make('joining_date')
                            ->label('Joining Date')
                            ->native(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $dur = $get('duration');
                                if ($state && $dur) {
                                    $comp = OfferLetter::calculateCompletionDate($state, $dur);
                                    if ($comp) $set('completion_date', $comp);
                                }
                            })
                            ->required(),

                        Forms\Components\DatePicker::make('completion_date')
                            ->label('Completion Date')
                            ->native(true)
                            ->live()
                            ->helperText('Auto-calculated; editable as needed.')
                            ->required(),

                        Forms\Components\TextInput::make('internship_role')
                            ->label('Internship Role')
                            ->required(),

                        Forms\Components\TextInput::make('internship_position')
                            ->label('Internship Position')
                            ->required(),

                        Forms\Components\TextInput::make('working_hours')
                            ->label('Working Hours')
                            ->default('11:00 AM to 4:00 PM, Monday to Saturday')
                            ->required(),

                        Forms\Components\DatePicker::make('offer_issue_date')
                            ->label('Offer Letter Issue on:')
                            ->native(true)
                            ->required(),
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
                ->action(function (array $data) {
                    $presetId = $data['offer_letter_template_id'] ?? null;
                    $applicationIds = $data['applications'] ?? [];
                    $template = $data['template'] ?? 'single_page';

                    $description = $template === 'multi_page'
                        ? (!empty(trim(strip_tags($data['description'] ?? '')))
                            ? $data['description']
                            : OfferLetter::defaultDescription(
                                $data['joining_date'],
                                $data['completion_date'],
                                $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday'
                            ))
                        : null;

                    // Also update the saved template preset if user tweaked any fields
                    if ($presetId) {
                        $preset = OfferLetterTemplate::find($presetId);
                        $preset?->update([
                            'template'            => $template,
                            'duration'            => $data['duration'] ?? $preset->duration,
                            'internship_role'     => $data['internship_role'],
                            'internship_position' => $data['internship_position'],
                            'joining_date'        => $data['joining_date'],
                            'completion_date'     => $data['completion_date'],
                            'working_hours'       => $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday',
                            'offer_issue_date'    => $data['offer_issue_date'],
                            'description'         => $description,
                        ]);
                    }

                    $count = 0;
                    foreach ($applicationIds as $appId) {
                        $application = Application::find($appId);
                        if (!$application) continue;

                        $payload = [
                            'application_id'           => $application->id,
                            'offer_letter_template_id' => $presetId,
                            'offer_status'             => 'draft',
                            'is_accepted'              => false,
                            'name'                     => $application->name,
                            'college'                  => $application->college,
                            'university'               => $application->college ?? '',
                            'degree'                   => $application->degree,
                            'phone'                    => $application->phone,
                            'email'                    => $application->email,
                            'joining_date'             => $data['joining_date'],
                            'completion_date'          => $data['completion_date'],
                            'internship_role'          => $data['internship_role'],
                            'internship_position'      => $data['internship_position'],
                            'working_hours'            => $data['working_hours'] ?? '11:00 AM to 4:00 PM, Monday to Saturday',
                            'duration'                 => $data['duration'] ?? '1 month',
                            'template'                 => $template,
                            'intern_id'                => $application->intern_id ?? null,
                            'description'              => $description,
                            'offer_issue_date'         => $data['offer_issue_date'] ?? now()->toDateString(),
                        ];

                        $existingDraft = OfferLetter::where('application_id', $application->id)
                            ->where('offer_status', 'draft')
                            ->first();

                        if ($existingDraft) {
                            $existingDraft->update($payload);
                        } else {
                            OfferLetter::create($payload);
                        }

                        $count++;
                    }

                    Notification::make()
                        ->title("Processed {$count} Offer Letter(s)")
                        ->body("Generated/updated {$count} draft offer letter(s) using the selected template.")
                        ->success()
                        ->send();

                    $this->dispatch('offer-templates-updated');
                }),

            Actions\CreateAction::make()->label('New Offer Letter'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OfferLetterStatsOverview::class,
            OfferLetterTemplatesWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $draftCount    = OfferLetter::where('offer_status', 'draft')->count();
        $acceptedCount = OfferLetter::where('offer_status', 'accepted')->count();
        $rejectedCount = OfferLetter::where('offer_status', 'rejected')->count();
        $allCount      = OfferLetter::count();

        return [
            'drafts' => Tab::make('🟡 Drafts')
                ->badge($draftCount)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('offer_status', 'draft')),

            'accepted' => Tab::make('✅ Accepted')
                ->badge($acceptedCount)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('offer_status', 'accepted')),

            'rejected' => Tab::make('❌ Rejected')
                ->badge($rejectedCount)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('offer_status', 'rejected')),

            'all' => Tab::make('All')
                ->badge($allCount)
                ->badgeColor('gray'),
        ];
    }
}
