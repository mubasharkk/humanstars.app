<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;

class DeleteCalendarAction
{
    public function execute(Calendar $calendar): void
    {
        $calendar->delete();
    }
}
