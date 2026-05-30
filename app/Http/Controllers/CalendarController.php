<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    /**
     * JSON feed FullCalendar polls.
     * FullCalendar sends ?start=...&end=... ISO dates.
     */
    public function events(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $query = Event::where('user_id', Auth::id());

        if ($start) {
            $query->where(function ($q) use ($start, $end) {
                $q->where('start', '>=', $start);
                if ($end) {
                    $q->where('start', '<', $end);
                }
            });
        }

        return response()->json(
            $query->get()->map(fn($e) => $e->toFullCalendar())
        );
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $data['user_id'] = Auth::id();

        $event = Event::create($data);

        return response()->json($event->toFullCalendar(), 201);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeOwnership($event);
        $data = $this->validatePayload($request, $event);
        $event->update($data);

        return response()->json($event->fresh()->toFullCalendar());
    }

    public function destroy(Event $event)
    {
        $this->authorizeOwnership($event);
        $event->delete();
        return response()->noContent();
    }

    /* ============================================================
     | Helpers
     | ============================================================ */
    protected function authorizeOwnership(Event $event): void
    {
        abort_unless($event->user_id === Auth::id(), 403, 'Not your event.');
    }

    protected function validatePayload(Request $request, ?Event $existing = null): array
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start'       => 'required|date',
            'end'         => 'nullable|date|after_or_equal:start',
            'all_day'     => 'nullable|boolean',
            'category'    => ['required', Rule::in(Event::CATEGORIES)],
            'url'         => 'nullable|url|max:2048',
        ]);
    }
}
