<?php

namespace App\Actions\CalendarEvent;

use App\Models\CalendarEvent;

class DeleteEventAction
{
    public function execute(CalendarEvent $event): void
    {
        $event->delete();
    }
}
