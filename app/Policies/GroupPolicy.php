<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Owner or any member may view a group. */
    public function view(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group)
            || $group->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    /** Only the owner may update or delete the group or manage its members. */
    public function update(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group);
    }

    public function delete(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group);
    }

    public function manageMembers(User $user, Group $group): bool
    {
        return $this->isOwner($user, $group);
    }

    private function isOwner(User $user, Group $group): bool
    {
        return $group->user_id === $user->id;
    }
}
