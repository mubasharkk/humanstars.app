<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    public function viewAny(User $user, Calendar $calendar): bool
    {
        return $this->canAccessCalendar($user, $calendar);
    }

    public function view(User $user, CalendarEvent $event): bool
    {
        return $this->canAccessCalendar($user, $event->calendar);
    }

    public function create(User $user, Calendar $calendar): bool
    {
        return $this->canWriteCalendar($user, $calendar);
    }

    public function update(User $user, CalendarEvent $event): bool
    {
        return $this->canWriteCalendar($user, $event->calendar);
    }

    public function delete(User $user, CalendarEvent $event): bool
    {
        return $this->canWriteCalendar($user, $event->calendar);
    }

    private function canAccessCalendar(User $user, ?Calendar $calendar): bool
    {
        if (! $calendar) {
            return false;
        }

        if ($calendar->user_id === $user->id) {
            return true;
        }

        return $calendar->shares()
            ->where('shareable_type', User::class)
            ->where('shareable_id', $user->id)
            ->exists();
    }

    private function canWriteCalendar(User $user, ?Calendar $calendar): bool
    {
        if (! $calendar) {
            return false;
        }

        if ($calendar->user_id === $user->id) {
            return true;
        }

        return $calendar->shares()
            ->where('shareable_type', User::class)
            ->where('shareable_id', $user->id)
            ->where('permission', 'READWRITE')
            ->exists();
    }
}
