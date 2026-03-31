<?php

namespace App\Actions\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddGroupMemberAction
{
    public function execute(Group $group, int $userId): User
    {
        if ($group->members()->where('user_id', $userId)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => ['This user is already a member of the group.'],
            ]);
        }

        $group->members()->attach($userId);

        return User::find($userId);
    }
}
