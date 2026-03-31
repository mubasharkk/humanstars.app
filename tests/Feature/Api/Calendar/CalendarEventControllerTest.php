<?php

namespace Tests\Feature\Api\Calendar;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingWithCalendar(?User $user = null): array
    {
        $user     = $user ?? User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create(['user_id' => $user->id]);

        return [$user, $token, $calendar];
    }

    // -------------------------------------------------------------------------
    // GET /api/calendars/{calendar}/events
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication(): void
    {
        $calendar = Calendar::factory()->create();

        $this->getJson("/api/calendars/{$calendar->id}/events")->assertUnauthorized();
    }

    public function test_index_lists_events_for_calendar(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        CalendarEvent::factory()->count(3)->create(['calendar_id' => $calendar->id]);

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}/events")
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_index_filters_events_by_from_date(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        CalendarEvent::factory()->create([
            'calendar_id' => $calendar->id,
            'starts_at'   => '2025-01-01 10:00:00',
            'ends_at'     => '2025-01-01 11:00:00',
        ]);
        CalendarEvent::factory()->create([
            'calendar_id' => $calendar->id,
            'starts_at'   => '2025-06-01 10:00:00',
            'ends_at'     => '2025-06-01 11:00:00',
        ]);

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}/events?from=2025-04-01")
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_index_filters_events_by_to_date(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        CalendarEvent::factory()->create([
            'calendar_id' => $calendar->id,
            'starts_at'   => '2025-01-01 10:00:00',
            'ends_at'     => '2025-01-01 11:00:00',
        ]);
        CalendarEvent::factory()->create([
            'calendar_id' => $calendar->id,
            'starts_at'   => '2025-06-01 10:00:00',
            'ends_at'     => '2025-06-01 11:00:00',
        ]);

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}/events?to=2025-04-01")
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_index_forbidden_for_unrelated_calendar(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $calendar = Calendar::factory()->create();

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}/events")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // POST /api/calendars/{calendar}/events
    // -------------------------------------------------------------------------

    public function test_store_creates_virtual_event(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        $response = $this->withToken($token)->postJson("/api/calendars/{$calendar->id}/events", [
            'title'       => 'Team Standup',
            'type'        => 'virtual',
            'meeting_url' => 'https://meet.example.com/standup',
            'starts_at'   => '2025-05-10 09:00:00',
            'ends_at'     => '2025-05-10 09:30:00',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'Team Standup', 'type' => 'virtual'])
            ->assertJsonMissingPath('latitude')
            ->assertJsonMissingPath('longitude');
    }

    public function test_store_creates_on_site_event(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        $response = $this->withToken($token)->postJson("/api/calendars/{$calendar->id}/events", [
            'title'     => 'Office Meeting',
            'type'      => 'on-site',
            'address'   => '123 Main St, London',
            'starts_at' => '2025-05-10 09:00:00',
            'ends_at'   => '2025-05-10 10:00:00',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['type' => 'on-site', 'address' => '123 Main St, London']);
    }

    public function test_store_validates_required_fields(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        $this->withToken($token)
            ->postJson("/api/calendars/{$calendar->id}/events", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'type', 'starts_at', 'ends_at']);
    }

    public function test_store_validates_ends_at_after_starts_at(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();

        $this->withToken($token)
            ->postJson("/api/calendars/{$calendar->id}/events", [
                'title'     => 'Bad Event',
                'type'      => 'on-site',
                'starts_at' => '2025-05-10 10:00:00',
                'ends_at'   => '2025-05-10 09:00:00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ends_at']);
    }

    // -------------------------------------------------------------------------
    // GET /api/calendars/{calendar}/events/{event}
    // -------------------------------------------------------------------------

    public function test_show_returns_event(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();
        $event = CalendarEvent::factory()->create(['calendar_id' => $calendar->id]);

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}/events/{$event->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $event->id]);
    }

    public function test_show_does_not_expose_lat_lng(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();
        $event = CalendarEvent::factory()->onSite()->create(['calendar_id' => $calendar->id]);

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}/events/{$event->id}")
            ->assertOk()
            ->assertJsonMissingPath('latitude')
            ->assertJsonMissingPath('longitude');
    }

    // -------------------------------------------------------------------------
    // PUT /api/calendars/{calendar}/events/{event}
    // -------------------------------------------------------------------------

    public function test_update_modifies_event(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();
        $event = CalendarEvent::factory()->create(['calendar_id' => $calendar->id]);

        $this->withToken($token)
            ->putJson("/api/calendars/{$calendar->id}/events/{$event->id}", ['title' => 'Renamed'])
            ->assertOk()
            ->assertJsonFragment(['title' => 'Renamed']);
    }

    public function test_update_forbidden_for_non_owner(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $calendar = Calendar::factory()->create();
        $event    = CalendarEvent::factory()->create(['calendar_id' => $calendar->id]);

        $this->withToken($token)
            ->putJson("/api/calendars/{$calendar->id}/events/{$event->id}", ['title' => 'Hacked'])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/calendars/{calendar}/events/{event}
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_event(): void
    {
        [, $token, $calendar] = $this->actingWithCalendar();
        $event = CalendarEvent::factory()->create(['calendar_id' => $calendar->id]);

        $this->withToken($token)
            ->deleteJson("/api/calendars/{$calendar->id}/events/{$event->id}")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Event deleted successfully.']);

        $this->assertSoftDeleted('calendar_events', ['id' => $event->id]);
    }
}
