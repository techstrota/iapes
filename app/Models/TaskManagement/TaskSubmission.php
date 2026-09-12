<?php

namespace App\Models\TaskManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\TaskManagement\Task;
use App\Models\InternManagement\Intern;

use App\Traits\LogsActivity;

class TaskSubmission extends Model
{
    use LogsActivity;

     protected $primaryKey = 'submission_id';

    protected $fillable = [
        'task_id',
        'intern_id',
        'status',
        'submission_text',
        'submission_file',
        'submitted_at',
        'admin_feedback',
        'marks',
        'grade',
        'evaluated_at',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class,'task_id');
    }


    public function taskAssignment()
    {
        return $this->belongsTo(
            \App\Models\TaskManagement\TaskAssignment::class,
            'task_id',
            'task_id'
        )->where('intern_id', auth()->id());
    }


    public function intern()
    {
        return $this->belongsTo(Intern::class,'intern_id');

    }
}
