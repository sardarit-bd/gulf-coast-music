<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistPhotoController extends Controller
{
    public function index()
    {
        try {
            $artist = Auth::user()->artist;

            if (!$artist) {
                return response()->json([
                    'error'   => 'Artist profile not found.',
                    'success' => false,
                    'status'  => 404,
                    'message' => 'Please create an artist profile first.'
                ], 404);
            }

            $photos = $artist->photos;

            return response()->json([
                'data' => [
                    'photos' => $photos
                ],
                'success' => true,
                'status' => 200,
                'message' => 'Photos fetched successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred while fetching photos.',
                'success' => false,
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048'
        ]);

        $path = $request->file('image')->store('artist/photos', 'public');

        $photo = Auth::user()->artist->photos()->create([
            'path' => $path
        ]);

        return response()->json([
            'data' => [
                'photo' => $photo
            ],
            'success' => true,
            'status' => 201,
            'message' => 'Photo uploaded successfully.',
        ], 201);
    }

    public function destroy(ArtistPhoto $photo)
    {
        if ($photo->artist_id !== Auth::user()->artist->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $photo->delete();

        return response()->json(['message' => 'Photo deleted successfully.'], 200);
    }
}
