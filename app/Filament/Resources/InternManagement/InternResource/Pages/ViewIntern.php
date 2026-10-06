<?php

namespace App\Filament\Resources\InternManagement\InternResource\Pages;

use App\Filament\Resources\InternManagement\InternResource;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Carbon;
use App\Services\CompletionDocumentService;

class ViewIntern extends ViewRecord
{
    protected static string $resource = InternResource::class;

    protected static string $view = 'filament.intern-management.intern-resource.pages.view-intern';

    public function getTitle(): string
    {
        $name = $this->record->name
            ?: ($this->record->offerletter?->name
            ?? ($this->record->application?->name
            ?? 'Intern Profile'));

        return "{$name} ({$this->record->intern_code})";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Details')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->url(fn (Intern $record): string => InternResource::getUrl('edit', ['record' => $record])),

            Actions\Action::make('completion_certificate')
                ->label('Completion Certificate')
                ->icon('heroicon-o-academic-cap')
                ->color('warning')
                ->modalHeading('Intern Completion Documents (Certificate & Letter)')
                ->modalDescription('Verify or enter completion grading, project, and tenure details to generate both the Certificate and Completion Letter at the same time.')
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
                    Forms\Components\Placeholder::make('existing_docs_notice')
                        ->label('Generated Documents Available')
                        ->visible(fn (Intern $record): bool => 
                            filled($record->cert_ref_id) || 
                            filled($record->letter_ref_id) || 
                            $record->completionCertificate()->exists() || 
                            $record->completionLetter()->exists() ||
                            filled($record->cert_token)
                        )
                        ->content(function (Intern $record) {
                            $certUrl = route('intern.certificate.view', ['id' => $record->id]);
                            $certPdf = route('intern.certificate.download', ['id' => $record->id]);
                            $letterUrl = route('intern.completion_letter.view', ['id' => $record->id]);
                            $letterPdf = route('intern.completion_letter.download', ['id' => $record->id]);
                            
                            $certRef = $record->cert_ref_id ?: ($record->completionCertificate?->cert_ref_id ?: 'Generated');
                            $letRef = $record->letter_ref_id ?: ($record->completionLetter?->letter_ref_id ?: 'Generated');

                            return new \Illuminate\Support\HtmlString("
                                <div style='background: #090e1c; border: 1.5px solid rgba(245, 158, 11, 0.4); border-radius: 10px; padding: 12px 16px; margin-bottom: 8px;'>
                                    <div style='display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;'>
                                        <span style='font-size: 11px; font-weight: 700; color: #fbbf24; text-transform: uppercase; letter-spacing: 0.05em;'>
                                            ⚡ Documents Already Generated
                                        </span>
                                        <span style='font-size: 10px; font-weight: 700; color: #4edea3; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 6px; border-radius: 4px;'>
                                            READY TO VIEW
                                        </span>
                                    </div>
                                    <div style='display: flex; flex-wrap: wrap; gap: 8px;'>
                                        <a href='{$certUrl}' target='_blank' style='display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; background: rgba(245, 158, 11, 0.15); color: #fbbf24; text-decoration: none; border: 1px solid rgba(245, 158, 11, 0.3);'>
                                            🎓 View Certificate ({$certRef}) ↗
                                        </a>
                                        <a href='{$certPdf}' target='_blank' style='display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #17223b; color: #b8c4ff; text-decoration: none; border: 1px solid #222a3d;'>
                                            ⬇ Download Cert PDF
                                        </a>
                                        <a href='{$letterUrl}' target='_blank' style='display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; background: rgba(16, 185, 129, 0.15); color: #4edea3; text-decoration: none; border: 1px solid rgba(16, 185, 129, 0.3);'>
                                            📄 View Letter ({$letRef}) ↗
                                        </a>
                                        <a href='{$letterPdf}' target='_blank' style='display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #17223b; color: #b8c4ff; text-decoration: none; border: 1px solid #222a3d;'>
                                            ⬇ Download Letter PDF
                                        </a>
                                    </div>
                                    <div style='font-size: 11px; color: #94a3b8; margin-top: 8px;'>
                                        ℹ️ You can review or edit the fields below and submit to update both documents simultaneously.
                                    </div>
                                </div>
                            ");
                        })
                        ->columnSpanFull(),

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
                            ->label('Grade Awarded')
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
                            ->label('Internship Role')
                            ->placeholder('e.g. Software Development'),

                        Forms\Components\TextInput::make('working_hours')
                            ->label('Working Schedule / Hours')
                            ->placeholder('e.g. 42 hours per week'),
                    ]),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('degree')
                            ->label('Degree / Course'),

