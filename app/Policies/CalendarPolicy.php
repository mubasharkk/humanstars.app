<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;

class CalendarPolicy
{
    /** Any authenticated user may list calendars (filtering happens in the query). */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Owner or any user with a share may view a calendar. */
    public function view(User $user, Calendar $calendar): bool
    {
        return $this->isOwner($user, $calendar)
            || $this->hasShare($user, $calendar);
    }

    /** Any authenticated user may create a calendar. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Owner or READWRITE share holders may update a calendar. */
    public function update(User $user, Calendar $calendar): bool
    {
        return $this->isOwner($user, $calendar)
            || $this->hasShare($user, $calendar, 'READWRITE');
    }

    /** Only the owner may delete a calendar. */
    public function delete(User $user, Calendar $calendar): bool
    {
        return $this->isOwner($user, $calendar);
    }

    private function isOwner(User $user, Calendar $calendar): bool
    {
        return $calendar->user_id === $user->id;
    }

    private function hasShare(User $user, Calendar $calendar, ?string $permission = null): bool
    {
        return $calendar->shares()
            ->where('shareable_type', User::class)
            ->where('shareable_id', $user->id)
            ->when($permission, fn ($q) => $q->where('permission', $permission))
            ->exists();
    }
}
