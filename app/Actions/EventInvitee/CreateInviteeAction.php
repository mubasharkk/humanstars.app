<?php

namespace App\Actions\EventInvitee;

use App\Models\CalendarEvent;
use App\Models\EventInvitee;
use App\Models\Group;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateInviteeAction
{
    public function execute(CalendarEvent $event, string $type, int $id): EventInvitee
    {
        [$modelClass, $label] = match ($type) {
            'user'  => [User::class, 'user'],
            'group' => [Group::class, 'group'],
        };

        $inviteable = $modelClass::find($id);

        if (! $inviteable) {
            throw ValidationException::withMessages([
                'inviteable_id' => ["The selected {$label} does not exist."],
            ]);
        }

        $alreadyInvited = $event->invitees()
            ->where('inviteable_type', $modelClass)
            ->where('inviteable_id', $id)
            ->exists();

        if ($alreadyInvited) {
            throw ValidationException::withMessages([
                'inviteable_id' => ["This {$label} has already been invited."],
            ]);
        }

        return $event->invitees()->create([
            'inviteable_type' => $modelClass,
            'inviteable_id'   => $id,
            'status'          => 'pending',
        ]);
    }
}
