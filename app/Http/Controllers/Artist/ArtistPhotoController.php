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

        // ✅ Validate base64 image
        $request->validate([
            'image' => 'required|string'
        ]);

        // ✅ Convert base64 to file and store
        $imageData = $request->input('image');
        $path = $this->storeBase64Image($imageData, 'artist/photos');

        // ✅ Save DB record
        $photo = $artist->photos()->create([
            'artist_id' => $artist->id,
            'photo_url' => $path
        ]);

        // ✅ Return full URL in response
        $photoUrl = asset('storage/' . $photo->path);

        return response()->json([
            'data' => [
                'photo' => [
                    'id'   => $photo->id,
                    'url'  => $photoUrl,
                    'path' => $photo->path
                ]
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

/**
 * Convert base64 image to file and save in storage, return path
 */
protected function storeBase64Image(string $base64Image, string $folder)
{
    // Remove base64 prefix if present
    $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
    $base64Image = str_replace(' ', '+', $base64Image);

    $imageData = base64_decode($base64Image);
    if ($imageData === false) {
        throw new \Exception('Invalid base64 image data');
    }

    // Generate unique file name (png default)
    $fileName = uniqid() . '.png';
    $filePath = $folder . '/' . $fileName;

    if (!\Storage::disk('public')->put($filePath, $imageData)) {
        throw new \Exception('Failed to save image');
    }

    return $filePath;
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
