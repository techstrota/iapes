<?php

namespace App\Filament\Resources\CandidateManagement;

use App\Filament\Resources\CandidateManagement\OfferLetterResource\Pages;
use App\Models\InterviewManagement\OfferLetter;
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
                // ─── STEP 1: Pick template FIRST ───────────────────────────────
                Forms\Components\Section::make('Offer Letter Template')
                    ->schema([
                        Select::make('template')
                            ->label('Offer Letter Template')
                            ->options([
                                '3_month_offer_letter' => '3 Month Offer Letter',
                                '4_month_offer_letter' => '4 Month Offer Letter',
                                '6_month_offer_letter' => '6 Month Offer Letter',
                                'one_month'            => 'One Month Internship',
                                'general'              => 'General Internship',
                            ])
                            ->required()
                            ->live()
                            ->placeholder('Select a template to continue…')
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (in_array($state, ['4_month_offer_letter', '6_month_offer_letter'])) {
                                    $currentDesc = $get('description');
                                    if (empty(trim(strip_tags($currentDesc ?? '')))) {
                                        $set('description', OfferLetter::defaultDescription(
                                            $get('joining_date'),
                                            $get('completion_date'),
                                            $get('working_hours') ?: '42 hours per week'
                                        ));
                                    }
                                }
                                self::updateCompletionDate($set, $get);
                            }),
                    ]),
 
                // ─── SECTION A: Application selector ──
                Section::make('Intern Selection')
                    ->description('Select interns from the same college to generate letters in bulk.')
                    ->visible(fn (Get $get) => filled($get('template')))
                    ->schema([
                        Select::make('applications')
                            ->label('Select Interns')
                            ->multiple()
                            ->options(function (Get $get, ?OfferLetter $record) {
                                $query = Application::where('status', 'shortlisted')
                                    ->whereNotNull('name')
                                    ->where('name', '!=', '')
                                    ->whereNotNull('id');
 
                                $query->where(function ($q) use ($record) {
                                    $q->whereDoesntHave('offerLetter')
                                    ->orWhereHas('offerLetter', function ($subQ) use ($record) {
                                        if ($record) {
                                            $subQ->where('id', $record->id);
                                        } else {
                                            $subQ->whereRaw('1 = 0');
                                        }
                                    });
                                });
 
                                if (!$record) {
                                    $selectedIds = $get('applications') ?? [];
                                    if (!empty($selectedIds)) {
                                        $firstIntern = Application::find($selectedIds[0]);
                                        if ($firstIntern) {
                                            $query->where('college', $firstIntern->college);
                                        }
                                    }
                                }
 
                                return $query->get()
                                    ->mapWithKeys(function ($app) {
                                        $name    = trim((string) ($app->name    ?? '')) ?: 'Unnamed Intern';
                                        $college = trim((string) ($app->college ?? '')) ?: 'No College Listed';
                                        $degree  = trim((string) ($app->degree  ?? '')) ?: 'No Degree Listed';
                                        $id      = (int) $app->id;
 
                                        return [$id => "{$name} - {$college} ({$degree})"];
                                    })
                                    ->filter(fn ($label, $id) => is_string($label) && $label !== '');
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                return Application::whereIn('id', $values)
                                    ->get()
                                    ->mapWithKeys(function ($app) {
                                        $name    = trim((string) ($app->name    ?? '')) ?: 'Unnamed Intern';
                                        $college = trim((string) ($app->college ?? '')) ?: 'No College Listed';
                                        $degree  = trim((string) ($app->degree  ?? '')) ?: 'No Degree Listed';
 
                                        return [(int) $app->id => "{$name} - {$college} ({$degree})"];
                                    })
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateHydrated(function (Set $set, ?OfferLetter $record, $state) {
                                if ($record) {
                                    $set('name',       $record->name);
                                    $set('university', $record->university);
                                    $set('college',    $record->college);
                                    $set('degree',     $record->degree);
                                    $set('email',      $record->email);
                                    $set('phone',      $record->phone);
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
                    ->description(fn (Get $get) => $get('template') === 'general'
                        ? 'Fill in the intern\'s details directly — no application required.'
                        : 'Pre-filled from the selected application. All fields are editable.')
                    ->visible(fn (Get $get) => filled($get('template')))
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Intern Full Name')
                                ->dehydrated(true)
                                ->placeholder('e.g. Rahul Sharma')
                                ->required()
                                ->live(debounce: 250)
                                ->helperText('Pre-fills from the selected intern but can be changed.')
                                ->regex('/^[a-zA-Z\s]+$/')
                                ->validationMessages([
                                    'regex' => 'The name must only contain letters and spaces.',
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
                                ->regex('/^\+?[0-9]+$/')
                                ->validationMessages([
                                    'regex' => 'The phone number must contain only digits and an optional leading +.',
                                    'max'   => 'The phone number cannot exceed 15 characters.',
                                ]),
                        ]),
                    ]),
 
                // ─── INTERNSHIP DETAILS ──────────────
                Forms\Components\Section::make('Internship Details')
                    ->visible(fn (Get $get) => filled($get('template')))
                    ->columns(2)
                    ->schema([
                        DatePicker::make('joining_date')
                            ->required()
                            ->native(true)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::updateCompletionDate($set, $get);
                                $template = $get('template');
                                if (in_array($template, ['4_month_offer_letter', '6_month_offer_letter'])) {
                                    $currentDesc = $get('description');
                                    if (empty(trim(strip_tags($currentDesc ?? '')))) {
                                        $set('description', OfferLetter::defaultDescription(
                                            $state,
                                            $get('completion_date'),
                                            $get('working_hours') ?: '42 hours per week'
                                        ));
                                    }
                                }
                            })
                            ->minDate(now()->subYear())
                            ->maxDate(now()->addYears(2)),
 
                        DatePicker::make('completion_date')
                            ->required()
                            ->native(true)
                            ->live(debounce: 250)
                            ->minDate(fn (Get $get) => $get('joining_date') ?? now())
                            ->maxDate(now()->addYears(2))
                            ->validationMessages([
                                'after' => 'The completion date must be a date after the joining date.',
                            ]),
 
                        TextInput::make('internship_role')
                            ->label('Internship Role')
                            ->placeholder('Web Developer')
                            ->live(debounce: 250)
                            ->required(),
 
                        TextInput::make('internship_position')
                            ->label('Internship Position')
                            ->placeholder('e.g. Junior Developer Intern')
                            ->live(debounce: 250)
                            ->required(),
 
                        TextInput::make('working_hours')
                            ->label('Working Hours')
                            ->placeholder('e.g. 42 hours per week')
                            ->default('42 hours per week')
                            ->live(debounce: 250)
                            ->required(),
                        
                        DatePicker::make('offer_issue_date')
                            ->label('Offer Letter Issue on:')
                            ->required()
                            ->native(true)
                            ->live(debounce: 250)
                            ->minDate(fn (Get $get) => $get('joining_date') 
                                ? \Illuminate\Support\Carbon::parse($get('joining_date'))->subDays(7) 
                                : null
                            )
                            ->maxDate(fn (Get $get) => $get('joining_date')
                                ? \Illuminate\Support\Carbon::parse($get('joining_date'))->subDay() 
                                : null)
                            ->validationMessages([
                                'before' => 'The offer issue date must be at least one day before the joining date.',
                                'after_or_equal' => 'The offer must be issued within 7 days of the joining date.',
                            ]),
 
                        Forms\Components\Section::make('Offer Letter Body')
                            ->visible(fn (Get $get) => filled($get('template')))
                            ->schema([
                                Forms\Components\RichEditor::make('description')
                                    ->dehydrated(true)
                                    ->label('Custom Description')
                                    ->live(debounce: 500)
                                    ->default(function (Get $get) {
                                        $template = $get('template');
                                        if (in_array($template, ['4_month_offer_letter', '6_month_offer_letter'])) {
                                            return OfferLetter::defaultDescription(
                                                $get('joining_date'),
                                                $get('completion_date'),
                                                $get('working_hours') ?: '42 hours per week'
                                            );
                                        }
                                        return null;
                                    })
                                    ->helperText('This formatted content appears on the second page of the offer letter.')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline',
                                        'bulletList', 'orderedList',
                                        'h2', 'h3',
                                        'undo', 'redo',
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),
 
                Forms\Components\Placeholder::make('note')
                    ->content('The Offer Letter Code will be generated automatically upon saving.')
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => filled($get('template'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->contentGrid(['sm' => 1, 'md' => 2, 'lg' => 2, '2xl' => 3])
            ->columns([
                Stack::make([
                    // Header: Candidate Name + Status Badge
                    Split::make([
                        TextColumn::make('name')
                            ->label('Candidate')
                            ->state(function ($record): string {
                                return $record->application?->name 
                                    ?? $record->getRawOriginal('name') 
                                    ?? 'Unnamed Candidate';
                            })
                            ->searchable()
                            ->sortable()
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Tables\Columns\TextColumn\TextColumnSize::Large),

                        TextColumn::make('offer_status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'draft'    => 'warning',
                                'accepted' => 'success',
                                'rejected' => 'danger',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'draft'    => '🟡 Draft',
                                'accepted' => '✅ Accepted',
                                'rejected' => '❌ Rejected',
                                default    => ucfirst($state ?? 'draft'),
                            })
                            ->alignEnd(),
                    ]),

                    // College
                    TextColumn::make('college')
                        ->label('College')
                        ->state(function ($record): string {
                            return $record->application?->college 
                                ?? $record->getRawOriginal('college') 
                                ?? '—';
                        })
                        ->icon('heroicon-m-academic-cap')
                        ->color('gray')
                        ->size(\Filament\Tables\Columns\TextColumn\TextColumnSize::Small),

                    // Role + Template
                    Split::make([
                        TextColumn::make('internship_role')
                            ->label('Role')
                            ->icon('heroicon-m-briefcase')
                            ->badge()
                            ->color('info'),

                        TextColumn::make('template')
                            ->label('Template')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                '3_month_offer_letter' => '3 Month',
                                '4_month_offer_letter' => '4 Month',
                                '6_month_offer_letter' => '6 Month',
                                'one_month'            => '1 Month',
                                'general'              => 'General',
                                default                => ucfirst($state ?? 'General'),
                            })
                            ->alignEnd(),
                    ]),

                    // Period / Dates
                    TextColumn::make('joining_date')
                        ->label('Period')
                        ->icon('heroicon-m-calendar')
                        ->color('gray')
                        ->size(\Filament\Tables\Columns\TextColumn\TextColumnSize::Small)
                        ->formatStateUsing(function ($record): string {
                            if (!$record->joining_date) return 'Dates pending';
                            $start = \Carbon\Carbon::parse($record->joining_date)->format('d M Y');
                            $end = $record->completion_date 
                                ? \Carbon\Carbon::parse($record->completion_date)->format('d M Y')
                                : '—';
                            return "{$start} → {$end}";
                        }),

                    // Code
                    TextColumn::make('offer_letter_code')
                        ->label('Code')
                        ->icon('heroicon-m-tag')
                        ->color('gray')
                        ->size(\Filament\Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                        ->searchable(),
                ])->space(3),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('offer_status')
                    ->label('Status')
                    ->options([
                        'draft'    => '🟡 Draft',
                        'accepted' => '✅ Accepted',
                        'rejected' => '❌ Rejected',
                    ]),
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
                                    'application_id'  => $record->application_id,
                                    'offer_letter_id' => $record->id,
                                    'intern_code'     => $generatedCode,
                                    'username'        => $username,
                                    'password'        => \Illuminate\Support\Facades\Hash::make($plainPassword),
                                    'name'            => $record->application?->name ?? $record->name ?? 'Intern ' . $sequence,
                                    'email'           => $record->application?->email ?? $record->email ?? "intern{$sequence}@example.com",
                                    'joining_date'    => $record->joining_date,
                                    'is_active'       => true,
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
                    ->label('Bulk Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($records) {
                        $zipFileName = 'offer_letters.zip';
                        $zipPath = storage_path($zipFileName);
                        $zip = new ZipArchive;

                        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                            foreach ($records as $offer) {
                                $template = $offer->template ?? 'general';
                                $pdf = Pdf::loadView("offerletter.$template", [
                                    'offers' => collect([$offer])
                                ]);
                                $fileName = str_replace('/', '-', $offer->offer_letter_code) . '.pdf';
                                $zip->addFromString($fileName, $pdf->output());
                            }
                            $zip->close();
                        }

                        return response()->download($zipPath)->deleteFileAfterSend(true);
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('bulk_print')
                    ->label('Bulk Print')
                    ->icon('heroicon-o-printer')
                    ->action(function ($records) {
                        $template = $records->first()?->template ?? 'general';
                        $pdf = Pdf::loadView("offerletter.$template", [
                            'offers' => $records
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'offer_letters.pdf'
                        );
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferLetters::route('/'),
            'create' => Pages\CreateOfferLetter::route('/create'),
            'edit' => Pages\EditOfferLetter::route('/{record}/edit'),
        ];
    }

    // Add this helper method inside your Resource class to keep the code clean
    public static function updateCompletionDate(Set $set, Get $get)
    {
        $selectedIds = $get('applications');
        $joiningDate = $get('joining_date');

        if (empty($selectedIds) || !$joiningDate) {
            return;
        }

        // We fetch the first intern selected to determine the duration
        $application = Application::find(collect($selectedIds)->first());

        if ($application && $application->duration && $application->duration_unit) {
            $date = Carbon::parse($joiningDate);
            $duration = (int) $application->duration;
            $unit = strtolower($application->duration_unit);

            // Add duration based on unit (e.g., 'months', 'weeks', 'days')
            if (str_contains($unit, 'month')) {
                $date->addMonths($duration);
            } elseif (str_contains($unit, 'week')) {
                $date->addWeeks($duration);
            } else {
                $date->addDays($duration);
            }

            $computedCompletion = $date->format('Y-m-d');
            $set('completion_date', $computedCompletion);

            $template = $get('template');
            if (in_array($template, ['4_month_offer_letter', '6_month_offer_letter'])) {
                $currentDesc = $get('description');
                if (empty(trim(strip_tags($currentDesc ?? '')))) {
                    $set('description', OfferLetter::defaultDescription(
                        $joiningDate,
                        $computedCompletion,
                        $get('working_hours') ?: '42 hours per week'
                    ));
                }
            }
        }
    }
}
