<?php

namespace App\Models\InterviewManagement;

use Illuminate\Database\Eloquent\Model;

class OfferLetterTemplate extends Model
{
    protected $fillable = [
        'name',
        'template',
        'internship_role',
        'internship_position',
        'joining_date',
        'completion_date',
        'duration',
        'working_hours',
        'offer_issue_date',
        'description',
    ];

    protected $casts = [
        'joining_date'    => 'date',
        'completion_date' => 'date',
        'offer_issue_date' => 'date',
    ];

    public const LAYOUT_OPTIONS = [
        'single_page' => 'Single Page Offer Letter',
        'multi_page'  => 'Multi Page Offer Letter',
    ];

    protected static function booted(): void
    {
        static::updated(function (OfferLetterTemplate $preset) {
            $preset->syncDraftOfferLetters();
        });
    }

    public function offerLetters()
    {
        return $this->hasMany(OfferLetter::class, 'offer_letter_template_id');
    }

    public function getDurationLabelAttribute(): string
    {
        $d = trim((string) ($this->duration ?: ''));
        if (empty($d)) {
            return '3 Months';
        }

        return OfferLetter::DURATION_SUGGESTIONS[strtolower($d)] ?? ucwords($d);
    }

    public function getLayoutLabelAttribute(): string
    {
        $tpl = $this->template ?: 'single_page';

        return match ($tpl) {
            'single_page', 'one_month', '3_month_offer_letter', 'general' => 'Single Page Offer Letter',
            'multi_page', '4_month_offer_letter', '6_month_offer_letter'  => 'Multi Page Offer Letter',
            default => self::LAYOUT_OPTIONS[$tpl] ?? ucfirst(str_replace('_', ' ', $tpl)),
        };
    }

    public function getSummaryLabelAttribute(): string
    {
        $layout = $this->layout_label;
        $role = $this->internship_role ?: 'No Role';
        $duration = $this->duration_label;
        $dates = $this->joining_date
            ? $this->joining_date->format('d M Y') . ($this->completion_date ? ' → ' . $this->completion_date->format('d M Y') : '')
            : 'Dates not set';

        return "{$this->name} — {$role} ({$layout} • {$duration} | {$dates})";
    }

    public function syncDraftOfferLetters(): int
    {
        $isMultiPage = in_array($this->template, ['multi_page', '4_month_offer_letter', '6_month_offer_letter']);

        $description = !empty(trim(strip_tags($this->description ?? '')))
            ? $this->description
            : ($isMultiPage
                ? OfferLetter::defaultDescription(
                    $this->joining_date?->format('Y-m-d'),
                    $this->completion_date?->format('Y-m-d'),
                    $this->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday'
                )
                : null);

        return $this->offerLetters()
            ->where('offer_status', 'draft')
            ->update([
                'template'            => $this->template ?: 'single_page',
                'duration'            => $this->duration ?: '3 months',
                'internship_role'     => $this->internship_role,
                'internship_position' => $this->internship_position,
                'joining_date'        => $this->joining_date?->format('Y-m-d'),
                'completion_date'     => $this->completion_date?->format('Y-m-d'),
                'working_hours'       => $this->working_hours ?: '11:00 AM to 4:00 PM, Monday to Saturday',
                'offer_issue_date'    => $this->offer_issue_date?->format('Y-m-d'),
                'description'         => $isMultiPage ? $description : null,
            ]);
    }
}
