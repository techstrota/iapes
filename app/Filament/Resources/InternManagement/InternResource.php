<?php

namespace App\Filament\Resources\InternManagement;

use App\Filament\Resources\InternManagement\InternResource\Pages;
use App\Filament\Resources\InternManagement\InternResource\RelationManagers;
use App\Models\InterviewManagement\OfferLetter;
use App\Models\InternManagement\Intern;
use App\Models\InterviewManagement\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, TextArea, FileUpload, Select, DatePicker, TimePicker, Section, Grid, RichEditor};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\{Action, BulkAction, DeleteBulkAction, EditAction, ActionGroup};
use Filament\Tables\Columns\{TextColumn, ToggleColumn, BadgeColumn, IconColumn};
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\View;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Browsershot\Browsershot;
use ZipArchive;
use Filament\Forms\Set;
use Filament\Forms\Get;

class InternResource extends Resource
{
    protected static ?string $model = Intern::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?int $navigationSort = 3;

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Intern Selection')
                    ->description('Select an intern to generate or edit their completion details.')
                    ->schema([
                        Select::make('application_id') // Binds to the Intern ID
                            // ->relationship('application', 'name')
                            ->label('Select Intern')
                            ->options(function (?Intern $record) {
                                return Intern::with(['application', 'offerletter'])
                                    ->where(function ($query) use ($record) {
                                        // Always include the current intern when editing
                                        if ($record?->id) {
                                            $query->where('id', $record->id);
                                        }
                                    })
                                    ->orWhereHas('offerletter', fn($q) => $q->where('is_accepted', true))
                                    ->get()
                                    ->mapWithKeys(function (Intern $intern) {
                                        // Priority: offerLetter name → application name → intern_code
                                        $name = $intern->offerletter?->name
                                            ?? $intern->application?->name
                                            ?? $intern->intern_code;

                                        return [$intern->application_id ?? $intern->id => $name];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            // ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (!$state) return;

                                $app = Application::with([
                                    'offer_letters' => fn($q) => $q->where('is_accepted', true)
                                ])->find($state);

                                if ($app) {
                                    $offer = $app->offer_letters->first();

                                    // Always prefer offer letter name as that's the legal name
                                    $set('intern_name',          $offer?->name          ?? $app->name);
                                    $set('college',              $offer?->college        ?? $app->college);
                                    $set('degree',               $offer?->degree         ?? $app->degree);
                                    $set('university',           $offer?->university     ?? $app->university);
                                    $set('joining_date',         $offer?->joining_date);
                                    $set('internship_role',      $offer?->internship_role);
                                    $set('internship_position',  $offer?->internship_position);
                                }
                            }),
                    ]),

                Section::make('Completion Details')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('project_name')
                                ->label('Project Name')
                                ->required(),
                            
                            TextInput::make('grade')
                                ->label('Grade')
                                ->placeholder('A, B, etc.')
                                ->maxLength(255),
                            
                            // DatePicker::make('completion_date')
                            //     ->label('Completion Date')
                            //     ->default(now())
                            //     ->afterStateHydrated(fn ($component, $record) => $component->state($record?->offer_letters?->completion_date))
                            //     ->required(),

                            DatePicker::make('issuing_date')
                                ->label('Issuing Date')
                                ->required()
                                ->after('completion_date')
                                ->minDate(fn (Get $get) => $get('completion_date')),

                            Select::make('completion_letter_template')
                            ->label('Completion Letter Template')
                            ->options([
                                'bachelors' => 'Bachelor Degree Completion Letter',
                                'masters' => 'Master Degree Completion Letter',
                            ])
                            ->required()
                            ->native(false) // This makes it look like the modern dropdown in your image
                            ->searchable()   // Optional: allows HR to type and find the template quickly
                            ->placeholder('Select a template')
                            ->columnSpan(1),
                        ]),
                        
