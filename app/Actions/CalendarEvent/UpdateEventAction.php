<?php

namespace App\Actions\CalendarEvent;

use App\Models\CalendarEvent;

class UpdateEventAction
{
    public function execute(CalendarEvent $event, array $data): CalendarEvent
    {
        $event->update($data);

        return $event->fresh();
    }
}
