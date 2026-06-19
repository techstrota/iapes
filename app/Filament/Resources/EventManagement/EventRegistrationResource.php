<?php

namespace App\Filament\Resources\EventManagement;

use App\Filament\Resources\EventManagement\EventRegistrationResource\Pages;
use App\Models\EventRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Section};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, SelectColumn};
use Filament\Tables\Actions\{Action, BulkAction, ActionGroup, EditAction, DeleteBulkAction};
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Browsershot\Browsershot;
use ZipArchive;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationGroup = 'Event Management';
    protected static ?int $navigationSort = 13;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Participant Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->regex('/^[a-zA-Z\s]+$/') // Only alphabets and spaces
                            ->validationMessages(['regex' => 'The name field must only contain alphabets and spaces.']),
                        TextInput::make('email')->email()->required(),
                        TextInput::make('phone')->tel(),
                        TextInput::make('institution'),
                        TextInput::make('certificate_number')
                            ->placeholder('Will be generated on issue')
                            ->disabled(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc') 
            ->poll('5s')
            ->columns([
                TextColumn::make('event.event_title')
                    ->label('Event Name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('event.event_type')
                    ->label('Event Type')
                    ->formatStateUsing(fn (string $state): string => str($state)->headline())
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Participant Name')
                    ->searchable(),

                TextColumn::make('certificate_number')
                    ->label('Certificate Number')
                    ->placeholder('Not Issued')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                SelectColumn::make('attendance_status')
                    ->label('Participation Status')
                    ->options([
                        'registered' => 'Registered',
                        'attended' => 'Attended',
                        'absent' => 'Absent',
                    ]),
            ])
            ->filters([
                 // 1. Event Type Filter
            SelectFilter::make('event_type')
                ->label('Event Type')
                ->options(fn () => \App\Models\Event::query()
                    ->whereNotNull('event_type')
                    ->distinct()
                    ->pluck('event_type', 'event_type')
                    ->mapWithKeys(fn ($type) => [
                        $type => str($type)->headline() // Capitalizes and formats (e.g., 'event_type' -> 'Event Type')
                        ])
                    ->toArray()
                )
                ->query(function (Builder $query, array $data) {
                    if ($data['value']) {
                        $query->whereHas('event', fn ($q) => $q->where('event_type', $data['value']));
                    }
                }),

                    // 2. Dependent Event Title Filter
               Tables\Filters\SelectFilter::make('event')
                    ->label('Event Name')
                    ->relationship('event', 'event_title', function (Builder $query, $livewire) {
                        // Access the table's current filter states via the Livewire component
                        // The path is usually tableFilters.{filterName}.{key}
                        $eventType = $livewire->tableFilters['event_type']['value'] ?? null;

                        return $query->when($eventType, fn ($q) => $q->where('event_type', $eventType));
                    })
                    ->searchable()
                    ->preload()

            ])
            ->actions([
                ActionGroup::make([

                // 1. VIEW CERTIFICATE (New Action)
                    Action::make('viewCertificate')
                        ->label('View Certificate')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        // Only show if issued
                        ->visible(fn ($record) => $record->certificate_number !== null)
                        ->modalHeading('Certificate Preview')
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false) // Hide the "Submit" button
                        ->modalCancelActionLabel('Close')
                        ->modalContent(fn (EventRegistration $record) => view(
                            'event.certificate', 
                            [
                                'registration' => $record,
                                'isPdf' => false // This ensures asset() is used for logos instead of public_path()
                            ]
                        )),
                    // Generate Certificate Number
                    Action::make('issueCertificate')
                        ->label('Issue Certificate')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->requiresConfirmation()
                        // HIDDEN logic: Hide if certificate exists OR if the event is still upcoming
                        ->hidden(fn ($record) => 
                            $record->certificate_number !== null || 
                            $record->event?->event_status === 'upcoming'
                        )
                        ->action(function ($record) {
                            // Extra check inside the action
                            if ($record->event?->event_status === 'upcoming') {
                                Notification::make()
                                    ->title('Cannot Issue Certificate')
                                    ->body('Certificates cannot be issued for upcoming events.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            $record->update([
                                'attendance_status' => 'attended',
                                'certificate_number' => $record->generateCertificateNumber(),
                                'certificate_issued' => true,
                            ]);

                            Notification::make()
                                ->title('Certificate Issued Successfully')
                                ->success()
                                ->send();
                        }),

                    // Download Single PDF
                    Action::make('downloadPdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->visible(fn ($record) => $record->certificate_number !== null)
                        ->action(fn (EventRegistration $record) => static::downloadSinglePdf($record)),

                    EditAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk Print (One PDF with many pages)
                    BulkAction::make('bulk_print_pdf')
                        ->label('Bulk Print Certs')
                        ->icon('heroicon-o-printer')
                       ->action(function ($records) {
                            // Filter out records where the event is still upcoming
                            $filteredRecords = $records->filter(fn ($record) => $record->event?->event_status !== 'upcoming');

                            if ($filteredRecords->isEmpty()) {
                                Notification::make()
                                    ->title('No valid records')
                                    ->body('None of the selected participants belong to completed events.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            return static::downloadBulkPdf($filteredRecords);
                        }),

                    // Bulk ZIP (Multiple PDF files)
                    BulkAction::make('bulk_zip_download')
                        ->label('Bulk Download Certs (ZIP)')
                        ->icon('heroicon-o-archive-box')
                        ->action(function ($records) {
                        // Filter out records where the event is still upcoming
                        $filteredRecords = $records->filter(fn ($record) => $record->event?->event_status !== 'upcoming');

                        if ($filteredRecords->isEmpty()) {
                            Notification::make()
                                ->title('Action Aborted')
                                ->body('You cannot generate ZIP files for upcoming events.')
                                ->danger()
                                ->send();
                            return;
                        }

                        return static::downloadBulkZip($filteredRecords);
                    }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventRegistrations::route('/'),
            'create' => Pages\CreateEventRegistration::route('/create'),
            'edit' => Pages\EditEventRegistration::route('/{record}/edit'),
        ];
    }

    // --- Browsershot Helpers ---

    protected static function getBrowsershotInstance(string $html): Browsershot
    {
        return Browsershot::html($html)
            ->setNodeBinary(env('NODE_PATH', 'C:\Program Files\nodejs\node.exe'))
            ->setNpmBinary(env('NPM_PATH', 'C:\Program Files\nodejs\npm.cmd'))
            ->setChromePath(env('CHROME_PATH'))
            ->noSandbox()
            ->landscape() // Certificate orientation
            ->format('A4')
            ->showBackground()
            ->timeout(120)
            ->waitUntilNetworkIdle();
    }

    public static function downloadSinglePdf(EventRegistration $record)
    {
        if (!$record->certificate_number) {
            $record->update(['certificate_number' => $record->generateCertificateNumber()]);
        }

        // Passes 'registration' variable to the blade
        $html = view("event.certificate", ['registration' => $record])->render();
        $pdf = static::getBrowsershotInstance($html)->pdf();

        return response()->streamDownload(
            fn () => print($pdf),
            "Certificate-{$record->certificate_number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    protected static function downloadBulkPdf($records)
    {
        foreach ($records as $record) {
            if (!$record->certificate_number) {
                $record->update(['certificate_number' => $record->generateCertificateNumber()]);
            }
        }

        // Passes 'registrations' (plural) to the blade
        $html = View::make("event.premium-certificate", ['registrations' => $records])->render();
        $pdf = static::getBrowsershotInstance($html)->pdf();

        return response()->streamDownload(
            fn () => print($pdf), 
            "Bulk_Event_Certificates_" . now()->format('Y-m-d') . ".pdf"
        );
    }

    protected static function downloadBulkZip($records) 
    {
        $zip = new ZipArchive;
        $zipFileName = 'event_certificates_' . now()->timestamp . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            foreach ($records as $record) {
                if (!$record->certificate_number) {
                    $record->update(['certificate_number' => $record->generateCertificateNumber()]);
                }

                $html = View::make("event.premium-certificate", ['registration' => $record])->render();
                $pdf = static::getBrowsershotInstance($html)->pdf();
                
                $zip->addFromString("Certificate_{$record->certificate_number}.pdf", $pdf);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}