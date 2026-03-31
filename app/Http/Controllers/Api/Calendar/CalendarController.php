<?php

namespace App\Http\Controllers\Api\Calendar;

use App\Actions\Calendar\CreateCalendarAction;
use App\Actions\Calendar\DeleteCalendarAction;
use App\Actions\Calendar\UpdateCalendarAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\StoreCalendarRequest;
use App\Http\Requests\Calendar\UpdateCalendarRequest;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * List all calendars for the authenticated user.
     * Response is separated into owned and shared calendars.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $owned = Calendar::where('user_id', $user->id)->get();

        $shared = Calendar::whereHas('shares', function ($query) use ($user) {
            $query->where('shareable_type', User::class)
                ->where('shareable_id', $user->id);
        })->get();

        return response()->json([
            'owned'  => CalendarResource::collection($owned),
            'shared' => CalendarResource::collection($shared),
        ]);
    }

    public function store(StoreCalendarRequest $request, CreateCalendarAction $action): JsonResponse
    {
        $this->authorize('create', Calendar::class);

        $calendar = $action->execute($request->user(), $request->validated());

        return response()->json(new CalendarResource($calendar), 201);
    }

    public function show(Calendar $calendar): JsonResponse
    {
        $this->authorize('view', $calendar);

        return response()->json(new CalendarResource($calendar));
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar, UpdateCalendarAction $action): JsonResponse
    {
        $this->authorize('update', $calendar);

        $calendar = $action->execute($calendar, $request->validated());

        return response()->json(new CalendarResource($calendar));
    }

    public function destroy(Calendar $calendar, DeleteCalendarAction $action): JsonResponse
    {
        $this->authorize('delete', $calendar);

        $action->execute($calendar);

        return response()->json(['message' => 'Calendar deleted successfully.']);
    }
}
