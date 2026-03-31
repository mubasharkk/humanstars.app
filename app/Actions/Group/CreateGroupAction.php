<?php

namespace App\Actions\Group;

use App\Models\Group;
use App\Models\User;

class CreateGroupAction
{
    public function execute(User $user, array $data): Group
    {
        return $user->ownedGroups()->create($data);
    }
}
