<?php

namespace App\Http\Controllers\Journalist;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    /**
     * Display a listing of news.
     */
    public function index()
    {
        $news = News::with(['journalist', 'newsPhotos'])
            ->latest('news_date')
            ->get();

        return response()->json([
            'data' => $news,
            'success' => true,
            'status' => 200,
            'message' => 'News fetched successfully.',
        ], 200);
    }

    /**
     * Store a newly created news.
     */
    public function store(Request $request)
    {
    $validated = $request->validate([
        'journalist_id' => 'required|exists:journalists,id',
        'title'         => 'required|string|max:255',
        'description'   => 'required|string',
        'news_date'     => 'required|date',
        'location'      => 'nullable|string|max:255',
        'credit'        => 'nullable|string|max:255',
        'status'        => 'required|in:draft,published,archived',
        'published_at'  => 'nullable|date',
        'photos'        => 'nullable|array|max:5', // max 5 photos
        'photos.*'      => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096',
    ]);

    // Create news first
    $news = News::create($validated);

    // Handle photos (if any)
    $savedPhotos = [];
    if ($request->hasFile('photos')) {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('news/photos', 'public');
            $savedPhotos[] = $news->newsPhotos()->create([
                'photo_path' => $path,
            ]);
        }
    }

    return response()->json([
        'data' => [
            'news'   => $news->load(['journalist', 'newsPhotos']),
            'photos' => $savedPhotos,
        ],
        'success' => true,
        'status' => 201,
        'message' => 'News created successfully with photos.',
    ], 201);
    }


    /**
     * Display the specified news.
     */
    public function show(News $news)
    {
        return response()->json([
            'data' => $news->load(['journalist', 'newsPhotos']),
            'success' => true,
            'status' => 200,
            'message' => 'News fetched successfully.',
        ], 200);
    }

    /**
     * Update the specified news.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'description'  => 'sometimes|required|string',
            'news_date'    => 'sometimes|required|date',
            'location'     => 'nullable|string|max:255',
            'credit'       => 'nullable|string|max:255',
            'status'       => 'sometimes|required|in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        $news->update($validated);

        return response()->json([
            'data' => $news->load(['journalist', 'newsPhotos']),
            'success' => true,
            'status' => 200,
            'message' => 'News updated successfully.',
        ], 200);
    }

    /**
     * Remove the specified news.
     */
    public function destroy(News $news)
    {
        $news->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'News deleted successfully.',
        ], 200);
    }
}
