<?php

namespace App\Models\InternManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\InterviewManagement\Application;

use App\Models\InterviewManagement\OfferLetter;
use App\Models\User;
use App\Models\InternManagement\InternshipBatch;
use App\Models\InternManagement\InternTeam;
use App\Models\TaskManagement\TaskSubmission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

use App\Traits\LogsActivity;

class Intern extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'application_id',
        'internship_batch_id',
        'offer_letter_id',
        'intern_team_id',
        'intern_code',
        'username',
        'password',
        'name',
        'email',
        'completion_letter_template', // Added this line
        'project_name',
        'project_description',
        // 'joining_date',
        'issuing_date',
        'is_active',
        'intern_image',
        'cert_token',
        'grade',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($intern) {
            // Automatically generate a token if one wasn't provided
            if (empty($intern->cert_token)) {
                $intern->cert_token = Str::random(32);
            }
        });

        // Add this to prevent UPDATING the application_id
        static::updating(function ($intern) {
            if ($intern->isDirty('application_id')) {
                // Revert it to the original value from the database
                $intern->application_id = $intern->getOriginal('application_id');
            }
        });

        // For intern status 
        // static::retrieved(function ($intern) {
        //     if ($intern->is_active && $intern->offerletter?->completion_date?->isPast()) {
        //         $intern->is_active = false;
        //         $intern->save();
        //     }
        // });

        static::retrieved(function ($intern) {
            $completionDate = $intern->offerletter?->completion_date;

            if ($completionDate) {
                // Ensure we are working with a Carbon instance
                $date = \Carbon\Carbon::parse($completionDate);

                if ($intern->is_active && $date->isPast()) {
                    // Silently update the database status
                    $intern->is_active = false;
                    $intern->save();
                }
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'intern';
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function offerletter()
    {
        // Make sure the method name 'offerletter' matches what you wrote in the Infolist
        return $this->belongsTo(\App\Models\InterviewManagement\OfferLetter::class, 'offer_letter_id');
    } 
    

    public function user(): BelongsTo
    {
        // Intern 'username' matches User 'email'
        return $this->belongsTo(User::class, 'username', 'email');
    }

    public function batch()
    {
        return $this->belongsTo(InternshipBatch::class, 'internship_batch_id');
    }

    public function team()
    {
        // Note: This assumes you added 'team_id' to your interns table
        return $this->belongsTo(InternTeam::class, 'intern_team_id');
    }

    public function teammates()
    {
        // Gets other interns in the same team, excluding the current intern
        return $this->hasMany(Intern::class, 'intern_team_id', 'intern_team_id')
            ->where('id', '!=', $this->id);
    }

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class, 'intern_id');
    }

    public function offer_letters() // For accessing dates in i-card
    {
        return $this->belongsTo(OfferLetter::class, 'offer_letter_id');
    }
}
