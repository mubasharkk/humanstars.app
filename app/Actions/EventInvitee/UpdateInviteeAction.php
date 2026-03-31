<?php

namespace App\Actions\EventInvitee;

use App\Models\EventInvitee;

class UpdateInviteeAction
{
    public function execute(EventInvitee $invitee, string $status): EventInvitee
    {
        $invitee->update(['status' => $status]);

        return $invitee->fresh();
    }
}
