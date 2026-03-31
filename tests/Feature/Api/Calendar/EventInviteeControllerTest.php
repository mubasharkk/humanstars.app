<?php

namespace Tests\Feature\Api\Calendar;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\EventInvitee;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventInviteeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function scaffold(): array
    {
        $owner    = User::factory()->create();
        $token    = auth('api')->login($owner);
        $calendar = Calendar::factory()->create(['user_id' => $owner->id]);
        $event    = CalendarEvent::factory()->create(['calendar_id' => $calendar->id]);

        return [$owner, $token, $calendar, $event];
    }

    private function url(Calendar $calendar, CalendarEvent $event, ?EventInvitee $invitee = null): string
    {
        $base = "/api/calendars/{$calendar->id}/events/{$event->id}/invitees";

        return $invitee ? "{$base}/{$invitee->id}" : $base;
    }

    // -------------------------------------------------------------------------
    // GET /api/calendars/{calendar}/events/{event}/invitees
    // -------------------------------------------------------------------------

    public function test_index_lists_invitees(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();

        EventInvitee::create([
            'calendar_event_id' => $event->id,
            'inviteable_type'   => User::class,
            'inviteable_id'     => $invitee->id,
            'status'            => 'pending',
        ]);

        $this->withToken($token)
            ->getJson($this->url($calendar, $event))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_index_requires_authentication(): void
    {
        // Create resources without logging in so the guard has no cached user.
        $calendar = Calendar::factory()->create();
        $event    = CalendarEvent::factory()->create(['calendar_id' => $calendar->id]);

        $this->getJson($this->url($calendar, $event))->assertUnauthorized();
    }

    public function test_index_forbidden_for_unrelated_user(): void
    {
        [,, $calendar, $event] = $this->scaffold();
        $other = User::factory()->create();
        $token = auth('api')->login($other);

        $this->withToken($token)
            ->getJson($this->url($calendar, $event))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // POST /api/calendars/{calendar}/events/{event}/invitees
    // -------------------------------------------------------------------------

    public function test_store_invites_a_user(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();

        $this->withToken($token)
            ->postJson($this->url($calendar, $event), [
                'inviteable_type' => 'user',
                'inviteable_id'   => $invitee->id,
            ])
            ->assertCreated()
            ->assertJsonFragment(['status' => 'pending'])
            ->assertJsonPath('invitee.email', $invitee->email);
    }

    public function test_store_invites_a_group(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();
        $group = Group::factory()->create();

        $this->withToken($token)
            ->postJson($this->url($calendar, $event), [
                'inviteable_type' => 'group',
                'inviteable_id'   => $group->id,
            ])
            ->assertCreated()
            ->assertJsonPath('invitee.name', $group->name);
    }

    public function test_store_prevents_duplicate_invite(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();

        $data = ['inviteable_type' => 'user', 'inviteable_id' => $invitee->id];

        $this->withToken($token)->postJson($this->url($calendar, $event), $data)->assertCreated();
        $this->withToken($token)->postJson($this->url($calendar, $event), $data)->assertUnprocessable();
    }

    public function test_store_fails_for_non_existent_user(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();

        $this->withToken($token)
            ->postJson($this->url($calendar, $event), [
                'inviteable_type' => 'user',
                'inviteable_id'   => 99999,
            ])
            ->assertUnprocessable();
    }

    public function test_store_forbidden_for_non_owner(): void
    {
        [,, $calendar, $event] = $this->scaffold();
        $other   = User::factory()->create();
        $token   = auth('api')->login($other);
        $invitee = User::factory()->create();

        $this->withToken($token)
            ->postJson($this->url($calendar, $event), [
                'inviteable_type' => 'user',
                'inviteable_id'   => $invitee->id,
            ])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // PATCH /api/calendars/{calendar}/events/{event}/invitees/{invitee}
    // -------------------------------------------------------------------------

    public function test_update_owner_can_change_status(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();

        $record = EventInvitee::create([
            'calendar_event_id' => $event->id,
            'inviteable_type'   => User::class,
            'inviteable_id'     => $invitee->id,
            'status'            => 'pending',
        ]);

        $this->withToken($token)
            ->patchJson($this->url($calendar, $event, $record), ['status' => 'accepted'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'accepted']);
    }

    public function test_update_invitee_can_rsvp_their_own_record(): void
    {
        [,, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();
        $token   = auth('api')->login($invitee);

        $record = EventInvitee::create([
            'calendar_event_id' => $event->id,
            'inviteable_type'   => User::class,
            'inviteable_id'     => $invitee->id,
            'status'            => 'pending',
        ]);

        $this->withToken($token)
            ->patchJson($this->url($calendar, $event, $record), ['status' => 'declined'])
            ->assertOk()
            ->assertJsonFragment(['status' => 'declined']);
    }

    public function test_update_forbidden_for_unrelated_user(): void
    {
        [,, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();
        $other   = User::factory()->create();
        $token   = auth('api')->login($other);

        $record = EventInvitee::create([
            'calendar_event_id' => $event->id,
            'inviteable_type'   => User::class,
            'inviteable_id'     => $invitee->id,
            'status'            => 'pending',
        ]);

        $this->withToken($token)
            ->patchJson($this->url($calendar, $event, $record), ['status' => 'accepted'])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/calendars/{calendar}/events/{event}/invitees/{invitee}
    // -------------------------------------------------------------------------

    public function test_destroy_removes_invitee(): void
    {
        [$owner, $token, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();

        $record = EventInvitee::create([
            'calendar_event_id' => $event->id,
            'inviteable_type'   => User::class,
            'inviteable_id'     => $invitee->id,
            'status'            => 'pending',
        ]);

        $this->withToken($token)
            ->deleteJson($this->url($calendar, $event, $record))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Invitee removed successfully.']);

        $this->assertDatabaseMissing('event_invitees', ['id' => $record->id]);
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        [,, $calendar, $event] = $this->scaffold();
        $invitee = User::factory()->create();
        $other   = User::factory()->create();
        $token   = auth('api')->login($other);

        $record = EventInvitee::create([
            'calendar_event_id' => $event->id,
            'inviteable_type'   => User::class,
            'inviteable_id'     => $invitee->id,
            'status'            => 'pending',
        ]);

        $this->withToken($token)
            ->deleteJson($this->url($calendar, $event, $record))
            ->assertForbidden();
    }
}
