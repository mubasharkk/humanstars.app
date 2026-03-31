<?php

namespace Tests\Feature\Api\Calendar;

use App\Models\Calendar;
use App\Models\CalendarShare;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/calendars
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/calendars')->assertUnauthorized();
    }

    public function test_index_returns_owned_and_shared_calendars(): void
    {
        $user   = User::factory()->create();
        $token  = auth('api')->login($user);
        $other  = User::factory()->create();

        $owned  = Calendar::factory()->create(['user_id' => $user->id]);
        $shared = Calendar::factory()->create(['user_id' => $other->id]);

        CalendarShare::create([
            'calendar_id'     => $shared->id,
            'shareable_type'  => User::class,
            'shareable_id'    => $user->id,
            'permission'      => 'READ',
        ]);

        $response = $this->withToken($token)->getJson('/api/calendars');

        $response->assertOk()
            ->assertJsonPath('owned.0.id', $owned->id)
            ->assertJsonPath('shared.0.id', $shared->id);
    }

    public function test_index_does_not_return_other_users_calendars(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        Calendar::factory()->create(); // belongs to another user

        $response = $this->withToken($token)->getJson('/api/calendars');

        $response->assertOk()
            ->assertJsonCount(0, 'owned')
            ->assertJsonCount(0, 'shared');
    }

    // -------------------------------------------------------------------------
    // POST /api/calendars
    // -------------------------------------------------------------------------

    public function test_store_creates_a_calendar(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withToken($token)->postJson('/api/calendars', [
            'name'     => 'Work',
            'color'    => '#FF5733',
            'timezone' => 'Europe/London',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Work', 'color' => '#FF5733']);

        $this->assertDatabaseHas('calendars', ['user_id' => $user->id, 'name' => 'Work']);
    }

    public function test_store_validates_required_fields(): void
    {
        $token = auth('api')->login(User::factory()->create());

        $this->withToken($token)
            ->postJson('/api/calendars', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_color_format(): void
    {
        $token = auth('api')->login(User::factory()->create());

        $this->withToken($token)
            ->postJson('/api/calendars', ['name' => 'X', 'color' => 'red'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['color']);
    }

    // -------------------------------------------------------------------------
    // GET /api/calendars/{calendar}
    // -------------------------------------------------------------------------

    public function test_show_returns_calendar(): void
    {
        $user     = User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $calendar->id]);
    }

    public function test_show_denies_access_to_unrelated_calendar(): void
    {
        $user     = User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create(); // another user's

        $this->withToken($token)
            ->getJson("/api/calendars/{$calendar->id}")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // PUT /api/calendars/{calendar}
    // -------------------------------------------------------------------------

    public function test_update_modifies_calendar(): void
    {
        $user     = User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->putJson("/api/calendars/{$calendar->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated']);
    }

    public function test_update_forbidden_for_non_owner(): void
    {
        $user     = User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create();

        $this->withToken($token)
            ->putJson("/api/calendars/{$calendar->id}", ['name' => 'Hacked'])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/calendars/{calendar}
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_calendar(): void
    {
        $user     = User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->deleteJson("/api/calendars/{$calendar->id}")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Calendar deleted successfully.']);

        $this->assertSoftDeleted('calendars', ['id' => $calendar->id]);
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        $user     = User::factory()->create();
        $token    = auth('api')->login($user);
        $calendar = Calendar::factory()->create();

        $this->withToken($token)
            ->deleteJson("/api/calendars/{$calendar->id}")
            ->assertForbidden();
    }
}
