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
                'data' => $songs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
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
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'file'  => 'nullable|mimes:mp3,wav,ogg|max:10240', // max 10MB
                'link'  => 'nullable|url'
            ]);

            $artist = Auth::user()->artist;

            $path = null;
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('artist/songs', 'public');
            }

            $song = $artist->songs()->create([
                'title'     => $validated['title'],
                'file_path' => $path,
                'link'      => $validated['link'] ?? null,
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
