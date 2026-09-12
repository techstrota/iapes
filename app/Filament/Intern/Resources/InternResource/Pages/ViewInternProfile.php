<?php

namespace App\Filament\Intern\Resources\InternResource\Pages;

use App\Filament\Intern\Resources\InternResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Hash;
//use Filament\Forms\Components\{TextInput, TextArea, FileUpload, Select, DatePicker, TimePicker, Section, Grid};
//use Filament\Tables\Actions\{Action, BulkAction};
//use Filament\Tables\Columns\{TextColumn, BadgeColumn, IconColumn};


class ViewInternProfile extends ViewRecord
{
    protected static string $resource = InternResource::class;

    // ── Page Title ───────────────────────────────────────────────────
    public function getTitle(): string
    {
        return $this->record->name . ' — Profile';
    }

    protected function getHeaderActions(): array
    {
        return [

            // ── NEW: Upload Profile Picture Action ──
                Actions\Action::make('updateProfilePicture')
                ->label('Upload Photo')
                ->icon('heroicon-o-camera')
                ->color('info')
                ->form([
                    Forms\Components\FileUpload::make('intern_image')
                            ->label('Profile Picture')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->directory('intern-profiles')
                            ->visibility('public')
                            // ── Dynamic Filename Logic ──
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file, $record): string => (string) str($record->intern_code)
                                    ->replace('/', '-') // Changes TS26/WD/001 to TS26-WD-001
                                    ->prepend('profile-')
                                    ->append('.' . $file->getClientOriginalExtension()),
                            )
                                                    ->required(),
                            ])
                                ->action(function (array $data, $record) {
                                    $record->update([
                                        'intern_image' => $data['intern_image'],
                                    ]);

                                    Notification::make()
                                        ->title('Profile picture updated!')
                                        ->success()
                                        ->send();
                                })
                                ->modalHeading('Upload Your Picture')
                                ->modalSubmitActionLabel('Save Photo'),

            //-------------------------------------------------------------------------                                
            // Actions\Action::make('viewCertificate')
            //     ->label('View Certificate')
            //     ->icon('heroicon-o-academic-cap')
            //     ->color('success')
            //     ->visible(fn ($record) => $record->offerLetter?->is_accepted ?? false)
            //     ->url(fn ($record): string => route('intern.certificate.view', ['id' => $record->id]))
            //     ->openUrlInNewTab(),
            Actions\Action::make('viewIdCard')
                ->label('View ID Card')
                ->icon('heroicon-o-identification')
                ->color('primary')
                ->url(fn ($record) => route('print-id-card', ['id' => $record->id]))
                ->openUrlInNewTab()

            // Actions\Action::make('updatePassword')
            //     ->label('Reset Password')
            //     ->icon('heroicon-o-key')
            //     ->color('warning')
            //     // This is the modal popup
            //     ->form([
            //         Forms\Components\Section::make('Security')
            //             ->description('Update your account password here.')
            //             ->schema([
            //                 Forms\Components\TextInput::make('password')
            //                     ->label('New Password')
            //                     ->password()
            //                     ->revealable()
            //                     ->required() // Required in this specific modal
            //                     ->minLength(7)
            //                     ->same('password_confirmation')
            //                     ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

