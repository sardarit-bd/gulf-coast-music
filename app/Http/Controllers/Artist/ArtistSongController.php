<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistSong;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Accepts EITHER:
     * - multipart/form-data:   audio=<file>, title=<string?>
     * - application/json:      { "audio": "<base64 or data URI>", "title": "<string?>" }
     */
    public function store(Request $request)
    {
        try {
            // --- Diagnostics about the incoming request ---
            $rawLen = strlen($request->getContent() ?? '');
            Log::info('Incoming upload snapshot', [
                'content_type' => $request->header('Content-Type'),
                'content_len'  => (int) $request->header('Content-Length'),
                'raw_len'      => $rawLen,
                'keys_before'  => array_keys($request->all() ?? []),
            ]);

            // --- Fallback: manually parse JSON if Laravel didn't (e.g., text/plain) ---
            if (empty($request->all())) {
                $ct = $request->header('Content-Type', '');
                if (str_contains($ct, 'text/plain') || str_contains($ct, 'application/json')) {
                    $decoded = json_decode($request->getContent(), true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $request->merge($decoded);
                    }
                }
            }

            Log::info('Post-merge payload snapshot', [
                'keys'       => array_keys($request->all() ?? []),
                'title_len'  => strlen((string) $request->input('title')),
                'audio_len'  => strlen((string) $request->input('audio')),
                'has_file'   => $request->hasFile('audio'),
                'files_keys' => array_keys($request->allFiles() ?? []),
            ]);

            // --- Auth / profile check ---
            $artist = Auth::user()->artist;
            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'error'   => 'Artist not found',
                    'message' => 'This user does not have an artist profile.',
                ], 404);
            }

            // --- Accept either multipart file OR base64 string ---
            $path  = null;
            $title = trim((string) $request->input('title', ''));

            if ($request->hasFile('audio')) {
                // Multipart branch
                $request->validate([
                    'audio' => 'required|file|mimes:mp3,wav,ogg,mp4|max:20480', // 20MB
                    'title' => 'nullable|string|max:255',
                ]);

                $path = $request->file('audio')
                                ->store("artist/{$artist->id}/songs", 'public');

                // Derive title from original filename if missing
                if ($title === '') {
                    $orig  = $request->file('audio')->getClientOriginalName();
                    $title = Str::title(str_replace(['_', '-'], ' ', pathinfo($orig, PATHINFO_FILENAME)));
                    $title = trim(preg_replace('/\s+/', ' ', $title));
                    if ($title === '') {
                        $title = 'Untitled ' . now()->format('Ymd_His');
                    }
                }

            } else {
                // Base64 branch
                $request->validate([
                    'audio' => 'required|string',      // base64 string (with or without data URI)
                    'title' => 'nullable|string|max:255',
                ]);

                $audioB64 = (string) $request->input('audio');
                if ($audioB64 === '') {
                    return response()->json([
                        'success' => false,
                        'status'  => 422,
                        'error'   => 'Validation failed',
                        'message' => [
                            'audio' => ['Provide either a multipart file named "audio" or a base64 string in "audio".']
                        ],
                    ], 422);
                }

                $path = $this->saveBase64AudioFlexible(
                    $audioB64,
                    "artist/{$artist->id}/songs",
                    20 * 1024 * 1024 // 20MB
                );

                // Derive title if still empty: try optional filename/name fields, else from path
                if ($title === '') {
                    $fallbackName = $request->input('filename')
                        ?? $request->input('name')
                        ?? basename($path);

                    $title = Str::title(str_replace(['_', '-', '.mp3', '.wav', '.ogg', '.mp4'], ' ', $fallbackName));
                    $title = trim(preg_replace('/\s+/', ' ', $title));
                    if ($title === '') {
                        $title = 'Untitled ' . now()->format('Ymd_His');
                    }
                }
            }

            // --- Persist DB row ---
            $song = $artist->songs()->create([
                'title'   => $title,
                'mp3_url' => $path, // relative path
            ]);

            // Public URL for frontend playback
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

            // Delete stored file if present
            if (!empty($song->mp3_url) && Storage::disk('public')->exists($song->mp3_url)) {
                Storage::disk('public')->delete($song->mp3_url);
            }

            $song->delete();

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

    /**
     * Save base64 audio to the public disk and return the relative path.
     * Accepts data URIs (e.g., data:audio/mp3;base64,...) or raw base64 strings.
     */
    private function saveBase64AudioFlexible(string $audio, string $folder, int $maxBytes = 20971520): string
    {
        // Accept both "data:audio/mp3;base64,..." and raw base64 "AAAA..."
        $ext = 'mp3';
        if (preg_match('/^data:audio\/([\w.+-]+);base64,/', $audio, $m)) {
            $ext  = strtolower($m[1]); // mp3, mpeg, wav, ogg, mp4, x-wav, etc.
            $data = substr($audio, strpos($audio, ',') + 1);
        } else {
            $data = $audio;
        }

        // Normalize and decode
        $data    = str_replace(' ', '+', $data);
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new \Exception('Base64 decode failed for audio payload.');
        }

        // Enforce size limit (decoded bytes)
        if (strlen($decoded) > $maxBytes) {
            throw new \Exception('Audio exceeds maximum allowed size.');
        }

        // Normalize some extensions
        $ext = str_ireplace(['mpeg', 'x-wav'], ['mp3', 'wav'], $ext);
        if (!in_array($ext, ['mp3', 'wav', 'ogg', 'mp4'])) {
            $ext = 'mp3';
        }

        // Ensure folder exists
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $fileName = uniqid('audio_', true) . '.' . $ext;
        $path     = $folder . '/' . $fileName;

        Storage::disk('public')->put($path, $decoded);

        return $path; // relative path for DB
    }
}
