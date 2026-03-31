<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminderJob;
use App\Models\CalendarEvent;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature   = 'events:send-reminders';
    protected $description = 'Dispatch reminder emails for events starting within their configured reminder window.';

    public function handle(): void
    {
        $now = now();

        // Find events whose reminder window falls within the current minute.
        // Formula: starts_at - reminder_minutes = now (±30 seconds)
        CalendarEvent::query()
            ->whereNotNull('reminder_minutes')
            ->whereHas('invitees')
            ->whereRaw(
                'DATE_SUB(starts_at, INTERVAL reminder_minutes MINUTE) BETWEEN ? AND ?',
                [
                    $now->copy()->startOfMinute()->toDateTimeString(),
                    $now->copy()->endOfMinute()->toDateTimeString(),
                ]
            )
            ->each(fn (CalendarEvent $event) => SendEventReminderJob::dispatch($event));

        $this->info('Event reminders dispatched.');
    }
}
