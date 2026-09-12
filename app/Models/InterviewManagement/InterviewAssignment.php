<?php

namespace App\Models\InterviewManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use App\Models\InterviewManagement\Application;
use App\Models\InterviewManagement\InterviewBatch;

class InterviewAssignment extends Model
{
     protected $fillable = [
        'assignment_code',
        'interview_batch_id',
        'application_id',
        'attendance',
        'problem_solving',
        'communication',
        'overall_score',
        'remarks',
        'result'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            $batch = InterviewBatch::find($model->interview_batch_id);

            if ($batch && $batch->capacity_status === 'full') {
                throw new \Exception("Batch is already FULL.");
            }

            $date = now()->format('dmy');

            $lastNumber = (int) substr(
                optional(InterviewAssignment::latest('id')->first())->assignment_code,
                -3
            );

            $model->assignment_code = "ASSIGN/{$date}/" . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        });

        static::saving(function ($assignment) {

            $hasProblemSolving = !is_null($assignment->problem_solving) && $assignment->problem_solving !== '';
            $hasCommunication = !is_null($assignment->communication) && $assignment->communication !== '';

            if ($hasProblemSolving || $hasCommunication) {
                $assignment->overall_score =
                    ((float) ($assignment->problem_solving ?? 0)) + ((float) ($assignment->communication ?? 0));
            } else {
                $assignment->overall_score = null;
            }
                 
            if ($assignment->overall_score > 50) {
                throw new \Exception("Total marks cannot exceed 50");
            }
            
            // Only advance status to 'interviewed' when BOTH marks are provided AND candidate is present
            if ($assignment->attendance === 'present' && $hasProblemSolving && $hasCommunication) {
                if (!in_array($assignment->application->status, ['shortlisted', 'rejected'])) {
                    $assignment->application->update([
                        'status' => 'interviewed'
                    ]);
                }
            }

            if ($assignment->result === 'selected') {
                $assignment->application->update([
                    'status' => 'shortlisted'
                ]);
            }

            if ($assignment->result === 'rejected') {
                $assignment->application->update([
                    'status' => 'rejected'
                ]);
            }
        });

        static::updated(function ($assignment) {

            if ($assignment->isDirty('result') && $assignment->result) {

                // Only update the application status here.
                // Email dispatch is handled by CandidateResource actions to prevent
                // double-sending (which causes Gmail SMTP throttling/silent drops).
                if ($assignment->result === 'selected') {
                    $assignment->application
                        ->update(['status' => 'shortlisted']);
                } elseif ($assignment->result === 'rejected') {
                    $assignment->application
                        ->update(['status' => 'rejected']);
                }
            }
        });

        static::created(function ($assignment) {

            // Update Application Status
            $assignment->application
                ->update(['status' => 'interview_scheduled']);

            // Update Batch Capacity
            $assignment->batch->updateCapacityStatus();
        });

        static::deleted(function ($assignment) {

            if ($assignment->batch) {
                $assignment->batch->updateCapacityStatus();
            }

        });
    }
    
    public function batch()
    {
        return $this->belongsTo(
            InterviewBatch::class,
            'interview_batch_id',
            'id'
        );
    }

    public function application()
    {
        return $this->belongsTo(
            Application::class,
            'application_id',
            'id'
        );
    }

}
