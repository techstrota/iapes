<?php

namespace App\Models\ReportManagement;

use App\Models\InternManagement\Intern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    use HasFactory;

    protected $table = 'colleges';

    protected $fillable = [
        'name',
        'university_name',
        'address',
    ];

    public function evaluations(): HasMany
    {
        return $this->hasMany(CollegeEvaluation::class, 'college_id')->orderBy('created_at', 'desc');
    }

    public function activeEvaluations(): HasMany
    {
        return $this->hasMany(CollegeEvaluation::class, 'college_id')->where('is_archived', false)->orderBy('created_at', 'desc');
    }

    public function archivedEvaluations(): HasMany
    {
        return $this->hasMany(CollegeEvaluation::class, 'college_id')->where('is_archived', true)->orderBy('created_at', 'desc');
    }

    /**
     * Resolves all interns registered under this college name.
     */
    public function getInternsAttribute()
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return collect();
        }
        return Intern::where(function ($q) use ($name) {
            $q->whereRaw('LOWER(TRIM(college)) = ?', [strtolower($name)])
              ->orWhere('college', 'LIKE', '%' . $name . '%');
        })->get();
    }

    public function getInternsCountAttribute(): int
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return 0;
        }
        return Intern::where(function ($q) use ($name) {
            $q->whereRaw('LOWER(TRIM(college)) = ?', [strtolower($name)])
              ->orWhere('college', 'LIKE', '%' . $name . '%');
        })->count();
    }

    public function getActiveInternsCountAttribute(): int
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return 0;
        }
        return Intern::active()
            ->where(function ($q) use ($name) {
                $q->whereRaw('LOWER(TRIM(college)) = ?', [strtolower($name)])
                  ->orWhere('college', 'LIKE', '%' . $name . '%');
            })->count();
    }
}
