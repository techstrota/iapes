<?php

namespace App\Filament\Resources\InternManagement\InternResource\Pages;

use App\Filament\Resources\InternManagement\InternResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditIntern extends EditRecord
{
    protected static string $resource = InternResource::class;

    protected static string $view = 'filament.intern-management.intern-resource.pages.edit-intern';

    public function getTitle(): string
    {
        $name = $this->record->name ?: ($this->record->offerletter?->name ?? 'Intern');
        return "Edit Intern: {$name} ({$this->record->intern_code})";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_profile')
                ->label('View Profile')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) => InternResource::getUrl('view', ['record' => $record])),

            Actions\Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->modalHeading('Change Intern Password')
                ->modalDescription(fn ($record) => "Set a new login password for {$record->name} ({$record->intern_code}). Both the hashed password and the plain-text reference will be updated immediately.")
                ->modalSubmitActionLabel('Update Password')
                ->modalWidth('md')
                ->form([
                    \Filament\Forms\Components\Placeholder::make('login_id_info')
                        ->label('Intern Login User ID')
                        ->content(fn ($record) => $record->username ?: $record->email),
                    \Filament\Forms\Components\TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(6)
                        ->helperText('Minimum 6 characters. Click the refresh icon to auto-generate a secure random password.')
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('generateRandom')
                                ->icon('heroicon-m-arrow-path')
                                ->tooltip('Generate Random Secure Password')
                                ->action(function (\Filament\Forms\Set $set) {
                                    $generated = 'ts' . now()->format('y') . strtolower(\Illuminate\Support\Str::random(4)) . rand(10, 99);
                                    $set('new_password', $generated);
                                })
                        ),
                ])
                ->action(function (array $data, $record) {
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

            Actions\Action::make('view_id_card')
                ->label('Print I-Card')
                ->icon('heroicon-o-identification')
                ->color('gray')
                ->url(fn ($record) => route('print-id-card', ['id' => $record->id]))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $intern = $this->record->fresh(['application', 'offerletter']);

        // 1. Sync data to Application table if exists
        if ($intern->application) {
            $intern->application->update([
                'name'    => $intern->name,
                'college' => $intern->college,
                'degree'  => $intern->degree,
                'phone'   => $intern->phone,
                'year'    => $intern->academic_year,
                'cgpa'    => $intern->cgpa,
                'domain'  => $intern->domain,
                'skills'  => $intern->skills,
            ]);
        }

        // 2. Sync data to Offer Letter table if exists
        if ($intern->offerletter) {
            $intern->offerletter->update([
                'name'                => $intern->name,
                'completion_date'     => $intern->completion_date,
                'joining_date'        => $intern->joining_date,
                'internship_role'     => $intern->internship_role,
                'internship_position' => $intern->internship_position,
                'working_hours'       => $intern->working_hours,
                'university'          => $intern->university,
                'college'             => $intern->college,
                'degree'              => $intern->degree,
                'phone'               => $intern->phone,
            ]);
        }

        Notification::make()
            ->title('Intern details saved and synchronized successfully')
            ->success()
            ->send();
    }
}
