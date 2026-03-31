<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'calendar_id'      => $this->calendar_id,
            'title'            => $this->title,
            'description'      => $this->description,
            'type'             => $this->type,
            // virtual events
            'meeting_url'      => $this->meeting_url,
            // on-site events (lat/lng intentionally excluded — internal fields)
            'address'          => $this->address,
            'starts_at'        => $this->starts_at,
            'ends_at'          => $this->ends_at,
            'timezone'         => $this->effectiveTimezone(),
            'rrule'            => $this->rrule,
            'reminder_minutes' => $this->reminder_minutes,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
