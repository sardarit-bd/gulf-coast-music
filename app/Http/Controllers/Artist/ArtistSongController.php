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

            $songs = $artist->songs()->latest('id')->paginate($perPage);

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
     * Accepts:
     *  - multipart/form-data: audio=<file>, title=<string?>
     *  - application/json: audio|audio_b64|file|data|payload = base64 (with/without data URI), title=<string?>
     */
    public function store(Request $request)
    {
        try {
            // ---- Snapshots ----
            $raw = $request->getContent() ?? '';
            Log::info('Incoming upload snapshot', [
                'content_type' => $request->header('Content-Type'),
                'content_len'  => (int) $request->header('Content-Length'),
                'raw_len'      => strlen($raw),
                'keys_before'  => array_keys($request->all() ?? []),
                'has_file'     => $request->hasFile('audio'),
                'files_keys'   => array_keys($request->allFiles() ?? []),
            ]);

            // ---- Robust merge for text/plain / partially stripped JSON ----
            if (empty($request->all()) || (!$request->hasFile('audio') && !$request->hasAny(['audio','audio_b64','file','data','payload']))) {
                $ct = $request->header('Content-Type', '');
                if (str_contains($ct, 'text/plain') || str_contains($ct, 'application/json')) {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $request->merge($decoded);
                    }
                }
            }

            // ---- Extract base64 from multiple safe keys (WAF may strip 'audio') ----
            $b64 = $this->extractBase64AudioFromRequest($request);

            Log::info('Post-merge payload snapshot', [
                'keys'      => array_keys($request->all() ?? []),
                'title_len' => strlen((string) $request->input('title')),
                'b64_len'   => $b64 ? strlen($b64) : 0,
                'has_file'  => $request->hasFile('audio'),
            ]);

            // ---- Auth/profile ----
            $artist = Auth::user()->artist;
            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'status'  => 404,
                    'error'   => 'Artist not found',
                    'message' => 'This user does not have an artist profile.',
                ], 404);
            }

            // ---- Title (optional; we’ll derive if missing) ----
            $title = trim((string) $request->input('title', ''));

            $path = null;

            if ($request->hasFile('audio')) {
                // Multipart file path
                $request->validate([
                    'audio' => 'required|file|mimes:mp3,wav,ogg,mp4|max:20480', // 20MB
                    'title' => 'nullable|string|max:255',
                ]);

                $path = $request->file('audio')->store("artist/{$artist->id}/songs", 'public');

                if ($title === '') {
                    $orig  = $request->file('audio')->getClientOriginalName();
                    $title = Str::title(str_replace(['_', '-'], ' ', pathinfo($orig, PATHINFO_FILENAME)));
                    $title = trim(preg_replace('/\s+/', ' ', $title)) ?: ('Untitled ' . now()->format('Ymd_His'));
                }

            } else {
                // Base64 path (from any accepted key)
                if (!$b64) {
                    return response()->json([
                        'success' => false,
                        'status'  => 422,
                        'error'   => 'Validation failed',
                        'message' => [
                            'audio' => ['Provide either a multipart file named "audio" or a base64 string in one of: audio, audio_b64, file, data, payload.']
                        ],
                    ], 422);
                }

                // Optional: light validation of title only
                $request->validate([
                    'title' => 'nullable|string|max:255',
                ]);

                $path = $this->saveBase64AudioFlexible(
                    $b64,
                    "artist/{$artist->id}/songs",
                    20 * 1024 * 1024 // 20MB
                );

                if ($title === '') {
                    $fallbackName = $request->input('filename')
                        ?? $request->input('name')
                        ?? basename($path);
                    $title = Str::title(str_replace(['_', '-', '.mp3', '.wav', '.ogg', '.mp4'], ' ', $fallbackName));
                    $title = trim(preg_replace('/\s+/', ' ', $title)) ?: ('Untitled ' . now()->format('Ymd_His'));
                }
            }

            // ---- Persist ----
            $song = $artist->songs()->create([
                'title'   => $title,
                'mp3_url' => $path,
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
            Log::error('Song store error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
     * Extract base64 audio from multiple allowed keys or raw JSON.
     */
    private function extractBase64AudioFromRequest(Request $request): ?string
    {
        // Priority order; WAF sometimes strips a specific key like "audio"
        $candidates = [
            'audio',
            'audio_b64',
            'file',
            'data',
            'payload',
        ];

        foreach ($candidates as $key) {
            $val = $request->input($key);
            if (is_string($val) && $val !== '') {
                return $val;
            }
        }

        // Try raw JSON again (some proxies send text/plain)
        $raw = $request->getContent() ?? '';
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($candidates as $key) {
                    if (isset($decoded[$key]) && is_string($decoded[$key]) && $decoded[$key] !== '') {
                        return $decoded[$key];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Save base64 to public disk and return relative path.
     */
    private function saveBase64AudioFlexible(string $audio, string $folder, int $maxBytes = 20971520): string
    {
        $ext = 'mp3';
        if (preg_match('/^data:audio\/([\w.+-]+);base64,/', $audio, $m)) {
            $ext  = strtolower($m[1]);
            $data = substr($audio, strpos($audio, ',') + 1);
        } else {
            $data = $audio;
        }

        $data    = str_replace(' ', '+', $data);
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new \Exception('Base64 decode failed for audio payload.');
        }

        if (strlen($decoded) > $maxBytes) {
            throw new \Exception('Audio exceeds maximum allowed size.');
        }

        $ext = str_ireplace(['mpeg', 'x-wav'], ['mp3', 'wav'], $ext);
        if (!in_array($ext, ['mp3', 'wav', 'ogg', 'mp4'])) {
            $ext = 'mp3';
        }

        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $fileName = uniqid('audio_', true) . '.' . $ext;
        $path     = $folder . '/' . $fileName;

        Storage::disk('public')->put($path, $decoded);

        return $path;
    }
}
