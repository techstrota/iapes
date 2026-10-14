<?php

namespace App\Models\InterviewManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\InternManagement\Intern;

class OfferLetter extends Model
{
    protected $fillable = [
        'offer_letter_code',
        'application_id',
        'joining_date',
        'completion_date',
        'duration',
        'internship_role',
        'working_hours',
       // 'intern_id',
        'template',
        'offer_letter_template_id',
        'description',
        'college',
        'degree',   // ← add this too
        'name',
        'email',
        'phone',
        'university',
        'internship_position',
        'offer_issue_date',
        'offer_status',
        'is_accepted',
    ];
    protected $casts = [
        'joining_date' => 'date',
        'completion_date' => 'date', // or 'datetime'
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($offer) {

            $year = now()->year;

            $lastOffer = self::latest('id')->first();

            if ($lastOffer) {
                $lastNumber = (int) substr($lastOffer->offer_letter_code, -3);
                $sequence = $lastNumber + 1;
            } else {
                $sequence = 1;
            }

            $sequence = str_pad($sequence, 3, '0', STR_PAD_LEFT);

            $offer->offer_letter_code = "OFFER/{$year}/{$sequence}";
        });
    }

    public function application()
    {
        // Ensure this matches your foreign key in the offer_letters table
        return $this->belongsTo(Application::class, 'application_id');
    }

    // Accessor for convenience
    // public function getNameAttribute()
    // {
    //     return $this->application?->name;
    // }

    // public function getCollegeAttribute()
    // {
    //     return $this->application?->college;
    // }

    public function intern()
    {
        return $this->hasOne(Intern::class, 'offer_letter_id');
    }

    public function templatePreset()
    {
        return $this->belongsTo(OfferLetterTemplate::class, 'offer_letter_template_id');
    }

    public function isDraft(): bool
    {
        return $this->offer_status === 'draft';
    }

    public function isAccepted(): bool
    {
        return $this->offer_status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->offer_status === 'rejected';
    }

    public const DURATION_SUGGESTIONS = [
        '15 days'  => '15 Days',
        '21 days'  => '21 Days',
        '26 days'  => '26 Days',
        '30 days'  => '30 Days',
        '1 month'  => '1 Month',
        '2 months' => '2 Months',
        '3 months' => '3 Months',
        '4 months' => '4 Months',
        '6 months' => '6 Months',
        '1 year'   => '1 Year',
    ];

    /**
     * Calculate completion date dynamically from joining date and duration.
     * Supports standard options and custom expressions (e.g. "45 days", "8 weeks", "2 months", "1 year").
     */
    public static function calculateCompletionDate(?string $joiningDate, ?string $duration): ?string
    {
        if (!$joiningDate || !$duration) {
            return null;
        }

        try {
            $start = \Illuminate\Support\Carbon::parse($joiningDate);
            $d = trim(strtolower($duration));

            if (preg_match('/^(\d+)\s*(day|week|month|year)s?$/i', $d, $matches)) {
                $count = (int) $matches[1];
                $unit  = strtolower($matches[2]);

                return match ($unit) {
                    'day'   => $start->copy()->addDays($count)->format('Y-m-d'),
                    'week'  => $start->copy()->addWeeks($count)->format('Y-m-d'),
                    'month' => $start->copy()->addMonths($count)->format('Y-m-d'),
                    'year'  => $start->copy()->addYears($count)->format('Y-m-d'),
                    default => null,
                };
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function getDurationLabelAttribute(): string
    {
        $d = trim((string) ($this->duration ?: ''));
        if (empty($d)) {
            return '3 Months';
        }

        return self::DURATION_SUGGESTIONS[strtolower($d)] ?? ucwords($d);
    }

    public function getLayoutLabelAttribute(): string
    {
        $tpl = $this->template ?: 'single_page';

        return match ($tpl) {
            'single_page', 'one_month', '3_month_offer_letter', 'general' => 'Single Page Offer Letter',
            'multi_page', '4_month_offer_letter', '6_month_offer_letter'  => 'Multi Page Offer Letter',
            default => ucfirst(str_replace('_', ' ', $tpl)),
        };
    }

    /**
     * Auto-select template based on application duration.
     */
    public static function templateForDuration(?int $duration, ?string $unit): string
    {
        return 'single_page';
    }

    /**
     * Generate default formatted description for offer letter second page.
     */
    public static function defaultDescription(
        ?string $joiningDate = null,
        ?string $completionDate = null,
        ?string $workingHours = '11:00 AM to 4:00 PM, Monday to Saturday'
    ): string {
        $commence = $joiningDate 
            ? \Illuminate\Support\Carbon::parse($joiningDate)->format('jS F, Y') 
            : '18th December, 2025';

        $conclude = $completionDate 
            ? \Illuminate\Support\Carbon::parse($completionDate)->format('jS F, Y') 
            : '30th April, 2026';

        $hours = !empty($workingHours) ? $workingHours : '11:00 AM to 4:00 PM, Monday to Saturday';

        return "<p>The internship will commence on <strong>{$commence}</strong> and will conclude on <strong>{$conclude}</strong>. You will be expected to work <strong>{$hours}</strong>.</p>\n<p>Upon successful completion of the internship, you will receive a <strong>Certificate of Completion</strong> and a <strong>Letter of Recommendation</strong>. You will also be eligible for certain benefits, including access to the company’s facilities, events, and training programs.</p><p>To accept this offer, please sign and return this letter by <strong>{$commence}</strong>. If you have any questions or concerns, please feel free to contact us at any time. </p>";
    }

    protected static function booted()
    {
        parent::booted();

        static::updated(function ($offerLetter) {
            // If the OfferLetter was updated with new name/college data, 
            // we sync it back to the related application.
            if ($offerLetter->application) {
                $offerLetter->application->update([
                    'name' => $offerLetter->name,
                    'college' => $offerLetter->college,
                ]);
            }

            // Keep the associated intern record in sync with offer letter changes
            $intern = $offerLetter->intern ?? Intern::where('offer_letter_id', $offerLetter->id)->first();
            if ($intern) {
                $intern->update(array_filter([
                    'name'                => $offerLetter->name,
                    'email'               => $offerLetter->email,
                    'phone'               => $offerLetter->phone,
                    'college'             => $offerLetter->college,
                    'degree'              => $offerLetter->degree,
                    'university'          => $offerLetter->university ?: $offerLetter->college,
                    'internship_role'     => $offerLetter->internship_role,
                    'internship_position' => $offerLetter->internship_position,
                    'joining_date'        => $offerLetter->joining_date,
                    'completion_date'     => $offerLetter->completion_date,
                    'working_hours'       => $offerLetter->working_hours,
                ], fn ($val) => !is_null($val)));
            }
        });
    }
}
