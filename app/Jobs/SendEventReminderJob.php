<?php

namespace App\Jobs;

use App\Mail\EventReminderMail;
use App\Models\CalendarEvent;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEventReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly CalendarEvent $event,
    ) {}

    public function handle(): void
    {
        $recipients = $this->collectRecipients();

        foreach ($recipients as $user) {
            Mail::to($user->email)->queue(new EventReminderMail($this->event, $user));
        }
    }

    /**
     * Collect all unique User recipients:
     * - Accepted User invitees
     * - Members of accepted Group invitees
     * - The calendar owner
     */
    private function collectRecipients(): \Illuminate\Support\Collection
    {
        $users = collect();

        $invitees = $this->event->invitees()
            ->where('status', 'accepted')
            ->with('inviteable')
            ->get();

        foreach ($invitees as $invitee) {
            if ($invitee->inviteable instanceof User) {
                $users->push($invitee->inviteable);
            } elseif ($invitee->inviteable instanceof Group) {
                $users = $users->merge($invitee->inviteable->members);
            }
        }

        // Always include the calendar owner
        $owner = $this->event->calendar?->owner;
        if ($owner) {
            $users->push($owner);
        }

        return $users->unique('id');
    }
}