            //                 Forms\Components\TextInput::make('password_confirmation')
            //                     ->label('Confirm New Password')
            //                     ->password()
            //                     ->revealable()
            //                     ->required()
            //                     ->dehydrated(false),
            //             ])->columns(2),
            //     ])
                ->action(function (array $data, $record) {
                    // This logic saves the hashed password to the database
                    $record->update([
                        'password' => $data['password'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Password Reset Successfully')
                        ->success()
                        ->send();
                })
                ->modalWidth('lg')
                ->modalHeading('Reset Your Password')
                ->modalSubmitActionLabel('Update Password'),
        ];
    }
    
    // ── Infolist ─────────────────────────────────────────────────────
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ── Row 1: Identity + Internship Details + Batch & Team ──
            Grid::make(3)->schema([

                
                // ── Card 1: Basic Information ──
                Section::make('Basic Information')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(2) // Create a 2-column split inside the card
                            ->schema([
                                // Left Side: Text Details
                                Grid::make(1)
                                    ->columnSpan(1)
                                    ->schema([
                                        TextEntry::make('intern_code')
                                            ->label('Intern ID')
                                            ->badge()
                                            ->color('primary')
                                            ->copyable()
                                            ->size(TextEntrySize::Large),

                                        TextEntry::make('name')
                                            ->label('Full Name')
                                            ->weight(FontWeight::Bold)
                                            ->size(TextEntrySize::Large),
                                ]),

                    // Right Side: Profile Image
                    ImageEntry::make('intern_image')
                        ->label(false)
                        ->disk('public') // Explicitly tell it to use the public disk
                        ->visibility('public')
                        // ->pathStrategy(\Filament\Infolists\Components\ImageEntry\PathStrategy::Relative)
                        ->grow(false)
                        ->alignEnd()
                        ->columnSpan(1)
                        ->extraImgAttributes([
                            'class' => 'ring-2 ring-primary-500/20 shadow-md',
                        ]),
            ]),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->copyMessage('Email copied!'),

                        TextEntry::make('application.phone')
                            ->label('Phone Number')
                            ->icon('heroicon-m-phone')
                            ->default('Not provided'),

                        TextEntry::make('is_active')
                            ->label('Current Status')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Active'     => 'success',
                                'Completed'  => 'gray',
                                'Terminated' => 'danger',
                                default      => 'gray',
                            }),
                    ]),

                // ── Card 2: Internship Details ──
                Section::make('Internship Details')
                    ->icon('heroicon-o-briefcase')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('offerletter.internship_role')
                            ->label('Internship Type')
                            ->default('No Role Found') // If you see this, the relationship is failing
                            ->badge(),

                        TextEntry::make('application.degree')
                            ->label('Course / Degree')
                            ->icon('heroicon-m-academic-cap'),

                        TextEntry::make('application.college')
                            ->label('Department')
                            ->icon('heroicon-m-building-office-2')
                            ->default('Not assigned'),

                        TextEntry::make('internship_period')
                            ->label('Internship Period')
                            ->icon('heroicon-m-calendar')
                            ->getStateUsing(function ($record) {
                                $joining = $record->offerletter?->joining_date;
                                $completion = $record->offerletter?->completion_date;

                                if (!$joining && !$completion) {
                                    return 'Not scheduled';
                                }

                                $start = $joining ? \Carbon\Carbon::parse($joining)->format('d M Y') : '...';
                                $end = $completion ? \Carbon\Carbon::parse($completion)->format('d M Y') : '...';

                                return "{$start} - {$end}";
                            }),

                        TextEntry::make('is_active') // Using your boolean field
                            ->label('Account Status')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ]),

                // ── NEW Card 3: Batch & Team Data ──
                Section::make('Batch & Team')
                    ->icon('heroicon-o-users')
                    ->columnSpan(1)
                    ->schema([
                        // Batch Name & Timing
                        TextEntry::make('batch.batch_name') // Adjust 'batch_name' to your actual column name
                            ->label('Current Batch')
                            ->weight(FontWeight::Bold)
                            ->color('info'),

                        TextEntry::make('batch.batch_timing') // Adjust to your actual column name
                            ->label('Batch Timing')
                            ->icon('heroicon-m-clock')
                            ->default('Not Set'),

                        TextEntry::make('team.team_name') // Adjust to your actual column name
                            ->label('Team Name')
                            ->badge()
                            ->color('success')
                            ->separator(', '),

                        // Teammates List
                        RepeatableEntry::make('teammates')
                            ->label('My Teammates')
                            ->schema([
                                TextEntry::make('name')
                                    ->label(null) // Keeps it clean
                                    ->icon('heroicon-m-user')
                                    ->size(TextEntrySize::Small),
                            ])
                            ->columns(1)
                            ->grid(1)
                            ->visible(fn ($record) => $record->intern_team_id !== null),
                    ]),
            ]),
        ]);
    }

}
