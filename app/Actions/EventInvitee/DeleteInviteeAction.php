<?php

namespace App\Actions\EventInvitee;

use App\Models\EventInvitee;

class DeleteInviteeAction
{
    public function execute(EventInvitee $invitee): void
    {
        $invitee->delete();
    }
}
