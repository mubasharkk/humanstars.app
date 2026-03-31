<?php

namespace App\Actions\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveGroupMemberAction
{
    public function execute(Group $group, User $user): void
    {
        if (! $group->members()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => ['This user is not a member of the group.'],
            ]);
        }

        $group->members()->detach($user->id);
    }
}
