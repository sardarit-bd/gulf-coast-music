<?php

namespace App\Http\Controllers\Venue;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    // List all venues (optional city filter)
    public function index()
    {
       $cities = City::all()->pluck('name')->toArray();
        $venuesByCity = [];

        try{
                    foreach($cities as $city){
            $venuesByCity[$city] = Venue::where('city', $city)
                ->with(['photos', 'events'])
                ->get();
        }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Error fetching venues: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'data' => $venuesByCity,
            'success' => true,
            'status' => 200,
            'message' => 'Venues fetched city-wise successfully.'
        ]);
    }


    // Show single venue
    public function show(Venue $venue)
    {
        return response()->json([
            'data' => $venue->load('photos'),
            'success' => true,
            'status' => 200,
            'message' => 'Venue fetched successfully.'
        ]);
    }

    // Create a venue profile
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'name'       => 'required|string|max:255',
            'city'       => 'required|string|exists:cities,name',
            'address'    => 'nullable|string|max:255',
            'state'      => 'nullable|string|max:255',
            'zip'        => 'nullable|string|max:20',
            'phone'      => 'nullable|string|max:20',
            'capacity'   => 'nullable|integer',
            'biography'  => 'nullable|string',
            'open_hours' => 'nullable|string',
            'open_days'  => 'nullable|string',
        ]);


        // Color assignment based on city verification order
        $colorMap = ['Blue','Green','Red','Purple','Orange','Yellow','Pink','Brown','White','Black'];

        // Count all existing venues (ignore city)
        $venueCount = Venue::count(); // আগের সব venues এর সংখ্যা


        // Assign color based on current position
        $validated['color'] = $colorMap[$venueCount % count($colorMap)];

        try {
        // Create venue
        $venue = Venue::create($validated);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Error creating venue: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'data' => $venue,
            'success' => true,
            'status' => 201,
            'message' => 'Venue created successfully.'
        ]);
    }

    // Update venue profile
    public function update(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'city'       => 'sometimes|required|string|in:Mobile,New Orleans,Biloxi,Pensacola',
            'address'    => 'nullable|string|max:255',
            'state'      => 'nullable|string|max:255',
            'zip'        => 'nullable|string|max:20',
            'phone'      => 'nullable|string|max:20',
            'capacity'   => 'nullable|integer',
            'biography'  => 'nullable|string',
            'open_hours' => 'nullable|string',
            'open_days'  => 'nullable|string',
        ]);

        // Recalculate color if city changed
        if(isset($validated['city']) && $validated['city'] != $venue->city){
            $colorMap = ['Blue','Green','Red','Purple','Orange','Yellow','Pink','Brown','White','Black'];
            $venueCount = Venue::where('city', $validated['city'])->count();
            $validated['color'] = $colorMap[$venueCount % 10];
        }

        $venue->update($validated);

        return response()->json([
            'data' => $venue,
            'success' => true,
            'status' => 200,
            'message' => 'Venue updated successfully.'
        ]);
    }

    // Delete venue
    public function destroy(Venue $venue)
    {
        $venue->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Venue deleted successfully.'
        ]);
    }
}
