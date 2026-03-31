<?php

namespace App\Http\Resources;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventInviteeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $inviteable = $this->inviteable;

        $inviteeData = match (true) {
            $inviteable instanceof User  => ['id' => $inviteable->id, 'name' => $inviteable->name, 'email' => $inviteable->email],
            $inviteable instanceof Group => ['id' => $inviteable->id, 'name' => $inviteable->name],
            default                      => null,
        };

        return [
            'id'             => $this->id,
            'inviteable_type' => class_basename($this->inviteable_type),
            'invitee'        => $inviteeData,
            'status'         => $this->status,
            'created_at'     => $this->created_at,
        ];
    }
}
