<?php

namespace App\Observers;

use App\Jobs\GeocodeEventLocationJob;
use App\Models\CalendarEvent;

class CalendarEventObserver
{
    /**
     * Dispatch geocoding after the event is persisted so the model always has an ID.
     * Only triggers when the event is on-site and the address has actually changed.
     */
    public function saved(CalendarEvent $event): void
    {
        if (
            $event->type === 'on-site'
            && $event->address
            && $event->wasChanged('address')
        ) {
            GeocodeEventLocationJob::dispatch($event);
        }
    }
}
