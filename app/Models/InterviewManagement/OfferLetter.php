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
        'internship_role',
        'working_hours',
       // 'intern_id',
        'template',
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

    /**
     * Auto-select template based on application duration.
     */
    public static function templateForDuration(?int $duration, ?string $unit): string
    {
        if (!$duration || !$unit) return 'general';
        $unit = strtolower($unit);
        $months = str_contains($unit, 'month') ? $duration
                : (str_contains($unit, 'week') ? round($duration / 4.3) : round($duration / 30));

        return match(true) {
            $months <= 1  => 'one_month',
            $months <= 3  => '3_month_offer_letter',
            $months <= 4  => '4_month_offer_letter',
            $months >= 6  => '6_month_offer_letter',
            default       => 'general',
        };
    }

    /**
     * Generate default formatted description for offer letter second page.
     */
    public static function defaultDescription(
        ?string $joiningDate = null,
        ?string $completionDate = null,
        ?string $workingHours = '42 hours per week'
    ): string {
        $commence = $joiningDate 
            ? \Illuminate\Support\Carbon::parse($joiningDate)->format('jS F, Y') 
            : '18th December, 2025';

        $conclude = $completionDate 
            ? \Illuminate\Support\Carbon::parse($completionDate)->format('jS F, Y') 
            : '30th April, 2026';

        $hours = !empty($workingHours) ? $workingHours : '42 hours per week';

        return "<p>The internship will commence on <strong>{$commence}</strong> and will conclude on <strong>{$conclude}</strong>. You will be expected to work <strong>{$hours}</strong>, from <strong>Monday to Saturday</strong>, between <strong>10:30 AM to 5:30 PM</strong>.</p>\n<p>Upon successful completion of the internship, you will receive a <strong>Certificate of Completion</strong> and a <strong>Letter of Recommendation</strong>. You will also be eligible for certain benefits, including access to the company’s facilities, events, and training programs.</p>";
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
        });
    }
}
