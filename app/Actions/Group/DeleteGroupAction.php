<?php

namespace App\Actions\Group;

use App\Models\Group;

class DeleteGroupAction
{
    public function execute(Group $group): void
    {
        $group->delete();
    }
}
