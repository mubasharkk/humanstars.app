<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\CalendarShare;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarViewController extends Controller
{
    /**
     * Render the calendar page with owned and shared calendars as Inertia props.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $owned = Calendar::where('user_id', $user->id)
            ->select(['id', 'name', 'color', 'timezone'])
            ->get();

        $shared = Calendar::whereHas('shares', function ($query) use ($user) {
                $query->where('shareable_type', User::class)
                    ->where('shareable_id', $user->id);
            })
            ->select(['id', 'name', 'color', 'timezone'])
            ->get();

        return Inertia::render('Calendar/Index', [
            'owned'  => $owned,
            'shared' => $shared,
        ]);
    }

    /**
     * Return events for a calendar as JSON, filtered by optional from/to dates.
     * Used by the frontend calendar view via Axios (session-authenticated).
     */
    public function events(Request $request, Calendar $calendar): JsonResponse
    {
        $this->authorize('view', $calendar);

        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $events = $calendar->events()
            ->when($request->input('from'), fn ($q, $from) => $q->where('starts_at', '>=', $from))
            ->when($request->input('to'), fn ($q, $to) => $q->where('ends_at', '<=', $to))
            ->orderBy('starts_at')
            ->get(['id', 'calendar_id', 'title', 'type', 'address', 'meeting_url', 'starts_at', 'ends_at']);

        return response()->json($events);
    }
}
