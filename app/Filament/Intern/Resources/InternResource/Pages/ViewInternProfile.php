<?php

namespace App\Filament\Intern\Resources\InternResource\Pages;

use App\Filament\Intern\Resources\InternResource;
use App\Models\InternManagement\Intern;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;

class ViewInternProfile extends ViewRecord
{
    protected static string $resource = InternResource::class;

    protected static string $view = 'filament.intern.profile.view-intern-profile';

    public function getTitle(): string
    {
        return ($this->record->name ?? 'My Profile') . ' — Workspace Profile';
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Upload / Change Profile Picture ──
            Actions\Action::make('uploadPhoto')
                ->label('Update Photo')
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
                        ->getUploadedFileNameForStorageUsing(
                            fn ($file, $record): string => (string) str($record->intern_code ?: 'intern-' . $record->id)
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
                        ->title('Profile picture updated successfully! 📷')
                        ->success()
                        ->send();
                })
                ->modalHeading('Upload Profile Picture')
                ->modalSubmitActionLabel('Save Picture'),

            // ── Change Password ──
            Actions\Action::make('changePassword')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->same('password_confirmation'),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->dehydrated(false),
                ])
                ->action(function (array $data, Intern $record) {
                    $record->update([
                        'password' => Hash::make($data['password']),
                        'plain_password' => $data['password'],
                    ]);

                    Notification::make()
                        ->title('Password Updated Successfully 🔐')
                        ->body('Your intern portal login password has been changed.')
                        ->success()
                        ->send();
                })
                ->modalWidth('md')
                ->modalHeading('Change Your Account Password')
                ->modalSubmitActionLabel('Update Password'),

            // ── Edit Personal / Contact Info ──
            Actions\Action::make('updateContact')
                ->label('Edit Profile Info')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->fillForm(fn (Intern $record): array => [
                    'phone'      => $record->phone,
                    'college'    => $record->college,
                    'university' => $record->university,
                    'degree'     => $record->degree,
                    'skills'     => $record->skills,
                ])
                ->form([
                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('college')
                        ->label('College / Institution')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('university')
                        ->label('Affiliated University')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('degree')
                        ->label('Degree / Program')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('skills')
                        ->label('Skills & Technologies (comma-separated)')
                        ->placeholder('e.g. PHP, Laravel, React, Python, MySQL')
                        ->maxLength(255),
                ])
                ->action(function (array $data, Intern $record) {
                    $record->update($data);

                    Notification::make()
                        ->title('Profile details updated successfully! ✨')
                        ->success()
                        ->send();
                })
                ->modalHeading('Edit Personal & Academic Details')
                ->modalSubmitActionLabel('Save Changes'),
        ];
    }
}

