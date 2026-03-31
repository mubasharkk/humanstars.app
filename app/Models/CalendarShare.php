<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarShare extends Model
{
    protected $fillable = ['calendar_id', 'shareable_id', 'shareable_type', 'permission'];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }
}
