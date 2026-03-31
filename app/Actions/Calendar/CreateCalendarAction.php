<?php

namespace App\Actions\Calendar;

use App\Models\Calendar;
use App\Models\User;

class CreateCalendarAction
{
    public function execute(User $user, array $data): Calendar
    {
        return $user->calendars()->create($data);
    }
}
