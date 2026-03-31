<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\EventInvitee;
use App\Models\User;

class EventInviteePolicy
{
    /** Calendar owner or READWRITE share holder may view the invitee list. */
    public function viewAny(User $user, CalendarEvent $event): bool
    {
        return $this->canWrite($user, $event) || $this->isInvited($user, $event);
    }

    /** Calendar owner or READWRITE share holder may add invitees. */
    public function create(User $user, CalendarEvent $event): bool
    {
        return $this->canWrite($user, $event);
    }

    /**
     * The invitee themselves may update their own RSVP status.
     * The calendar owner / READWRITE holder may also update any invitee.
     */
    public function update(User $user, EventInvitee $invitee): bool
    {
        if ($invitee->inviteable_type === User::class && $invitee->inviteable_id === $user->id) {
            return true;
        }

        return $this->canWrite($user, $invitee->event);
    }

    /** Calendar owner or READWRITE share holder may remove invitees. */
    public function delete(User $user, EventInvitee $invitee): bool
    {
        return $this->canWrite($user, $invitee->event);
    }

    private function canWrite(User $user, ?CalendarEvent $event): bool
    {
        if (! $event?->calendar) {
            return false;
        }

        $calendar = $event->calendar;

        if ($calendar->user_id === $user->id) {
            return true;
        }

        return $calendar->shares()
            ->where('shareable_type', User::class)
            ->where('shareable_id', $user->id)
            ->where('permission', 'READWRITE')
            ->exists();
    }

    private function isInvited(User $user, CalendarEvent $event): bool
    {
        return $event->invitees()
            ->where('inviteable_type', User::class)
            ->where('inviteable_id', $user->id)
            ->exists();
    }
}
