<?php

namespace App\Actions\Group;

use App\Models\Group;

class UpdateGroupAction
{
    public function execute(Group $group, array $data): Group
    {
        $group->update($data);

        return $group->fresh();
    }
}