                        Forms\Components\TextInput::make('college')
                            ->label('College / Institution'),

                        Forms\Components\TextInput::make('university')
                            ->label('Affiliated University'),
                    ]),

                    Forms\Components\RichEditor::make('project_description')
                        ->label('Project Description & Summary')
                        ->columnSpanFull(),
                ])
                ->extraModalFooterActions(fn (Intern $record): array => array_filter([
                    (filled($record->cert_ref_id) || $record->completionCertificate()->exists() || filled($record->cert_token))
                        ? Actions\Action::make('modal_view_cert')
                            ->label('View Certificate ↗')
                            ->icon('heroicon-o-academic-cap')
                            ->color('warning')
                            ->url(route('intern.certificate.view', ['id' => $record->id]))
                            ->openUrlInNewTab()
                        : null,
                    (filled($record->letter_ref_id) || $record->completionLetter()->exists() || (filled($record->completion_letter_template) && filled($record->project_name)))
                        ? Actions\Action::make('modal_view_letter')
                            ->label('View Completion Letter ↗')
                            ->icon('heroicon-o-document-text')
                            ->color('success')
                            ->url(route('intern.completion_letter.view', ['id' => $record->id]))
                            ->openUrlInNewTab()
                        : null,
                ]))
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
                                ->icon('heroicon-o-academic-cap')
                                ->url(route('intern.certificate.view', ['id' => $record->id]), shouldOpenInNewTab: true),
                            \Filament\Notifications\Actions\Action::make('view_letter')
                                ->label('View Letter')
                                ->icon('heroicon-o-document-text')
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

            Actions\Action::make('view_offer_letter')
                ->label('Offer Letter')
                ->icon('heroicon-o-document-check')
                ->color('info')
                ->visible(fn (Intern $record) => filled($record->offer_letter_id))
                ->url(fn (Intern $record) => route('view-offer-pdf', ['id' => $record->offer_letter_id]))
                ->openUrlInNewTab(),

            Actions\Action::make('uploadPhoto')
                ->label('Upload Photo')
                ->icon('heroicon-o-camera')
                ->color('gray')
                ->form([
                    Forms\Components\FileUpload::make('intern_image')
                        ->label('Profile Picture')
                        ->image()
                        ->avatar()
                        ->imageEditor()
                        ->directory('intern-profiles')
                        ->visibility('public')
                        ->getUploadedFileNameForStorageUsing(
                            fn ($file, $record): string => (string) str($record->intern_code ?: 'intern')
                                ->replace('/', '-')
                                ->prepend('profile-')
                                ->append('.' . $file->getClientOriginalExtension()),
                        )
                        ->required(),
                ])
                ->action(function (array $data, Intern $record) {
                    $record->update([
                        'intern_image' => $data['intern_image'],
                    ]);

                    Notification::make()
                        ->title('Profile picture updated successfully')
                        ->success()
                        ->send();
                })
                ->modalHeading('Upload Intern Photo')
                ->modalSubmitActionLabel('Save Photo'),

            Actions\Action::make('view_id_card')
                ->label('I-Card')
                ->icon('heroicon-o-identification')
                ->color('gray')
                ->url(fn (Intern $record) => route('print-id-card', ['id' => $record->id]))
                ->openUrlInNewTab(),

            Actions\Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->modalHeading('Change Intern Password')
                ->modalDescription(fn (Intern $record) => "Set a new login password for {$record->name} ({$record->intern_code}). Both the hashed password and the plain-text reference will be updated immediately.")
                ->modalSubmitActionLabel('Update Password')
                ->modalWidth('md')
                ->form([
                    Forms\Components\Placeholder::make('login_id_info')
                        ->label('Intern Login User ID')
                        ->content(fn (Intern $record) => $record->username ?: $record->email),
                    Forms\Components\TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(6)
                        ->helperText('Minimum 6 characters. Click the refresh icon to auto-generate a secure random password.')
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('generateRandom')
                                ->icon('heroicon-m-arrow-path')
                                ->tooltip('Generate Random Secure Password')
                                ->action(function (Forms\Set $set) {
                                    $generated = 'ts' . now()->format('y') . strtolower(\Illuminate\Support\Str::random(4)) . rand(10, 99);
                                    $set('new_password', $generated);
                                })
                        ),
                ])
                ->action(function (array $data, Intern $record) {
                    $newPassword = $data['new_password'];

                    $record->update([
                        'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
                        'plain_password' => $newPassword,
                    ]);

                    Notification::make()
                        ->title('Password updated successfully! 🔑')
                        ->body("The new password has been saved for {$record->name}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // ── Section 1: Profile & Identity Overview (Stitch Style) ──
            Section::make('Intern Overview')
                ->icon('heroicon-o-identification')
                ->schema([
                    Grid::make(12)->schema([
                        ImageEntry::make('intern_image')
                            ->label(false)
                            ->disk('public')
                            ->visibility('public')
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?: ($record->offerletter?->name ?? $record->application?->name ?? 'Intern')) . '&background=1e40af&color=ffffff&size=128')
                            ->circular()
                            ->columnSpan(['sm' => 12, 'md' => 2])
                            ->extraImgAttributes([
                                'class' => 'ring-4 ring-primary-500/20 shadow-lg object-cover w-24 h-24',
                            ]),

                        Grid::make(1)
                            ->columnSpan(['sm' => 12, 'md' => 6])
                            ->schema([
                                TextEntry::make('intern_name_display')
                                    ->label(false)
                                    ->getStateUsing(fn ($record) => $record->name ?: ($record->offerletter?->name ?? $record->application?->name))
                                    ->weight(FontWeight::Bold)
                                    ->size(TextEntrySize::Large),

                                TextEntry::make('internship_role')
                                    ->label(false)
                                    ->getStateUsing(fn ($record) => $record->internship_role ?: ($record->offerletter?->internship_role ?? 'Role Not Assigned'))
                                    ->color('gray')
                                    ->icon('heroicon-m-briefcase'),

                                TextEntry::make('institution_display')
                                    ->label(false)
                                    ->getStateUsing(fn ($record) => $record->university ?: ($record->college ?: ($record->offerletter?->university ?? $record->offerletter?->college ?? $record->application?->college ?? 'College Not Recorded')))
                                    ->icon('heroicon-m-academic-cap')
                                    ->color('gray'),
                            ]),

                        Grid::make(1)
                            ->columnSpan(['sm' => 12, 'md' => 4])
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('intern_code')
                                        ->label('Intern ID')
                                        ->badge()
                                        ->color('primary')
                                        ->copyable(),

                                    TextEntry::make('application.application_code')
                                        ->label('Application ID')
                                        ->badge()
                                        ->color('gray')
                                        ->copyable()
                                        ->placeholder('None'),
                                ]),

                                Grid::make(2)->schema([
                                    TextEntry::make('is_active')
                                        ->label('Account')
                                        ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                                        ->badge()
                                        ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                                    TextEntry::make('internship_status')
                                        ->label('Tenure')
                                        ->badge()
                                        ->getStateUsing(function ($record) {
                                            $completionDate = $record->completion_date ?: $record->offerletter?->completion_date;
                                            if (!$completionDate) return 'On-going';
                                            return Carbon::parse($completionDate)->isPast() ? 'Completed' : 'On-going';
                                        })
                                        ->color(fn ($state) => $state === 'Completed' ? 'info' : 'success'),
                                ]),
                            ]),
                    ]),
                ]),

            // ── Section 2: Key Metric Ribbon (At-a-Glance) ──
            Grid::make(3)->schema([
                Section::make('Internship Duration')
                    ->icon('heroicon-o-calendar-days')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('tenure_period')
                            ->label(false)
                            ->getStateUsing(function ($record) {
                                $start = $record->joining_date ?: $record->offerletter?->joining_date;
                                $end = $record->completion_date ?: $record->offerletter?->completion_date;

                                if (!$start && !$end) {
                                    if ($record->application?->duration) {
                                        return $record->application->duration . ' ' . ($record->application->duration_unit ?? 'months');
                                    }
                                    return 'Not scheduled';
                                }

                                $startDate = $start ? Carbon::parse($start)->format('d M Y') : 'Start TBD';
                                $endDate = $end ? Carbon::parse($end)->format('d M Y') : 'End TBD';

                                if ($start && $end) {
                                    $diffMonths = (int) round(Carbon::parse($start)->floatDiffInMonths(Carbon::parse($end)));
                                    $monthStr = $diffMonths > 0 ? " ({$diffMonths} " . ($diffMonths === 1 ? 'Month' : 'Months') . ')' : '';
                                    return "{$startDate} → {$endDate}{$monthStr}";
                                }

                                return "{$startDate} → {$endDate}";
                            })
                            ->weight(FontWeight::SemiBold)
                            ->size(TextEntrySize::Large),

                        TextEntry::make('tenure_subtext')
                            ->label(false)
                            ->getStateUsing(function ($record) {
                                $end = $record->completion_date ?: $record->offerletter?->completion_date;
                                if (!$end) return 'Active tenure';
                                $endDate = Carbon::parse($end);
                                if ($endDate->isPast()) {
                                    return 'Tenure concluded ' . $endDate->diffForHumans();
                                }
                                return 'Concluding ' . $endDate->diffForHumans();
                            })
                            ->color('gray'),
                    ]),

                Section::make('Login Credentials')
                    ->icon('heroicon-o-key')
                    ->columnSpan(1)
                    ->schema([
                        ViewEntry::make('login_credentials')
                            ->label(false)
                            ->view('filament.intern-management.partials.credential-credentials'),
                    ]),

                Section::make('Project & Grading')
                    ->icon('heroicon-o-academic-cap')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('assigned_project')
                            ->label(false)
                            ->getStateUsing(fn ($record) => $record->team?->team_name ?: ($record->project_name ?: 'Project not assigned'))
                            ->weight(FontWeight::SemiBold)
                            ->size(TextEntrySize::Large),

                        TextEntry::make('project_track_or_desc')
                            ->label(false)
                            ->getStateUsing(function ($record) {
                                if ($record->team?->track) {
                                    return $record->team->track . ($record->team->mentor_name ? ' • Mentor: ' . $record->team->mentor_name : '');
                                }
                                if ($record->team?->project_description) {
                                    return \Illuminate\Support\Str::limit(strip_tags($record->team->project_description), 60);
                                }
                                return $record->intern_team_id ? 'Assigned to Squad #' . $record->intern_team_id : 'No team assigned';
                            })
                            ->color('gray')
                            ->size(TextEntrySize::Small),

                        Grid::make(2)->schema([
                            TextEntry::make('grade')
                                ->label('Grade')
                                ->badge()
                                ->color(fn ($state) => filled($state) ? 'success' : 'gray')
                                ->formatStateUsing(fn ($state) => filled($state) ? $state : '—')
                                ->placeholder('—'),

                            TextEntry::make('completion_letter_template')
                                ->label('Letter Template')
                                ->badge()
                                ->formatStateUsing(fn ($state) => filled($state) ? ucfirst($state) : '—')
                                ->color(fn ($state) => filled($state) ? 'primary' : 'gray')
                                ->placeholder('—'),
                        ]),
                    ]),
            ]),

            // ── Section 3: Personal & Academic Background ──
            Grid::make(2)->schema([
                Section::make('Personal & Contact Background')
                    ->icon('heroicon-o-user')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('email')
                                ->label('Email Address')
                                ->icon('heroicon-m-envelope')
                                ->copyable()
                                ->copyMessage('Email copied to clipboard'),

                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->icon('heroicon-m-phone')
                                ->getStateUsing(fn ($record) => $record->phone ?: ($record->offerletter?->phone ?? $record->application?->phone ?? 'Not provided'))
                                ->copyable(),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('degree')
                                ->label('Degree / Course')
                                ->icon('heroicon-m-academic-cap')
                                ->getStateUsing(fn ($record) => $record->degree ?: ($record->offerletter?->degree ?? $record->application?->degree ?? 'Not specified')),

                            TextEntry::make('academic_year')
                                ->label('Academic Year')
                                ->icon('heroicon-m-calendar')
                                ->getStateUsing(fn ($record) => $record->academic_year ?: ($record->application?->year ?? 'Not specified')),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('college')
                                ->label('College / Institution')
                                ->icon('heroicon-m-building-office-2')
                                ->getStateUsing(fn ($record) => $record->college ?: ($record->offerletter?->college ?? $record->application?->college ?? 'Not specified')),

                            TextEntry::make('university')
                                ->label('Affiliated University')
                                ->icon('heroicon-m-building-library')
                                ->getStateUsing(fn ($record) => $record->university ?: ($record->offerletter?->university ?? $record->college ?? 'Not specified')),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('cgpa')
                                ->label('CGPA / Percentage')
                                ->getStateUsing(fn ($record) => $record->cgpa ?: ($record->application?->cgpa ?? 'Not recorded')),

                            TextEntry::make('domain')
                                ->label('Domain Expertise')
                                ->badge()
                                ->color('info')
                                ->getStateUsing(fn ($record) => $record->domain ?: ($record->application?->domain ?? 'Not specified')),
                        ]),

                        TextEntry::make('skills')
                            ->label('Skills')
                            ->icon('heroicon-m-sparkles')
                            ->getStateUsing(fn ($record) => $record->skills ?: ($record->application?->skills ?? 'No specific skills listed'))
                            ->columnSpanFull(),
                    ]),

                // ── Section 4: Internship & Offer Letter Details ──
                Section::make('Internship & Offer Letter Agreement')
                    ->icon('heroicon-o-briefcase')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('internship_role')
                                ->label('Role')
                                ->badge()
                                ->color('primary')
                                ->getStateUsing(fn ($record) => $record->internship_role ?: ($record->offerletter?->internship_role ?? 'Not Assigned')),

                            TextEntry::make('internship_position')
                                ->label('Position Title')
                                ->getStateUsing(fn ($record) => $record->internship_position ?: ($record->offerletter?->internship_position ?? 'Not Assigned')),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('joining_date')
                                ->label('Joining Date')
                                ->icon('heroicon-m-calendar')
                                ->date('d M Y')
                                ->getStateUsing(fn ($record) => $record->joining_date ?: $record->offerletter?->joining_date)
                                ->placeholder('TBD'),

                            TextEntry::make('completion_date')
                                ->label('Completion Date')
                                ->icon('heroicon-m-calendar-days')
                                ->date('d M Y')
                                ->getStateUsing(fn ($record) => $record->completion_date ?: $record->offerletter?->completion_date)
                                ->placeholder('TBD'),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('working_hours')
                                ->label('Working Schedule')
                                ->icon('heroicon-m-clock')
                                ->getStateUsing(fn ($record) => $record->working_hours ?: ($record->offerletter?->working_hours ?? '42 hours per week')),

                            TextEntry::make('offerletter.offer_issue_date')
                                ->label('Offer Issue Date')
                                ->icon('heroicon-m-paper-airplane')
                                ->date('d M Y')
                                ->placeholder('Not recorded'),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('offerletter.offer_status')
                                ->label('Offer Status')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'accepted' => 'success',
                                    'draft' => 'warning',
                                    'rejected' => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn ($state) => ucfirst($state ?? 'Unknown')),

                            TextEntry::make('offerletter.is_accepted')
                                ->label('Candidate Acceptance')
                                ->badge()
                                ->formatStateUsing(fn ($state) => $state ? 'Accepted' : 'Pending Acceptance')
                                ->color(fn ($state) => $state ? 'success' : 'warning'),
                        ]),

                        TextEntry::make('view_offer_doc')
                            ->label('Offer Letter Document')
                            ->getStateUsing(fn ($record) => filled($record->offer_letter_id) ? '📄 View Signed Offer Letter PDF (Click to Open)' : 'No Offer Letter linked')
                            ->url(fn ($record) => filled($record->offer_letter_id) ? route('view-offer-pdf', ['id' => $record->offer_letter_id]) : null, shouldOpenInNewTab: true)
                            ->color('primary')
                            ->columnSpanFull(),
                    ]),
            ]),

            // ── Section 5: Batch & Team Placement ──
            Section::make('Batch & Team Placement')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('batch.batch_name')
                            ->label('Cohort Batch')
                            ->weight(FontWeight::Bold)
                            ->badge()
                            ->color('primary')
                            ->placeholder('Batch not assigned'),

                        TextEntry::make('batch.batch_timing')
                            ->label('Batch Timing')
                            ->icon('heroicon-m-clock')
                            ->placeholder('Not scheduled'),

                        TextEntry::make('team.team_name')
                            ->label('Assigned Squad / Team')
                            ->badge()
                            ->color('success')
                            ->placeholder('Project not assigned'),
                    ]),

                    RepeatableEntry::make('teammates')
                        ->label('Squad Teammates (Co-Interns)')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('name')
                                    ->label(false)
                                    ->getStateUsing(fn ($record) => $record->name ?: ($record->offerletter?->name ?? $record->application?->name))
                                    ->icon('heroicon-m-user')
                                    ->weight(FontWeight::SemiBold),

                                TextEntry::make('intern_code')
                                    ->label(false)
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('internship_role')
                                    ->label(false)
                                    ->getStateUsing(fn ($record) => $record->internship_role ?: ($record->offerletter?->internship_role ?? 'Intern'))
                                    ->color('gray')
                                    ->placeholder('Intern'),
                            ]),
                        ])
                        ->columns(1)
                        ->visible(fn ($record) => filled($record->intern_team_id)),
                ]),

            // ── Section 6: Project & Certification Details ──
            Section::make('Project & Completion Letter Details')
                ->icon('heroicon-o-document-check')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('project_name')
                            ->label('Project Title')
                            ->getStateUsing(fn ($record) => $record->team?->team_name ?: ($record->project_name ?: 'Project not assigned'))
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('Project not assigned'),

                        TextEntry::make('grade')
                            ->label('Performance Grade')
                            ->badge()
                            ->color(fn ($state) => filled($state) ? 'success' : 'gray')
                            ->formatStateUsing(fn ($state) => filled($state) ? $state : '—')
                            ->placeholder('—'),

                        TextEntry::make('issuing_date')
                            ->label('Document Issuing Date')
                            ->date('d M Y')
                            ->placeholder('Not issued'),
                    ]),

                    Grid::make(2)->schema([
                        TextEntry::make('cert_ref_id')
                            ->label('Certificate Reference ID')
                            ->badge()
                            ->color('warning')
                            ->copyable()
                            ->getStateUsing(fn ($record) => $record->cert_ref_id ?: $record->completionCertificate?->cert_ref_id)
                            ->placeholder('Not Generated'),

                        TextEntry::make('letter_ref_id')
                            ->label('Letter Reference ID')
                            ->badge()
                            ->color('success')
                            ->copyable()
                            ->getStateUsing(fn ($record) => $record->letter_ref_id ?: $record->completionLetter?->letter_ref_id)
                            ->placeholder('Not Generated'),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('cohort_archive_name')
                            ->label('Archived Cohort Cycle')
                            ->badge()
                            ->color('warning')
                            ->placeholder('Current Active Cohort'),

                        TextEntry::make('archived_at')
                            ->label('Archived On')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('Not archived'),

                        TextEntry::make('archive_note')
                            ->label('Archival Action Note')
                            ->placeholder('No notes recorded'),
                    ]),

                    TextEntry::make('project_description')
                        ->label('Project Summary & Description')
                        ->getStateUsing(fn ($record) => $record->team?->project_description ?: ($record->project_description ?: 'No description provided'))
                        ->html()
                        ->placeholder('No description provided')
                        ->columnSpanFull(),
                ]),

            // ── Section 7: Generated Documents Overview Panel ──
            Section::make('Generated Documents Overview')
                ->icon('heroicon-o-academic-cap')
                ->collapsible()
                ->schema([
                    ViewEntry::make('generated_documents_panel')
                        ->label(false)
                        ->view('filament.intern-management.partials.generated-documents-panel'),
                ]),
        ]);
    }
}
