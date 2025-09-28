<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistSong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistSongController extends Controller
{
    /**
     * Show all songs of the authenticated artist.
     */
    public function index()
    {
        try {
            $songs = Auth::user()->artist->songs;

            return response()->json([
                'data' => [
                    'songs' => $songs
                ],
                'success' => true,
                'status' => 200,
                'message' => 'Songs fetched successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'success' => false,
                'error'   => 'Failed to fetch songs.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new song for the authenticated artist.
     */
    public function store(Request $request)
    {
        return response()->json([
            'success' => $request->all(),
        ]);
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'link'  => 'nullable|url'
            ]);

            $artist = Auth::user()->artist;

            $song = $artist->songs()->create([
                'title'     => $validated['title'],
                'mp3_url'      => $validated['link'] ?? null,
            ]);

            return response()->json([
                'message' => 'Song added successfully.',
                'data'    => $song
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to add song.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a song.
     */
    public function destroy(ArtistSong $song)
    {
        try {
            // Ensure the logged-in artist owns this song
            if ($song->artist_id !== Auth::user()->artist->id) {
                return response()->json([
                    'error' => 'Unauthorized to delete this song.'
                ], 403);
            }

            $song->delete();

            return response()->json([
                'message' => 'Song deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to delete song.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
