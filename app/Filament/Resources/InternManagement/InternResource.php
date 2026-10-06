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
use Filament\Tables\Columns\{TextColumn, ToggleColumn, BadgeColumn, IconColumn, ImageColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\View;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Browsershot\Browsershot;
use ZipArchive;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Services\CompletionDocumentService;

class InternResource extends Resource
{
    protected static ?string $model = Intern::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationGroup = 'Intern Management';
    protected static ?int $navigationSort = 1;

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Intern Profile Details')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Identity & Profile')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                Grid::make(12)->schema([
                                    FileUpload::make('intern_image')
                                        ->label('Profile Photo')
                                        ->image()
                                        ->avatar()
                                        ->imageEditor()
                                        ->directory('intern-profiles')
                                        ->visibility('public')
                                        ->columnSpan(['sm' => 12, 'md' => 3]),

                                    Grid::make(2)->columnSpan(['sm' => 12, 'md' => 9])->schema([
                                        TextInput::make('name')
                                            ->label('Full Name')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('intern_code')
                                            ->label('Intern Code')
                                            ->placeholder('e.g. INT-001')
                                            ->maxLength(255),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->placeholder('+91 ...')
                                            ->maxLength(255),

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Account Active Status')
                                            ->helperText('Active interns appear in active rosters and can authenticate to the portal.')
                                            ->default(true),
                                    ]),
                                ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Academic Background')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('college')
                                        ->label('College / Institution')
                                        ->maxLength(255),

                                    TextInput::make('university')
                                        ->label('University')
                                        ->maxLength(255),

                                    TextInput::make('degree')
                                        ->label('Degree / Course')
                                        ->maxLength(255),

                                    TextInput::make('academic_year')
                                        ->label('Academic Year')
                                        ->placeholder('e.g. 3rd Year, 2026')
                                        ->maxLength(255),

                                    TextInput::make('cgpa')
                                        ->label('CGPA / Percentage')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100),

                                    TextInput::make('domain')
                                        ->label('Domain Interest')
                                        ->placeholder('e.g. Web Development, AI')
                                        ->maxLength(255),

                                    TextArea::make('skills')
                                        ->label('Skills / Competencies')
                                        ->placeholder('e.g. Laravel, React, Python')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Placement & Terms')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('internship_role')
                                        ->label('Internship Role')
                                        ->placeholder('e.g. Full Stack Developer')
                                        ->required(),

                                    TextInput::make('internship_position')
                                        ->label('Position')
                                        ->placeholder('e.g. Intern, Associate')
                                        ->maxLength(255),

                                    TextInput::make('working_hours')
                                        ->label('Working Hours')
                                        ->default('42 hours per week')
                                        ->maxLength(255),

                                    Select::make('internship_batch_id')
                                        ->label('Cohort Batch')
                                        ->relationship('batch', 'batch_name')
                                        ->searchable()
                                        ->preload()
                                        ->nullable(),

                                    Select::make('intern_team_id')
                                        ->label('Project Squad / Team')
                                        ->relationship('team', 'team_name')
                                        ->searchable()
                                        ->preload()
                                        ->nullable(),

                                    DatePicker::make('joining_date')
                                        ->label('Joining Date')
                                        ->native(false)
                                        ->displayFormat('d-m-Y'),

                                    DatePicker::make('completion_date')
                                        ->label('Completion Date')
                                        ->native(false)
                                        ->displayFormat('d-m-Y'),
                                ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Project & Completion')
                            ->icon('heroicon-m-document-check')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('project_name')
                                        ->label('Project Name')
                                        ->placeholder('e.g. FinTech Core Checkout')
                                        ->maxLength(255),

                                    TextInput::make('grade')
                                        ->label('Final Grade')
                                        ->placeholder('e.g. A+, O, A')
                                        ->maxLength(10),

                                    DatePicker::make('issuing_date')
                                        ->label('Certificate Issuing Date')
                                        ->native(false)
                                        ->displayFormat('d-m-Y'),
                                ]),

                                Grid::make(3)->schema([
                                    TextInput::make('cohort_archive_name')
                                        ->label('Archived Cohort Cycle')
                                        ->placeholder('e.g. 2025-2026 Annual Cohort')
                                        ->maxLength(255),

                                    Textarea::make('archive_note')
                                        ->label('Archival Action Note')
                                        ->placeholder('Milestones or remarks recorded during completion promotion')
                                        ->rows(2)
                                        ->columnSpan(2),
                                ]),

                                Grid::make(3)->schema([
                                    Select::make('completion_letter_template')
                                        ->label('Completion Letter Template')
                                        ->options([
                                            'bachelors' => 'Bachelor Degree Completion Letter',
                                            'masters' => 'Master Degree Completion Letter',
                                        ])
                                        ->native(false)
                                        ->placeholder('Select a template')
                                        ->columnSpan(1),
                                ]),

                                RichEditor::make('project_description')
                                    ->label('Project Description & Summary')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->defaultSort('intern_code', 'desc')
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 3,
            ])
            ->recordUrl(fn ($record) => Pages\ViewIntern::getUrl(['record' => $record]))
            ->columns([
                Tables\Columns\Layout\View::make('filament.intern-management.intern-card')
                    ->components([
                        TextColumn::make('intern_code')->searchable(),
                        TextColumn::make('name')->searchable(),
                        TextColumn::make('email')->searchable(),
                        TextColumn::make('cert_ref_id')->searchable(),
                        TextColumn::make('letter_ref_id')->searchable(),
                        TextColumn::make('offerletter.name')->searchable(),
                        TextColumn::make('offerletter.internship_role')->searchable(),
                        TextColumn::make('application.name')->searchable(),
                        TextColumn::make('batch.batch_name')->searchable(),
                        TextColumn::make('team.team_name')->searchable(),
                    ]),
            ])
            ->filters([
                SelectFilter::make('internship_batch_id')
                    ->relationship('batch', 'batch_name')
                    ->label('Cohort Batch')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('intern_team_id')
                    ->relationship('team', 'team_name')
                    ->label('Squad Team')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('completion_letter_template')
                    ->options([
                        'bachelors' => 'Bachelor Degree',
                        'masters' => 'Master Degree',
                    ])
                    ->label('Letter Template'),

                SelectFilter::make('is_active')
                    ->options([
                        1 => 'Active Interns',
                        0 => 'Inactive Interns',
                    ])
                    ->label('Account Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('completion_certificate')
                        ->label('Completion Certificate')
                        ->icon('heroicon-o-academic-cap')
                        ->color('warning')
                        ->modalHeading('Intern Completion Documents (Certificate & Letter)')
                        ->modalDescription('Verify or modify details to generate both the Certificate and Completion Letter at the same time.')
                        ->modalSubmitActionLabel(fn (Intern $record): string => 
                            ($record->completionCertificate()->exists() || filled($record->cert_ref_id) || $record->completionLetter()->exists() || filled($record->letter_ref_id))
                                ? 'Update & Re-generate Both'
                                : 'Generate Both Documents'
                        )
                        ->fillForm(fn (Intern $record): array => [
                            'template' => $record->completionLetter?->template 
                                ?? ($record->completion_letter_template ?? 'bachelors'),
                            'project_name' => $record->completionCertificate?->project_name 
                                ?? ($record->completionLetter?->project_name 
                                ?? ($record->team?->team_name 
                                ?? ($record->project_name ?? ''))),
                            'grade' => $record->completionCertificate?->grade 
                                ?? ($record->completionLetter?->grade 
                                ?? ($record->grade ?? 'A')),
                            'issuing_date' => $record->completionCertificate?->issuing_date 
                                ?? ($record->completionLetter?->issuing_date 
                                ?? ($record->issuing_date ?? now()->toDateString())),
                            'joining_date' => $record->completionCertificate?->joining_date 
                                ?? ($record->completionLetter?->joining_date 
                                ?? ($record->joining_date ?? $record->offerletter?->joining_date)),
                            'completion_date' => $record->completionCertificate?->completion_date 
                                ?? ($record->completionLetter?->completion_date 
                                ?? ($record->completion_date ?? $record->offerletter?->completion_date)),
                            'internship_role' => $record->completionCertificate?->internship_role 
                                ?? ($record->completionLetter?->internship_role 
                                ?? ($record->internship_role ?: ($record->offerletter?->internship_role ?? 'Software Development'))),
                            'working_hours' => $record->completionLetter?->working_hours 
                                ?? ($record->working_hours ?? '42 hours per week'),
                            'degree' => $record->completionLetter?->degree 
                                ?? ($record->degree ?? $record->offerletter?->degree),
                            'college' => $record->completionLetter?->college 
                                ?? ($record->college ?? $record->offerletter?->college),
                            'university' => $record->completionLetter?->university 
                                ?? ($record->university ?? $record->offerletter?->university),
                            'project_description' => $record->completionLetter?->project_description 
                                ?? ($record->team?->project_description ?? $record->project_description),
                        ])
                        ->form([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('template')
                                    ->label('Letter Template')
                                    ->options([
                                        'bachelors' => 'Bachelor Degree Completion Letter',
                                        'masters'   => 'Master Degree Completion Letter',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('project_name')
                                    ->label('Assigned Project')
                                    ->required(),

                                Forms\Components\TextInput::make('grade')
                                    ->label('Awarded Grade')
                                    ->placeholder('e.g. A+, O, A')
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\DatePicker::make('issuing_date')
                                    ->label('Document Issuing Date')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\DatePicker::make('joining_date')
                                    ->label('Joining Date'),

                                Forms\Components\DatePicker::make('completion_date')
                                    ->label('Completion Date'),
                            ]),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('internship_role')
                                    ->label('Internship Role'),

                                Forms\Components\TextInput::make('working_hours')
                                    ->label('Working Schedule'),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('degree')
                                    ->label('Degree / Course'),

                                Forms\Components\TextInput::make('college')
                                    ->label('College / Institution'),

                                Forms\Components\TextInput::make('university')
                                    ->label('University'),
                            ]),

                            Forms\Components\RichEditor::make('project_description')
                                ->label('Project Description')
                                ->columnSpanFull(),
                        ])
                        ->action(function (array $data, Intern $record) {
                            $service = app(CompletionDocumentService::class);
                            $cert = $service->generateCertificate($record, $data, auth()->user()?->email ?? 'Admin');
                            $letter = $service->generateLetter($record, $data, auth()->user()?->email ?? 'Admin');

                            Notification::make()
                                ->title("Certificate & Completion Letter Generated Successfully! 🎓📄")
                                ->body("Certificate: {$cert->cert_ref_id} • Letter: {$letter->letter_ref_id}")
                                ->success()
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view_cert')
                                        ->label('View Certificate')
                                        ->url(route('intern.certificate.view', ['id' => $record->id]), shouldOpenInNewTab: true),
                                    \Filament\Notifications\Actions\Action::make('view_letter')
                                        ->label('View Letter')
                                        ->url(route('intern.completion_letter.view', ['id' => $record->id]), shouldOpenInNewTab: true),
                                    \Filament\Notifications\Actions\Action::make('download_cert')
                                        ->label('Download Cert PDF')
                                        ->url(route('intern.certificate.download', ['id' => $record->id]), shouldOpenInNewTab: true),
                                    \Filament\Notifications\Actions\Action::make('download_letter')
                                        ->label('Download Letter PDF')
                                        ->url(route('intern.completion_letter.download', ['id' => $record->id]), shouldOpenInNewTab: true),
                                ])
                                ->persistent()
                                ->send();
                        }),

                    Tables\Actions\Action::make('view_id_card')
                        ->label('I-Card')
                        ->icon('heroicon-o-identification')
                        ->visible(fn ($record) => $record->offerletter?->is_accepted ?? false)
                        ->url(fn ($record) => route('print-id-card', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('view_completion_letter')
                        ->label('View Completion Letter')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->visible(fn (Intern $record) => 
                            filled($record->letter_ref_id) || 
                            (($record->offerletter?->is_accepted ?? false) && filled($record->completion_letter_template) && filled($record->project_name))
                        )
                        ->url(fn (Intern $record) => route('intern.completion_letter.view', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_completion_letter')
                        ->label('Download Completion Letter')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->visible(fn (Intern $record) => 
                            filled($record->letter_ref_id) || 
                            (($record->offerletter?->is_accepted ?? false) && filled($record->completion_letter_template) && filled($record->project_name))
                        )
                        ->url(fn (Intern $record) => route('intern.completion_letter.download', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('view_certificate')
                        ->label('View Certificate')
                        ->icon('heroicon-o-academic-cap')
                        ->color('warning')
                        ->visible(fn (Intern $record) => 
                            filled($record->cert_ref_id) || 
                            (($record->offerletter?->is_accepted ?? false) && filled($record->project_name))
                        )
                        ->url(fn (Intern $record) => route('intern.certificate.view', ['id' => $record->id]))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('print_certificate')
                        ->label('Download Certificate')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('warning')
                        ->visible(fn (Intern $record) => 
                            filled($record->cert_ref_id) || 
                            (($record->offerletter?->is_accepted ?? false) && filled($record->project_name))
                        )
                        ->url(fn (Intern $record) => route('intern.certificate.download', ['id' => $record->id]))
                        ->openUrlInNewTab(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button()
                ->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('mark_attendance_present')
                        ->label('Mark Attendance (Present)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $intern) {
                                \App\Models\Attendance::updateOrCreate(
                                    [
                                        'intern_id' => $intern->id,
                                        'date' => now()->toDateString(),
                                    ],
                                    [
                                        'status' => 'present',
                                    ]
                                );
                            }
                            Notification::make()
                                ->title('Attendance marked as Present for ' . count($records) . ' interns')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('mark_attendance_wfh')
                        ->label('Mark Attendance (WFH)')
                        ->icon('heroicon-o-home')
                        ->color('info')
                        ->action(function ($records) {
                            foreach ($records as $intern) {
                                \App\Models\Attendance::updateOrCreate(
                                    [
                                        'intern_id' => $intern->id,
                                        'date' => now()->toDateString(),
                                    ],
                                    [
                                        'status' => 'wfh',
                                    ]
                                );
                            }
                            Notification::make()
                                ->title('Attendance marked as WFH for ' . count($records) . ' interns')
                                ->success()
                                ->send();
                        }),

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
            'view' => Pages\ViewIntern::route('/{record}'),
            'edit' => Pages\EditIntern::route('/{record}/edit'),
            'certificate' => Pages\ViewCertificate::route('/{record}/certificate'),
        ];
    }
}
