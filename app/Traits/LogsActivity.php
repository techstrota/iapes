<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        foreach (static::getRecordEventsToLog() as $event) {
            static::$event(function ($model) use ($event) {
                $model->logActivity($event);
            });
        }
    }

    protected static function getRecordEventsToLog(): array
    {
        return ['created', 'updated', 'deleted'];
    }

    public function logActivity(string $event)
    {
        $description = $this->getActivityDescription($event);
        $properties = $this->getActivityProperties($event);

        // For deletions, we MUST capture the identifier now because the subject will be null later
        if ($event === 'deleted') {
            $properties['record_name'] = $this->getActivityLogIdentifier();
        }
        
        $userId = Auth::guard('web')->id();
        
        if (!$userId && Auth::guard('intern')->check()) {
            $intern = Auth::guard('intern')->user();
            $properties['performed_by_intern'] = $intern->id;
            $description .= " (by Intern: {$intern->name})";
        }
        
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $event,
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'description' => $description,
            'properties' => $properties,
        ]);
    }

    protected function getActivityDescription(string $event): string
    {
        if ($event === 'updated') {
            $dirty = $this->getDirty();
            unset($dirty['updated_at']);
            
            $changedFields = array_keys($dirty);
            
            if (count($changedFields) > 0) {
                return "Updated: " . implode(', ', $changedFields);
            }
        }

        if ($event === 'created') {
            if (class_basename($this) === 'Application') {
                return "New Application Received";
            }
            return "Created new record";
        }

        if ($event === 'deleted') {
            return "Deleted record";
        }

        return ucfirst($event);
    }

    protected function getActivityLogIdentifier(): ?string
    {
        $nameCols = ['name', 'title', 'username', 'event_title', 'interview_batch_name', 'intern_batch_name'];
        $codeCols = ['application_code', 'interview_batch_code', 'intern_code'];
        
        $name = null;
        foreach ($nameCols as $col) { if (isset($this->{$col})) { $name = (string) $this->{$col}; break; } }
        
        $code = null;
        foreach ($codeCols as $col) { if (isset($this->{$col})) { $code = (string) $this->{$col}; break; } }
        
        if ($name && $code) return "{$name} ({$code})";
        if ($name) return $name;
        if ($code) return $code;
        return "ID: {$this->id}";
    }

    protected function getActivityProperties(string $event): array
    {
        if ($event === 'updated') {
            return [
                'old' => array_intersect_key($this->getOriginal(), $this->getDirty()),
                'attributes' => $this->getDirty(),
            ];
        }

        if ($event === 'created') {
            return [
                'attributes' => $this->toArray(),
            ];
        }

        return [];
    }
}
