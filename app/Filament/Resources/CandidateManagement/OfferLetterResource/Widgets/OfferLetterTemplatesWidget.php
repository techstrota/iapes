<?php

namespace App\Filament\Resources\CandidateManagement\OfferLetterResource\Widgets;

use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InterviewManagement\OfferLetterTemplate;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class OfferLetterTemplatesWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string $view = 'filament.offer-letters.templates-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected $listeners = [
        'offer-templates-updated' => '$refresh',
    ];

    public function getTemplatesProperty()
    {
        return OfferLetterTemplate::withCount([
            'offerLetters as total_count',
            'offerLetters as draft_count' => fn ($q) => $q->where('offer_status', 'draft'),
            'offerLetters as accepted_count' => fn ($q) => $q->where('offer_status', 'accepted'),
        ])->orderBy('name')->get();
    }

    protected static function getTemplateFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Template Name')
                    ->placeholder('e.g. 1-Month Web Developer Batch')
                    ->required(),

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
                    ->default(now()->toDateString())
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
                ->toolbarButtons([
                    'bold', 'italic', 'underline',
                    'bulletList', 'orderedList',
                    'h2', 'h3',
                    'undo', 'redo',
                ])
                ->columnSpanFull(),
        ];
    }

    public function createTemplateAction(): Action
    {
        return Action::make('createTemplate')
            ->label('New Template')
            ->icon('heroicon-m-plus')
            ->color('primary')
            ->modalWidth('4xl')
            ->modalHeading('Create Offer Letter Template')
            ->modalDescription('Create a reusable template with duration, layout, role, position, dates, working hours, and custom body.')
            ->modalSubmitActionLabel('Create Template')
            ->form(self::getTemplateFormSchema())
            ->action(function (array $data) {
                $template = $data['template'] ?: 'single_page';
                $preset = OfferLetterTemplate::create([
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
                ]);

                Notification::make()
                    ->title("Template '{$preset->name}' Created")
                    ->body('You can now use this template to generate or update candidate offer letters in bulk.')
                    ->success()
                    ->send();

                $this->dispatch('offer-templates-updated');
            });
    }

    public function editTemplateAction(): Action
    {
        return Action::make('editTemplate')
            ->label('Edit')
            ->icon('heroicon-m-pencil-square')
            ->color('info')
            ->modalWidth('4xl')
            ->modalHeading('Edit Template & Sync Linked Drafts')
            ->modalDescription('Updating this template automatically updates all Draft offer letters linked to it.')
            ->modalSubmitActionLabel('Save & Sync Drafts')
            ->fillForm(function (array $arguments): array {
                $preset = OfferLetterTemplate::find($arguments['template'] ?? null);
                if (!$preset) {
                    return [];
                }

                return [
                    'name'                => $preset->name,
                    'template'            => $preset->template ?: 'single_page',
                    'duration'            => $preset->duration ?: '1 month',
                    'internship_role'     => $preset->internship_role,
                    'internship_position' => $preset->internship_position,
                    'joining_date'        => $preset->joining_date?->format('Y-m-d'),
                    'completion_date'     => $preset->completion_date?->format('Y-m-d'),
                    'working_hours'       => $preset->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                    'offer_issue_date'    => $preset->offer_issue_date?->format('Y-m-d'),
                    'description'         => $preset->description,
                ];
            })
            ->form(self::getTemplateFormSchema())
            ->action(function (array $data, array $arguments) {
                $preset = OfferLetterTemplate::find($arguments['template'] ?? null);
                if (!$preset) {
                    return;
                }

                $template = $data['template'] ?: 'single_page';
                $preset->update([
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
                ]);

                $draftCount = $preset->offerLetters()->where('offer_status', 'draft')->count();

                Notification::make()
                    ->title("Template '{$preset->name}' Updated")
                    ->body("Synced updated template details across {$draftCount} linked draft offer letter(s).")
                    ->success()
                    ->send();

                $this->dispatch('offer-templates-updated');
            });
    }

    public function applyTemplateAction(): Action
    {
        return Action::make('applyTemplate')
            ->label('Generate / Apply')
            ->icon('heroicon-m-user-plus')
            ->color('primary')
            ->modalWidth('2xl')
            ->modalHeading(function (array $arguments) {
                $preset = OfferLetterTemplate::find($arguments['template'] ?? null);
                return $preset ? "Generate Offer Letters — {$preset->name}" : 'Generate Offer Letters from Template';
            })
            ->modalDescription('Select one or more shortlisted candidates. New candidates will get a draft offer letter; candidates with an existing draft will have their draft updated to match this template.')
            ->modalSubmitActionLabel('Generate / Update Offer Letters')
            ->form([
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
                    ->helperText('You can select multiple shortlisted candidates at once.'),
            ])
            ->action(function (array $data, array $arguments) {
                $preset = OfferLetterTemplate::find($arguments['template'] ?? null);
                if (!$preset) {
                    return;
                }

                $applicationIds = $data['applications'] ?? [];
                $template = $preset->template ?: 'single_page';
                $description = $template === 'multi_page'
                    ? (!empty(trim(strip_tags($preset->description ?? '')))
                        ? $preset->description
                        : OfferLetter::defaultDescription(
                            $preset->joining_date?->format('Y-m-d'),
                            $preset->completion_date?->format('Y-m-d'),
                            $preset->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                        ))
                    : null;

                $count = 0;
                foreach ($applicationIds as $appId) {
                    $application = Application::find($appId);
                    if (!$application) {
                        continue;
                    }

                    $payload = [
                        'application_id'           => $application->id,
                        'offer_letter_template_id' => $preset->id,
                        'offer_status'             => 'draft',
                        'is_accepted'              => false,
                        'name'                     => $application->name,
                        'college'                  => $application->college,
                        'university'               => $application->college ?? '',
                        'degree'                   => $application->degree,
                        'phone'                    => $application->phone,
                        'email'                    => $application->email,
                        'joining_date'             => $preset->joining_date?->format('Y-m-d'),
                        'completion_date'          => $preset->completion_date?->format('Y-m-d'),
                        'internship_role'          => $preset->internship_role,
                        'internship_position'      => $preset->internship_position,
                        'working_hours'            => $preset->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                        'duration'                 => $preset->duration ?: '1 month',
                        'template'                 => $template,
                        'intern_id'                => $application->intern_id ?? null,
                        'description'              => $description,
                        'offer_issue_date'         => $preset->offer_issue_date?->format('Y-m-d') ?? now()->toDateString(),
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
                    ->title("Generated / Updated {$count} Offer Letter(s)")
                    ->body("Applied template '{$preset->name}' to {$count} candidate(s).")
                    ->success()
                    ->send();

                $this->dispatch('offer-templates-updated');
            });
    }

    public function deleteTemplateAction(): Action
    {
        return Action::make('deleteTemplate')
            ->label('Delete')
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete Offer Letter Template')
            ->modalDescription('Are you sure you want to delete this template? Existing offer letters will remain intact.')
            ->action(function (array $arguments) {
                $preset = OfferLetterTemplate::find($arguments['template'] ?? null);
                if ($preset) {
                    $name = $preset->name;
                    $preset->delete();

                    Notification::make()
                        ->title("Template '{$name}' Deleted")
                        ->success()
                        ->send();

                    $this->dispatch('offer-templates-updated');
                }
            });
    }
}
