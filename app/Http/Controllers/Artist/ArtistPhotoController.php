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
    try {
        $artist = Auth::user()->artist;

        if (!$artist) {
            return response()->json([
                'success' => false,
                'status'  => 404,
                'message' => 'Artist not found.'
            ], 404);
        }

        // ✅ Check max 5 photos
        if ($artist->photos()->count() >= 5) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => 'You can upload a maximum of 5 photos only.'
            ], 400);
        }

        // ✅ Validate base64 input
        $request->validate([
            'image' => 'required|string' // expecting base64 string
        ]);

        $imageData = $request->input('image');

        // ✅ Extract base64 content (remove "data:image/*;base64," if present)
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $extension = strtolower($type[1]); // jpg, png, gif
        } else {
            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => 'Invalid image format.'
            ], 422);
        }

        $imageData = str_replace(' ', '+', $imageData);
        $image = base64_decode($imageData);

        if ($image === false) {
            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => 'Base64 decoding failed.'
            ], 422);
        }

        // ✅ Generate unique file name
        $fileName = uniqid().'.'.$extension;
        $filePath = 'artist/photos/'.$fileName;

        // ✅ Save to storage/app/public/artist/photos
        \Storage::disk('public')->put($filePath, $image);

        // ✅ Save to DB
        $photo = $artist->photos()->create([
            'path' => $filePath
        ]);

        return response()->json([
            'data' => [
                'photo' => $photo
            ],
            'success' => true,
            'status' => 201,
            'message' => 'Photo uploaded successfully.',
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'status'  => 422,
            'message' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Photo upload error: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'status'  => 500,
            'message' => 'Failed to upload photo.',
            'error'   => $e->getMessage()
        ], 500);
    }
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
