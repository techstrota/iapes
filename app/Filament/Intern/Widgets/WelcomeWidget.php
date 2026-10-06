<?php

namespace App\Filament\Intern\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.intern.widgets.welcome-widget';
    protected static ?int $sort = 0;

    // Full width banner at top
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        /** @var \App\Models\InternManagement\Intern|null $user */
        $user = Auth::user();
        $hour = now()->hour;

        $greeting = match (true) {
            $hour >= 17 => 'Good evening',
            $hour >= 12 => 'Good afternoon',
            default     => 'Good morning',
        };

        $role = $user?->internship_role ?: ($user?->offerletter?->internship_role ?? 'Engineering Intern');
        $batch = $user?->batch?->batch_name ?? 'Active Cohort';
        $code = $user?->intern_code;

        return [
            'greeting' => $greeting,
            'name'     => $user?->name ?? 'Intern',
            'role'     => $role,
            'batch'    => $batch,
            'code'     => $code,
            'date'     => now()->format('l, F j, Y'),
            'internId' => $user?->id,
        ];
    }
}