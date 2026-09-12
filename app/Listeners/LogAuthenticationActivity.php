<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Request;

class LogAuthenticationActivity
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $action = '';
        $description = '';
        $user = null;

        if ($event instanceof Login) {
            $action = 'login';
            $user = $event->user;
            $description = "User logged in";
        } elseif ($event instanceof Logout) {
            $action = 'logout';
            $user = $event->user;
            $description = "User logged out";
        } elseif ($event instanceof Failed) {
            $action = 'login_failed';
            $description = "Failed login attempt for email: " . ($event->credentials['email'] ?? 'unknown');
        }

        if ($action) {
            // activity_logs.user_id is a FK → users.id.
            // Interns authenticate via the 'intern' guard and live in the
            // `interns` table, NOT `users`. Writing their ID into user_id
            // violates the FK constraint. Set user_id = null for interns and
            // store their identity in properties and subject_type/subject_id.
            $isIntern = $user instanceof \App\Models\InternManagement\Intern;

            ActivityLog::create([
                'user_id'      => $isIntern ? null : $user?->id,
                'action'       => $action,
                'subject_type' => $user ? get_class($user) : null,
                'subject_id'   => $user?->id,
                'description'  => $description,
                'properties'   => [
                    'ip'          => Request::ip(),
                    'user_agent'  => Request::userAgent(),
                    'intern_id'   => $isIntern ? $user->id   : null,
                    'intern_name' => $isIntern ? $user->name : null,
                ],
            ]);
        }
    }
}
