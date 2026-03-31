<?php

namespace App\Http\Controllers\Api\Calendar;

use App\Actions\CalendarEvent\CreateEventAction;
use App\Actions\CalendarEvent\DeleteEventAction;
use App\Actions\CalendarEvent\UpdateEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarEvent\ListEventRequest;
use App\Http\Requests\CalendarEvent\StoreEventRequest;
use App\Http\Requests\CalendarEvent\UpdateEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\Calendar;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;

class CalendarEventController extends Controller
{
    /**
     * List events for a calendar, optionally filtered by date range.
     * Query params: from (date), to (date).
     */
    public function index(ListEventRequest $request, Calendar $calendar): JsonResponse
    {
        $this->authorize('viewAny', [CalendarEvent::class, $calendar]);

        $events = $calendar->events()
            ->when($request->validated('from'), fn ($q, $from) => $q->where('starts_at', '>=', $from))
            ->when($request->validated('to'), fn ($q, $to) => $q->where('ends_at', '<=', $to))
            ->orderBy('starts_at')
            ->get();

        return response()->json(CalendarEventResource::collection($events));
    }

    public function store(StoreEventRequest $request, Calendar $calendar, CreateEventAction $action): JsonResponse
    {
        $this->authorize('create', [CalendarEvent::class, $calendar]);

        $event = $action->execute($calendar, $request->validated());

        return response()->json(new CalendarEventResource($event), 201);
    }

    public function show(Calendar $calendar, CalendarEvent $event): JsonResponse
    {
        $this->authorize('view', $event);

        return response()->json(new CalendarEventResource($event));
    }

    public function update(UpdateEventRequest $request, Calendar $calendar, CalendarEvent $event, UpdateEventAction $action): JsonResponse
    {
        $this->authorize('update', $event);

        $event = $action->execute($event, $request->validated());

        return response()->json(new CalendarEventResource($event));
    }

    public function destroy(Calendar $calendar, CalendarEvent $event, DeleteEventAction $action): JsonResponse
    {
        $this->authorize('delete', $event);

        $action->execute($event);

        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
