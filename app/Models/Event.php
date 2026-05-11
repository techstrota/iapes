<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    //
    protected $fillable = [
        'event_title',
        'event_description',
        'event_type',
        'event_start_date',
        'event_end_date',
        'type',
        'meeting_link',
        'meeting_platform',
        'event_venue',
        'event_status',
        'skills',
      //  'created_by'
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }
}
