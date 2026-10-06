<?php

namespace App\Models\InternManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompletionLetter extends Model
{
    protected $fillable = [
        'intern_id',
        // Reference
        'letter_ref_id',
        'template',
        // Intern identity snapshot
        'intern_name',
        'intern_code',
        'internship_role',
        'degree',
        'college',
        'university',
        // Dates
        'joining_date',
        'completion_date',
        'issuing_date',
        // Evaluation & content
        'project_name',
        'grade',
        'project_description',
        'working_hours',
        // Audit
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'joining_date'    => 'date',
        'completion_date' => 'date',
        'issuing_date'    => 'date',
        'generated_at'    => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->letter_ref_id)) {
                $intern = Intern::find($model->intern_id);
                $code = $intern?->intern_code ?: ('INT-' . str_pad((string) $model->intern_id, 3, '0', STR_PAD_LEFT));
                $model->letter_ref_id = 'LET-' . $code;
            }

            $model->generated_at ??= now();
        });
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }
}
