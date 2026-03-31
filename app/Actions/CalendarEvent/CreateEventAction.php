<?php

namespace App\Actions\CalendarEvent;

use App\Models\Calendar;
use App\Models\CalendarEvent;

class CreateEventAction
{
    public function execute(Calendar $calendar, array $data): CalendarEvent
    {
        return $calendar->events()->create($data);
    }
}