                        RichEditor::make('project_description')
                            ->label('Project Description')
                            ->columnSpanFull(),
                    ]),

                Section::make('Editable Fetched Information')
                    ->description('Changes here will update the Offer Letter and Application records.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('intern_name')
                                ->label('Full Name')
                                ->required()
                                ->dehydrated(true) // Keep value on submit
                                ->afterStateHydrated(function ($component, $record) {
                                    $component->state(
                                        $record?->offerletter?->name
                                        ?? $record?->application?->name
                                    );
                                }),
 
                            TextInput::make('degree')
                                ->label('Degree/Course')
                                ->dehydrated(true)
                                ->afterStateHydrated(fn ($component, $record) => $component->state(
                                    $record?->offerletter?->degree ?? $record?->application?->degree
                                )),
 
                            TextInput::make('college')
                                ->label('College')
                                ->dehydrated(true)
                                ->afterStateHydrated(fn ($component, $record) => $component->state(
                                    $record?->offerletter?->college ?? $record?->application?->college
                                )),
 
                            TextInput::make('university')
                                ->label('University')
                                ->dehydrated(true)
                                ->afterStateHydrated(fn ($component, $record) => $component->state(
                                    $record?->offerletter?->university
                                )),
 
                            TextInput::make('internship_role')
                                ->label('Role')
                                ->dehydrated(true)
                                ->afterStateHydrated(fn ($component, $record) => $component->state(
                                    $record?->offerletter?->internship_role
                                )),
 
                            TextInput::make('internship_position')
                                ->label('Position')
                                ->dehydrated(true)
                                ->afterStateHydrated(fn ($component, $record) => $component->state(
                                    $record?->offerletter?->internship_position
                                )),
 
                            DatePicker::make('completion_date')
                                ->label('Completion Date')
                                ->dehydrated(true)
                                ->afterStateHydrated(fn ($component, $record) => $component->state(
                                    $record?->offerletter?->completion_date
                                )),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('3s') // ⬅ auto refresh
            ->columns([
                //
                TextColumn::make('intern_code')
                    ->label('Intern ID')
                    ->sortable(),

                TextColumn::make('application.application_code')
                    ->label('Application ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Intern Name')
                    ->getStateUsing(function ($record) {
                        return $record->offerletter?->name 
                            ?? $record->application?->name 
                            ?? '';
                    })
                    ->description(fn ($record): string => $record->offerletter->internship_role ?? 'Not Allocated')
                    ->searchable(['name']) // Allows searching if 'name' is a column in 'interns' table
                    ->sortable(),
                    
                TextColumn::make('offerletter.internship_role')
                    ->label('Intern Role')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('internship_duration')
                    ->label('Internship Duration')
                    ->getStateUsing(function ($record) {
                        if (!$record->application) 
                        {
                            $start = \Carbon\Carbon::parse($record->offerletter->joining_date);
                            $end = \Carbon\Carbon::parse($record->offerletter->completion_date);
                            $days = (int) $start->diffInDays($end);

                            // If less than 30 days, show in Days
                            if ($days < 30) {
                                return "{$days} " . \Illuminate\Support\Str::plural('Day', $days);
                            }

                            // Otherwise, show in Months (rounded to whole number)
                            $months = (int) round($start->floatDiffInMonths($end));
                            return "{$months} " . \Illuminate\Support\Str::plural('Month', $months);
                        }

                        return $record->application->duration . ' ' . $record->application->duration_unit . '';
                    }),
                TextColumn::make('completion_letter_template')
                    ->label('Letter Template')
                    ->placeholder('Not Selected')
                    ->badge()
                    ->colors([
                        'primary' => 'bachelors',
                        'info' => 'masters',
                        'gray' => null, // Shows gray if no template is selected yet
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('offer_letters.completion_date')
                    ->label('Completion Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Not Completed'),

                TextColumn::make('project_name')
                    ->label('Project Name')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not Allocated'),

                ToggleColumn::make('is_active')
                    ->label('Intern Status')
                    ->disabled(fn ($record) => 
                    // Deactivate/Disable toggle if completion date has passed
                    $record->offerletter?->completion_date && 
                    \Carbon\Carbon::parse($record->offerletter->completion_date)->isPast()
                )
                ->afterStateUpdated(function ($record, $state) {
                    // Optional: Send a notification when manually toggled
                    Notification::make()
                        ->title($state ? 'Intern Activated' : 'Intern Deactivated')
                        ->success()
                        ->send();
                }),

                // Adding a status badge next to it makes it even clearer for HR
                TextColumn::make('status_label')
                    ->label('Intenrnship ')
                    ->badge()
                    ->getStateUsing(fn ($record) => 
                        \Carbon\Carbon::parse($record->offerletter?->completion_date)->isPast() 
                            ? 'Completed' 
                            : 'On-going'
                    )
                    ->colors([
                        'danger' => 'Completed',
                        'success' => 'On-going',
                    ]),

            ])
        
            ->filters([
                //
            ])
            ->actions([
            Tables\Actions\EditAction::make(),

            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('view_id_card')
                    ->label('I-Card')
                    ->icon('heroicon-o-identification')
                    ->visible(fn ($record) => $record->offerLetter?->is_accepted ?? false)
                    ->url(fn ($record) => route('print-id-card', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_completion_letter')
                    ->label('View Completion Letter')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Intern $record) => 
                        ($record->offerLetter?->is_accepted ?? false) && 
                        filled($record->completion_letter_template) &&
                        filled($record->project_name)
                    )
                    ->url(fn (Intern $record) => route('intern.completion_letter.view', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download_completion_letter')
                    ->label('Download Completion Letter')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (Intern $record) => 
                        ($record->offerLetter?->is_accepted ?? false) && 
                        filled($record->completion_letter_template) &&
                        filled($record->project_name)
                    )
                    ->url(fn (Intern $record) => route('intern.completion_letter.download', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_certificate')
                    ->label('View Certificate')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Intern $record) => 
                        ($record->offerLetter?->is_accepted ?? false) && 
                        filled($record->completion_letter_template) &&
                        filled($record->project_name)
                    )
                    ->url(fn (Intern $record) => route('intern.certificate.view', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('print_certificate')
                    ->label('Download Certificate')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Intern $record) => 
                        ($record->offerLetter?->is_accepted ?? false) && 
                        filled($record->completion_letter_template) &&
                        filled($record->project_name)
                    )
                    ->url(fn (Intern $record) => route('intern.certificate.download', ['id' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->color('gray')
            ->button() // Optional: makes the group look like a button
            ->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // --- COMPLETION LETTERS ---
                    BulkAction::make('bulk_download_completion_letters')
                        ->label('Bulk Download Letters (ZIP)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($records) {
                            // Filter records to only include those with templates and project names
                            $validRecords = $records->filter(fn ($intern) => 
                                filled($intern->completion_letter_template) && filled($intern->project_name)
                            );

                            if ($validRecords->isEmpty()) {
                                Notification::make()
                                    ->title('No valid templates selected')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $zipFileName = 'completion_letters_' . now()->timestamp . '.zip';
                            $zipPath = storage_path($zipFileName);
                            $zip = new ZipArchive;

                            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                                $logoPath = public_path('images/TsLogo.png');
                                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

                                foreach ($validRecords as $intern) {
                                    $template = $intern->completion_letter_template; // Removed the default 'bachelors'
                                    
                                    $html = View::make("completionletter.{$template}", [
                                        'intern' => $intern,
                                        'isPdf'  => true,
                                        'logo'   => $logoBase64,
                                    ])->render();

                                    $pdfContent = Browsershot::html($html)
                                        ->setNodeBinary(env('NODE_PATH', '/usr/bin/node'))
                                        ->setNpmBinary(env('NPM_PATH', '/usr/bin/npm'))
                                        ->setChromePath(env('CHROME_PATH'))
                                        ->format('A4')
                                        ->showBackground()
                                        ->noSandbox()
                                        ->pdf();

                                    $fileName = str_replace(['/', '\\'], '-', $intern->intern_code) . '.pdf';
                                    $zip->addFromString($fileName, $pdfContent);
                                }
                                $zip->close();
                            }
                            return response()->download($zipPath)->deleteFileAfterSend(true);
                        }),

                    BulkAction::make('bulk_print_completion_letters')
                        ->label('Bulk Print Letters')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function ($records) {
                            // Filter records logic
                            $validRecords = $records->filter(fn ($intern) => 
                                filled($intern->completion_letter_template) && filled($intern->project_name)
                            );

                            if ($validRecords->isEmpty()) {
                                Notification::make()
                                    ->title('No valid templates selected')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $logoPath = public_path('images/TsLogo.png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

                            $html = View::make('completionletter.bulk', [
                                'interns' => $validRecords, // Use filtered records
                                'isPdf'   => true,
                                'logo'    => $logoBase64,
                            ])->render();

                            $pdf = Browsershot::html($html)
                                ->setNodeBinary(env('NODE_PATH', '/usr/bin/node'))
                                ->setChromePath(env('CHROME_PATH'))
                                ->format('A4')
                                ->showBackground()
                                ->noSandbox()
                                ->pdf();

                            return response()->streamDownload(fn () => print($pdf), 'completion_letters_bulk.pdf');
                        }),

                    // --- CERTIFICATES ---
                    BulkAction::make('bulk_download_certificates')
                        ->label('Bulk Download Certs (ZIP)')
                        ->icon('heroicon-o-academic-cap')
                        ->color('info')
                        ->action(function ($records) {
                            $zipFileName = 'certificates_' . now()->timestamp . '.zip';
                            $zipPath = storage_path($zipFileName);
                            $zip = new ZipArchive;

                            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                                $logoPath = public_path('images/TsLogo.png');
                                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

                                foreach ($records as $intern) {
                                    $offer = $intern->offerletter;
                                    if (!$offer) continue;

                                    $html = View::make('certificate.certificate', [
                                        'offers' => collect([$offer]),
                                        'isPdf'  => true,
                                        'logo'   => $logoBase64,
                                        // If your view requires QR codes, you must generate them here or update the view 
                                        // to handle logic as seen in CertificateController@prepareViewData
                                    ])->render();

                                    $pdfContent = Browsershot::html($html)
                                        ->setNodeBinary(env('NODE_PATH', '/usr/bin/node'))
                                        ->setNpmBinary(env('NPM_PATH', '/usr/bin/npm'))
                                        ->setChromePath(env('CHROME_PATH')) 
                                        ->format('A4')
                                        ->landscape() // Certificates are usually landscape
                                        ->showBackground()
                                        ->noSandbox()
                                        ->pdf();

                                    $fileName = 'certificate_' . str_replace(['/', '\\'], '-', $intern->intern_code) . '.pdf';
                                    $zip->addFromString($fileName, $pdfContent);
                                }
                                $zip->close();
                            }
                            return response()->download($zipPath)->deleteFileAfterSend(true);
                        }),

                    BulkAction::make('bulk_print_certificates')
                        ->label('Bulk Print Certs')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function ($records) {
                            $offers = $records->map(fn($i) => $i->offerletter)->filter();
                            $logoPath = public_path('images/TsLogo.png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

                            $html = View::make('certificate.certificate', [
                                'offers' => $offers,
                                'isPdf'  => true,
                                'logo'   => $logoBase64,
                            ])->render();

                            $pdf = Browsershot::html($html)
                                ->setNodeBinary(env('NODE_PATH', '/usr/bin/node'))
                                ->setChromePath(env('CHROME_PATH'))
                                // ->setNpmBinary(env('NPM_PATH', 'C:\Program Files\nodejs\npm.cmd'))
                                ->format('A4')
                                ->landscape()
                                ->showBackground()
                                ->noSandbox()
                                ->pdf();

                            return response()->streamDownload(fn () => print($pdf), 'certificates_bulk.pdf');
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ])
                ->label('Bulk Operations')
                ->icon('heroicon-o-cog-6-tooth'),
                ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function canCreate(): bool
    {
    return false;
    }
    public static function can(string $action, ?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        // This overrides the Policy check entirely for this Resource
        return true; 
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInterns::route('/'),
            'create' => Pages\CreateIntern::route('/create'),
            'edit' => Pages\EditIntern::route('/{record}/edit'),
            'certificate' => Pages\ViewCertificate::route('/{record}/certificate'),
        ];
    }
}
