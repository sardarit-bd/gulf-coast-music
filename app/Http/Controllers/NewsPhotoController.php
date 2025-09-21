<?php

namespace App\Http\Controllers\Journalist;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsPhotoController extends Controller
{
    /**
     * Store photos for a news article (max 5).
     */
    public function store(Request $request, News $news)
    {
        $request->validate([
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // count existing photos
        $existingCount = $news->newsPhotos()->count();
        $newCount = count($request->file('photos', []));

        if ($existingCount + $newCount > 5) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'message' => 'A news article can have maximum 5 photos.',
            ], 422);
        }

        $savedPhotos = [];

        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store('news/photos', 'public');
            $savedPhotos[] = $news->newsPhotos()->create([
                'photo_path' => $path,
            ]);
        }

        return response()->json([
            'data' => $savedPhotos,
            'success' => true,
            'status' => 201,
            'message' => 'Photos uploaded successfully.',
        ], 201);
    }

    /**
     * Delete a specific photo.
     */
    public function destroy(NewsPhoto $photo)
    {
        if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Photo deleted successfully.',
        ], 200);
    }
}
