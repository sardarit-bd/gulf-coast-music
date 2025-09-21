<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\VenuePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VenuePhotoController extends Controller
{
    // List all photos for a venue
    public function index(Venue $venue)
    {
        return response()->json([
            'data' => $venue->photos,
            'success' => true,
            'status' => 200,
            'message' => 'Photos fetched successfully.'
        ]);
    }

    // Upload photos (max 5 per venue)
    public function store(Request $request, Venue $venue)
    {
        $request->validate([
            'photos'   => 'required|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'alt'      => 'nullable|string|max:255',
        ]);

        $existingCount = $venue->photos()->count();
        $newCount = count($request->file('photos', []));

        if ($existingCount + $newCount > 5) {
            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => 'A venue can have maximum 5 photos.'
            ]);
        }

        $savedPhotos = [];
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('venues/photos', 'public');
            $savedPhotos[] = $venue->photos()->create([
                'path' => $path,
                'alt'  => $request->input('alt', null),
            ]);
        }

        return response()->json([
            'data' => $savedPhotos,
            'success' => true,
            'status' => 201,
            'message' => 'Photos uploaded successfully.'
        ]);
    }

    // Delete a photo
    public function destroy(VenuePhoto $photo)
    {
        if ($photo->path && Storage::disk('public')->exists($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Photo deleted successfully.'
        ]);
    }
}
