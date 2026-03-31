<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EventInvitee extends Model
{
    protected $fillable = ['calendar_event_id', 'inviteable_id', 'inviteable_type', 'status'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }

    public function inviteable(): MorphTo
    {
        return $this->morphTo();
    }
}
