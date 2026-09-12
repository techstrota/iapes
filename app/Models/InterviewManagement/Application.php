<?php

namespace App\Models\InterviewManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\InterviewManagement\OfferLetter;
use Carbon\Carbon;

use App\Traits\LogsActivity;

class Application extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'application_code',
        'verification_token',
        'email',
        'email_verified_at',
        'name',
        'phone',
        'college',
        'degree',
        'year',
        'cgpa',
        'domain',
        'duration',
        'duration_unit',
        'skills',
        'resume_path',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($application) {
            if (empty($application->application_code)) {
                $datePart = now()->format('dmy');
                
                // 1. Fetch the latest record that is NOT pending and HAS a code
                $lastApplication = static::where('status', '!=', 'pending')
                    // ->whereNotNull('application_code')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastApplication && !empty($lastApplication->application_code)) {
                    // 2. Extract the numeric suffix from the end of the string
                    // We use explode or regex to ensure we get the number after the last slash
                    $segments = explode('/', $lastApplication->application_code);
                    $lastSequence = (int) end($segments); 
                    
                    $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $newSequence = '001';
                }

                // 3. Combine current date with the incrementing number
                $application->application_code = "APP/" . $datePart . "/" . $newSequence;
            }

            if (empty($application->verification_token)) {
                $application->verification_token = Str::random(60);
            }
        });

        static::updated(function ($application) {
            // Auto-create draft OfferLetter when candidate status is set to 'shortlisted'
            if ($application->isDirty('status') && $application->status === 'shortlisted') {
                $alreadyExists = OfferLetter::where('application_id', $application->id)->exists();
                if (!$alreadyExists) {
                    $template = OfferLetter::templateForDuration(
                        (int) $application->duration,
                        $application->duration_unit
                    );

                    $joiningDate = now()->addDays(7)->toDateString();
                    $completionDate = self::computeCompletionDate($joiningDate, $application->duration, $application->duration_unit);
                    $workingHours = '42 hours per week';

                    $description = in_array($template, ['4_month_offer_letter', '6_month_offer_letter'])
                        ? OfferLetter::defaultDescription($joiningDate, $completionDate, $workingHours)
                        : null;

                    OfferLetter::create([
                        'application_id'      => $application->id,
                        'offer_status'        => 'draft',
                        'is_accepted'         => false,
                        'template'            => $template,
                        'name'                => $application->name,
                        'college'             => $application->college,
                        'university'          => $application->college,
                        'degree'              => $application->degree,
                        'email'               => $application->email,
                        'phone'               => $application->phone,
                        'internship_role'     => $application->domain ?? 'Intern',
                        'internship_position' => ($application->domain ?? 'Intern') . ' Intern',
                        'working_hours'       => $workingHours,
                        'joining_date'        => $joiningDate,
                        'completion_date'     => $completionDate,
                        'description'         => $description,
                        'offer_issue_date'    => now()->toDateString(),
                    ]);
                }
            }

            // If name or college changes, find the associated offer letter
            $offerLetter = $application->offerLetter;
            if ($offerLetter) {
                $offerLetter->touch();
            }
        });
    }

    public static function computeCompletionDate(string $joiningDate, ?int $duration, ?string $unit): string
    {
        if (!$duration || !$unit) {
            return \Carbon\Carbon::parse($joiningDate)->addMonths(3)->toDateString();
        }
        $date = \Carbon\Carbon::parse($joiningDate);
        $unit = strtolower($unit);
        if (str_contains($unit, 'month')) {
            $date->addMonths($duration);
        } elseif (str_contains($unit, 'week')) {
            $date->addWeeks($duration);
        } else {
            $date->addDays($duration);
        }
        return $date->toDateString();
    }

    public function intern(): HasOne
    {
        // Assuming 'application_id' is the foreign key on the 'interns' table
        return $this->hasOne(\App\Models\InternManagement\Intern::class, 'application_id');
    }
    public function offer_letters(): HasMany
    {
        // Adjust the foreign key if it's not 'application_id'
        return $this->hasMany(OfferLetter::class, 'application_id');
    }
    public function offerLetter(): HasOne // Note the singular name
    {
        return $this->hasOne(OfferLetter::class, 'application_id');
    }

    public function interviewAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\InterviewManagement\InterviewAssignment::class, 'application_id');
    }

}
