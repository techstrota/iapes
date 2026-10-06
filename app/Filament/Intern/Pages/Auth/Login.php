<?php

namespace App\Filament\Intern\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form; // <-- Make sure this line is here!
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class Login extends BaseLogin
{
    // Override the form to use your custom fields
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->autofocus(),
                
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }
    protected function getRedirectUrl(): string
    {
        return filament()->getPanel('intern')->getUrl();
    }
    // Tell Filament to authenticate using the 'username' database column
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();

            // 1. Attempt login with the intern guard
            if (! auth('intern')->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
                throw ValidationException::withMessages([
                    'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            $intern = auth('intern')->user(); 

            // 2. Check dates and status
            $completionDate = $intern->offerletter?->completion_date 
                ? \Carbon\Carbon::parse($intern->offerletter->completion_date) 
                : null;
                
            $isExpired = $completionDate && $completionDate->isPast();
            $isInactive = !$intern->is_active;

            if ($isExpired || $isInactive) {
                $message = $isExpired 
                    ? 'Your internship period has ended. Access is no longer permitted.' 
                    : 'Your account is currently inactive. Please contact HR.';

                // LOGOUT
                auth('intern')->logout();

                // Clear the specific intern session but keep the CSRF token alive
                session()->flush(); 
                
                // Send notification to the NEXT request
                Notification::make()
                    ->title('Login Restricted')
                    ->body($message)
                    ->danger()
                    ->send();

                // Redirect back to login to refresh the CSRF state cleanly
                return app(LoginResponse::class);
            }

            session()->regenerate();

            return app(LoginResponse::class);

        } catch (ValidationException $e) {
            throw $e;
        }
    }
    
    protected function throwFailureNotification(string $message): void
    {
        Notification::make()
            ->title('Login Restricted')
            ->body($message)
            ->danger()
            ->send();

        throw ValidationException::withMessages([
            'data.username' => $message,
        ]);
    }
    
}