<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'color', 'description', 'timezone'];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(CalendarShare::class);
    }
}
