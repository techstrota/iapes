<?php

namespace App\Models\InternManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompletionCertificate extends Model
{
    protected $fillable = [
        'intern_id',
        // Reference & verification
        'cert_ref_id',
        'cert_token',
        // Intern identity snapshot
        'intern_name',
        'intern_code',
        'internship_role',
        // Dates
        'joining_date',
        'completion_date',
        'issuing_date',
        // Evaluation
        'project_name',
        'grade',
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
            if (empty($model->cert_ref_id)) {
                $intern = Intern::find($model->intern_id);
                $code = $intern?->intern_code ?: ('INT-' . str_pad((string) $model->intern_id, 3, '0', STR_PAD_LEFT));
                $model->cert_ref_id = 'CERT-' . $code;
            }

            if (empty($model->cert_token)) {
                $model->cert_token = Str::random(64);
            }

            $model->generated_at ??= now();
        });
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class, 'intern_id');
    }
}
