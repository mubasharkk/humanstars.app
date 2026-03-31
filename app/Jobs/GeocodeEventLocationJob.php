<?php

namespace App\Jobs;

use App\Models\CalendarEvent;
use App\Services\GoogleMapsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeocodeEventLocationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly CalendarEvent $event,
    ) {}

    public function handle(GoogleMapsService $maps): void
    {
        if (! $this->event->address) {
            return;
        }

        $result = $maps->geocode($this->event->address);

        if ($result['lat'] !== null && $result['lng'] !== null) {
            // updateQuietly prevents re-triggering the observer on this write.
            $this->event->updateQuietly([
                'latitude'  => $result['lat'],
                'longitude' => $result['lng'],
            ]);
        }
    }
}
