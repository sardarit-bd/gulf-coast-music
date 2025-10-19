<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistSong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Str;

class ArtistSongController extends Controller
{
    /**
     * GET /api/artist/songs
     * List all songs for the authenticated artist (paginated).
     * Query params: ?page=1&per_page=15
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $artist = $user?->artist;

            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'error'   => 'Artist not found',
                    'message' => 'This user does not have an artist profile.',
                    'data'    => ['songs' => []],
                ], 404);
            }

            $perPage = (int) ($request->query('per_page', 15));
            $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 15;

            $songs = $artist->songs()
                ->latest('id')
                ->paginate($perPage);

            // Append public URLs
            $songs->getCollection()->transform(function (ArtistSong $song) {
                $song->file_url = $song->mp3_url ? Storage::url($song->mp3_url) : null;
                return $song;
            });

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Songs fetched successfully.',
                'data'    => ['songs' => $songs],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Songs index error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'status'  => 500,
                'error'   => 'Failed to fetch songs.',
                'message' => $e->getMessage(),
                'data'    => ['songs' => []],
            ], 500);
        }
    }

    /**
     * POST /api/artist/songs
     * Store a new song for the authenticated artist (multipart/form-data).
     * Body: title (string), audio (file: mp3|wav|ogg)
     */
    // public function store(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'title' => 'required|string|max:255',
    //             'audio' => 'required|file|mimes:mp3,wav,mp4,ogg|max:20480',
    //         ]);

    //         $artist = Auth::user()->artist;
    //         if (!$artist) {
    //             return response()->json([
    //                 'success' => false,
    //                 'status'  => 404,
    //                 'error'   => 'Artist not found',
    //                 'message' => 'This user does not have an artist profile.',
    //             ], 404);
    //         }

    //         // Store file to public disk, keep relative path in DB
    //         $path = $request->file('audio')->store("artist/{$artist->id}/songs", 'public');
    //         Log::info('Song audio uploaded', ['user_id' => Auth::id(), 'path' => $path]);

    //         $song = $artist->songs()->create([
    //             'title'   => $validated['title'],
    //             'mp3_url' => $path,
    //         ]);


    //         $song->file_url = Storage::url($song->mp3_url);

    //         return response()->json([
    //             'success' => true,
    //             'status'  => 201,
    //             'message' => 'Song added successfully.',
    //             'data'    => $song,
    //         ], 201);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'status'  => 422,
    //             'error'   => 'Validation failed',
    //             'message' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         Log::error('Song store error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
    //         return response()->json([
    //             'success' => false,
    //             'status'  => 500,
    //             'error'   => 'Failed to add song.',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


public function store(Request $request)
{
    try {
        // ---------- Fallback: raw JSON merge if Laravel didn't parse ----------
        // Some clients send 'text/plain' with a JSON string. Laravel won't parse that.
        if (empty($request->all())) {
            $ct = $request->header('Content-Type', '');
            if (str_contains($ct, 'text/plain') || str_contains($ct, 'application/json')) {
                $raw = $request->getContent();
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge($decoded);
                }
            }
        }

        Log::info('Incoming base64 upload payload snapshot', [
            'content_type' => $request->header('Content-Type'),
            'keys'         => array_keys($request->all() ?? []),
            'title_len'    => strlen((string) $request->input('title')),
            'audio_len'    => strlen((string) $request->input('audio')),
        ]);

        // ---------- Validate (base64 as string) ----------
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'audio' => 'required|string', // base64 string (with or without data URI)
        ]);

        $artist = Auth::user()->artist;
        if (!$artist) {
            return response()->json([
                'success' => false,
                'status'  => 404,
                'error'   => 'Artist not found',
                'message' => 'This user does not have an artist profile.',
            ], 404);
        }

        // ---------- Persist decoded audio ----------
        $path = $this->saveBase64AudioFlexible(
            $validated['audio'],
            "artist/{$artist->id}/songs"
        );

        $song = $artist->songs()->create([
            'title'   => $validated['title'],
            'mp3_url' => $path, // relative path
        ]);

        $song->file_url = Storage::url($song->mp3_url);

        return response()->json([
            'success' => true,
            'status'  => 201,
            'message' => 'Song added successfully.',
            'data'    => $song,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'status'  => 422,
            'error'   => 'Validation failed',
            'message' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Song store error: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'status'  => 500,
            'error'   => 'Failed to add song.',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * DELETE /api/artist/songs/{song}
     * Delete a song (and its stored file) owned by the authenticated artist.
     */
    public function destroy(ArtistSong $song)
    {
        try {
            $artist = Auth::user()->artist;
            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'error'   => 'Artist not found',
                    'message' => 'This user does not have an artist profile.',
                ], 404);
            }

            if ($song->artist_id !== $artist->id) {
                return response()->json([
                    'success' => false,
                    'status'  => 403,
                    'error'   => 'Unauthorized',
                    'message' => 'You are not allowed to delete this song.',
                ], 403);
            }

            // Delete file from storage if present
            if (!empty($song->mp3_url) && Storage::disk('public')->exists($song->mp3_url)) {
                Storage::disk('public')->delete($song->mp3_url);
            }

            $song->delete();
            // Log deletion
            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Song deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Song destroy error: '.$e->getMessage(), ['song_id' => $song->id ?? null]);
            return response()->json([
                'success' => false,
                'status'  => 500,
                'error'   => 'Failed to delete song.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    // base 64 handler
private function saveBase64AudioFlexible(string $audio, string $folder): string
{
    // Accept both "data:audio/mp3;base64,AAA..." and raw "AAA..." strings
    $mime = null;
    $ext  = 'mp3'; // sensible default

    if (preg_match('/^data:audio\/([\w.+-]+);base64,/', $audio, $m)) {
        $ext  = strtolower($m[1]);     // mp3, mpeg, wav, ogg, mp4, x-wav...
        $data = substr($audio, strpos($audio, ',') + 1);
    } else {
        $data = $audio; // assume already base64 payload without header
    }

    // normalize & decode
    $data = str_replace(' ', '+', $data);
    $binary = base64_decode($data, true);
    if ($binary === false) {
        throw new \Exception('Base64 decode failed for audio payload.');
    }

    // Optional: light mime guess (if you want to refine ext)
    // $finfo = new \finfo(FILEINFO_MIME_TYPE);
    // $mime  = $finfo->buffer($binary);
    // if ($mime === 'audio/wav') $ext = 'wav';
    // elseif ($mime === 'audio/ogg') $ext = 'ogg';
    // elseif ($mime === 'audio/mpeg') $ext = 'mp3';

    // Ensure folder exists on public disk
    if (!Storage::disk('public')->exists($folder)) {
        Storage::disk('public')->makeDirectory($folder);
    }

    // Clean ext (e.g., "mpeg" → "mp3" if you prefer)
    $ext = str_ireplace(['mpeg', 'x-wav'], ['mp3', 'wav'], $ext);

    $fileName = uniqid('audio_', true) . '.' . $ext;
    $path     = $folder . '/' . $fileName;

    Storage::disk('public')->put($path, $binary);

    return $path; // relative
}


}
