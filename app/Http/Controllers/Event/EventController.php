<?php

namespace App\Http\Controllers\Event;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // List events by city
    public function index()
    {
        $events = Event::with('venue','photos')->get();
        $events = Event::with(['venue','photos'])->get()->map(function ($event) {
            $event->color = $event->venue->color;
            $event->city = $event->venue->city;
            return $event;
        });


        return response()->json([
            'data' => $events,
            'success' => true,
            'status' => 200,
            'message' => 'Events fetched successfully.'
        ]);
    }

    // Create new event
    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'artist'   => 'required|string|max:255',
            'date'     => 'required|date',
            'time'     => 'required',
        ]);

        $venue = Venue::findOrFail($validated['venue_id']);
        $validated['city'] = $venue->city;       // assign venue city
        $validated['color'] = $venue->color;     // assign venue color

        $event = Event::create($validated);

        return response()->json([
            'data' => $event,
            'success' => true,
            'status' => 201,
            'message' => 'Event created successfully.'
        ]);
    }

    // Show single event
    public function show(Event $event)
    {
        return response()->json([
            'data' => $event->load('venue'),
            'success' => true,
            'status' => 200,
            'message' => 'Event fetched successfully.'
        ]);
    }

    // Update event
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'artist' => 'sometimes|required|string|max:255',
            'date'   => 'sometimes|required|date',
            'time'   => 'sometimes|required',
        ]);

        $event->update($validated);

        return response()->json([
            'data' => $event,
            'success' => true,
            'status' => 200,
            'message' => 'Event updated successfully.'
        ]);
    }

    // Delete event
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Event deleted successfully.'
        ]);
    }
}
