<?php

namespace Tests\Feature\Api\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/groups
    // -------------------------------------------------------------------------

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/groups')->assertUnauthorized();
    }

    public function test_index_returns_owned_and_member_groups(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $owned  = Group::factory()->create(['user_id' => $user->id]);
        $other  = User::factory()->create();
        $member = Group::factory()->create(['user_id' => $other->id]);
        $member->members()->attach($user->id);

        $response = $this->withToken($token)->getJson('/api/groups');

        $response->assertOk()
            ->assertJsonPath('owned.0.id', $owned->id)
            ->assertJsonPath('member.0.id', $member->id);
    }

    public function test_index_excludes_unrelated_groups(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        Group::factory()->create(); // unrelated

        $response = $this->withToken($token)->getJson('/api/groups');

        $response->assertOk()
            ->assertJsonCount(0, 'owned')
            ->assertJsonCount(0, 'member');
    }

    // -------------------------------------------------------------------------
    // POST /api/groups
    // -------------------------------------------------------------------------

    public function test_store_creates_a_group(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withToken($token)->postJson('/api/groups', [
            'name'        => 'Engineering',
            'description' => 'The eng team.',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Engineering']);

        $this->assertDatabaseHas('groups', ['user_id' => $user->id, 'name' => 'Engineering']);
    }

    public function test_store_validates_required_fields(): void
    {
        $token = auth('api')->login(User::factory()->create());

        $this->withToken($token)
            ->postJson('/api/groups', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // -------------------------------------------------------------------------
    // GET /api/groups/{group}
    // -------------------------------------------------------------------------

    public function test_show_returns_group_to_owner(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->getJson("/api/groups/{$group->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $group->id]);
    }

    public function test_show_returns_group_to_member(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create();
        $group->members()->attach($user->id);

        $this->withToken($token)
            ->getJson("/api/groups/{$group->id}")
            ->assertOk();
    }

    public function test_show_forbidden_for_unrelated_user(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create();

        $this->withToken($token)
            ->getJson("/api/groups/{$group->id}")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // PUT /api/groups/{group}
    // -------------------------------------------------------------------------

    public function test_update_modifies_group(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->putJson("/api/groups/{$group->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated']);
    }

    public function test_update_forbidden_for_non_owner(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create();
        $group->members()->attach($user->id);

        $this->withToken($token)
            ->putJson("/api/groups/{$group->id}", ['name' => 'Hacked'])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/groups/{group}
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_group(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create(['user_id' => $user->id]);

        $this->withToken($token)
            ->deleteJson("/api/groups/{$group->id}")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Group deleted successfully.']);

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);
        $group = Group::factory()->create();

        $this->withToken($token)
            ->deleteJson("/api/groups/{$group->id}")
            ->assertForbidden();
    }
}
