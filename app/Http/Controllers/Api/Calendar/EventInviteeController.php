<?php

namespace App\Http\Controllers\Api\Calendar;

use App\Actions\EventInvitee\CreateInviteeAction;
use App\Actions\EventInvitee\DeleteInviteeAction;
use App\Actions\EventInvitee\UpdateInviteeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventInvitee\StoreInviteeRequest;
use App\Http\Requests\EventInvitee\UpdateInviteeRequest;
use App\Http\Resources\EventInviteeResource;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\EventInvitee;
use Illuminate\Http\JsonResponse;

class EventInviteeController extends Controller
{
    public function index(Calendar $calendar, CalendarEvent $event): JsonResponse
    {
        $this->authorize('viewAny', [EventInvitee::class, $event]);

        $invitees = $event->invitees()->with('inviteable')->get();

        return response()->json(EventInviteeResource::collection($invitees));
    }

    public function store(StoreInviteeRequest $request, Calendar $calendar, CalendarEvent $event, CreateInviteeAction $action): JsonResponse
    {
        $this->authorize('create', [EventInvitee::class, $event]);

        $invitee = $action->execute(
            $event,
            $request->validated('inviteable_type'),
            $request->validated('inviteable_id'),
        );

        return response()->json(new EventInviteeResource($invitee->load('inviteable')), 201);
    }

    public function update(UpdateInviteeRequest $request, Calendar $calendar, CalendarEvent $event, EventInvitee $invitee, UpdateInviteeAction $action): JsonResponse
    {
        $this->authorize('update', $invitee);

        $invitee = $action->execute($invitee, $request->validated('status'));

        return response()->json(new EventInviteeResource($invitee->load('inviteable')));
    }

    public function destroy(Calendar $calendar, CalendarEvent $event, EventInvitee $invitee, DeleteInviteeAction $action): JsonResponse
    {
        $this->authorize('delete', $invitee);

        $action->execute($invitee);

        return response()->json(['message' => 'Invitee removed successfully.']);
    }
}
