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
     * List all photos for a news article.
     */
    public function index(News $news)
    {
        return response()->json([
            'data' => $news->newsPhotos,
            'success' => true,
            'status' => 200,
            'message' => 'Photos fetched successfully.',
        ], 200);
    }

    /**
     * Store photos for a news article (max 5 per news).
     */
    public function store(Request $request, News $news)
    {
        $request->validate([
            'photos'   => 'required|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'alt'      => 'nullable|string|max:255',
        ]);

        $existingCount = $news->newsPhotos()->count();
        $newCount = count($request->file('photos', []));

        if ($existingCount + $newCount > 5) {
            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => 'A news article can have a maximum of 5 photos.',
            ], 422);
        }

        $savedPhotos = [];

        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('news/photos', 'public');
            $savedPhotos[] = $news->newsPhotos()->create([
                'path' => $path,
                'alt'  => $request->input('alt', null),
            ]);
        }

        return response()->json([
            'data'    => $savedPhotos,
            'success' => true,
            'status'  => 201,
            'message' => 'Photos uploaded successfully.',
        ], 201);
    }

    /**
     * Update photo alt text.
     */
    public function update(Request $request, NewsPhoto $photo)
    {
        $validated = $request->validate([
            'alt' => 'required|string|max:255',
        ]);

        $photo->update($validated);

        return response()->json([
            'data'    => $photo,
            'success' => true,
            'status'  => 200,
            'message' => 'Photo updated successfully.',
        ], 200);
    }

    /**
     * Remove a specific photo.
     */
    public function destroy(NewsPhoto $photo)
    {
        if ($photo->path && Storage::disk('public')->exists($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'status'  => 200,
            'message' => 'Photo deleted successfully.',
        ], 200);
    }
}
