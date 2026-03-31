<?php

namespace App\Services;

class GoogleMapsService
{
    /**
     * Geocode a text address into latitude/longitude coordinates.
     *
     * TODO: Integrate with the Google Maps Geocoding API.
     *   - Add GOOGLE_MAPS_API_KEY to .env
     *   - Endpoint: GET https://maps.googleapis.com/maps/api/geocode/json
     *              ?address={encoded_address}&key={GOOGLE_MAPS_API_KEY}
     *   - Parse response: results[0].geometry.location.{lat,lng}
     *   - Handle ZERO_RESULTS, REQUEST_DENIED, OVER_DAILY_LIMIT statuses
     *
     * @return array{lat: float|null, lng: float|null}
     */
    public function geocode(string $address): array
    {
        // Placeholder — replace with real HTTP call once API key is configured.
        return ['lat' => null, 'lng' => null];
    }
}
