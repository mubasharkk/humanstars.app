<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;

class UpdateCalendarAction
{
    public function execute(Calendar $calendar, array $data): Calendar
    {
        $calendar->update($data);

        return $calendar->fresh();
    }
}
