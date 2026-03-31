<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CalendarEvent extends Model
{
    use HasUuids, SoftDeletes;

    public function uniqueIds(): array
    {
        return ['id'];
    }

    protected $fillable = [
        'calendar_id',
        'title',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'timezone',
        'rrule',
        'reminder_minutes',
    ];

    protected $casts = [
        'starts_at' => 'datetime', // always UTC in DB
        'ends_at'   => 'datetime', // always UTC in DB
    ];

    // Resolves effective timezone: event-level override → calendar timezone → UTC
    public function effectiveTimezone(): string
    {
        return $this->timezone
            ?? $this->calendar?->timezone
            ?? 'UTC';
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function invitees(): HasMany
    {
        return $this->hasMany(EventInvitee::class);
    }
}
