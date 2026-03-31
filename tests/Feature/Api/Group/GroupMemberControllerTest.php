<?php

namespace Tests\Feature\Api\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private function ownerWithGroup(): array
    {
        $owner = User::factory()->create();
        $token = auth('api')->login($owner);
        $group = Group::factory()->create(['user_id' => $owner->id]);

        return [$owner, $token, $group];
    }

    // -------------------------------------------------------------------------
    // GET /api/groups/{group}/members
    // -------------------------------------------------------------------------

    public function test_index_lists_members(): void
    {
        [$owner, $token, $group] = $this->ownerWithGroup();
        $member = User::factory()->create();
        $group->members()->attach($member->id);

        $this->withToken($token)
            ->getJson("/api/groups/{$group->id}/members")
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_index_forbidden_for_unrelated_user(): void
    {
        [,, $group] = $this->ownerWithGroup();
        $other = User::factory()->create();
        $token = auth('api')->login($other);

        $this->withToken($token)
            ->getJson("/api/groups/{$group->id}/members")
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // POST /api/groups/{group}/members
    // -------------------------------------------------------------------------

    public function test_store_adds_a_member(): void
    {
        [$owner, $token, $group] = $this->ownerWithGroup();
        $member = User::factory()->create();

        $this->withToken($token)
            ->postJson("/api/groups/{$group->id}/members", ['user_id' => $member->id])
            ->assertCreated()
            ->assertJsonFragment(['id' => $member->id, 'email' => $member->email]);

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);
    }

    public function test_store_prevents_duplicate_member(): void
    {
        [$owner, $token, $group] = $this->ownerWithGroup();
        $member = User::factory()->create();
        $group->members()->attach($member->id);

        $this->withToken($token)
            ->postJson("/api/groups/{$group->id}/members", ['user_id' => $member->id])
            ->assertUnprocessable();
    }

    public function test_store_rejects_non_existent_user(): void
    {
        [$owner, $token, $group] = $this->ownerWithGroup();

        $this->withToken($token)
            ->postJson("/api/groups/{$group->id}/members", ['user_id' => 99999])
            ->assertUnprocessable();
    }

    public function test_store_forbidden_for_non_owner(): void
    {
        [,, $group] = $this->ownerWithGroup();
        $other  = User::factory()->create();
        $token  = auth('api')->login($other);
        $member = User::factory()->create();

        $this->withToken($token)
            ->postJson("/api/groups/{$group->id}/members", ['user_id' => $member->id])
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // DELETE /api/groups/{group}/members/{user}
    // -------------------------------------------------------------------------

    public function test_destroy_removes_a_member(): void
    {
        [$owner, $token, $group] = $this->ownerWithGroup();
        $member = User::factory()->create();
        $group->members()->attach($member->id);

        $this->withToken($token)
            ->deleteJson("/api/groups/{$group->id}/members/{$member->id}")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Member removed successfully.']);

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);
    }

    public function test_destroy_rejects_non_member(): void
    {
        [$owner, $token, $group] = $this->ownerWithGroup();
        $nonMember = User::factory()->create();

        $this->withToken($token)
            ->deleteJson("/api/groups/{$group->id}/members/{$nonMember->id}")
            ->assertUnprocessable();
    }

    public function test_destroy_forbidden_for_non_owner(): void
    {
        [,, $group] = $this->ownerWithGroup();
        $other  = User::factory()->create();
        $token  = auth('api')->login($other);
        $member = User::factory()->create();
        $group->members()->attach($member->id);

        $this->withToken($token)
            ->deleteJson("/api/groups/{$group->id}/members/{$member->id}")
            ->assertForbidden();
    }
}
