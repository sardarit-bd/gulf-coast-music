<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
class ArtistController extends Controller
{
    /**
     * Display the authenticated artist profile.
     */
    public function index()
    {
        try {
            $artist = Auth::user()
                ->artist()
                ->with(['photos', 'songs'])
                ->first();

            if (!$artist) {
                return response()->json([
                    'error' => 'Please complete your artist profile first.'
                ], 404);
            }

            $artist->email = Auth::user()->email;
            // convert image urls
            $artist->image_url = $artist->image ? url('public/'.'storage/'.($artist->image)) : null;
            $artist->cover_photo_url = $artist->cover_photo ? url('public/'.Storage::url($artist->cover_photo)) : null;
            // returning response
            return response()->json([
                'data' => $artist,
                'success' => true,
                'status' => 200,
                'message' => 'Artist profile fetched successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred while fetching the artist profile.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created artist profile.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'bio'         => 'nullable|string',
                'city'        => 'nullable|string|max:255',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'cover_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            ]);

            $data = [
                'user_id' => Auth::id(),
                'name'    => $validated['name'],
                'bio'     => $validated['bio'] ?? null,
                'city'    => $validated['city'] ?? null,
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('artist/images', 'public');
            }
            if ($request->hasFile('cover_photo')) {
                $data['cover_photo'] = $request->file('cover_photo')->store('artist/covers', 'public');
            }

            $artist = Artist::create($data);

            return response()->json([
                'data'    => $artist,
                'success' => true,
                'status'  => 201,
                'message' => 'Artist profile created successfully.',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred while creating the artist profile.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified artist.
     */
    public function show(Artist $artist)
    {
        try {
            $artist->load(['photos', 'songs', 'genres']);
            $artist->image_url = $artist->image ? Storage::url($artist->image) : null;
            $artist->cover_photo_url = $artist->cover_photo ? Storage::url($artist->cover_photo) : null;

            return response()->json([
                'data'    => $artist,
                'success' => true,
                'status'  => 200,
                'message' => 'Artist profile fetched successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred while fetching the artist.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified artist profile.
     */

    public function updateProfile(Request $request, $id)
    {
        try {
            $artist = Artist::with('user')->findOrFail($id);

            if ($artist->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized to update this profile.'], 403);
            }

            // Accept file upload OR base64 OR keep existing
            $validator = Validator::make($request->all(), [
                'name'        => 'sometimes|required|string|max:255',
                'email'       => 'sometimes|email|max:255',
                'genre'       => 'nullable|string|max:255',
                'bio'         => 'nullable|string',
                'city'        => 'nullable|string|max:255',

                // allow either file uploads or strings (base64/existing path)
                'image'       => 'nullable',
                'cover_photo' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Validation failed', 'message' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            // --- Update user profile fields ---
            if ($artist->user) {
                $artist->user->update([
                    'name'  => $validated['name']  ?? $artist->user->name,
                    // uncomment if you actually want to allow changing email on User:
                    // 'email' => $validated['email'] ?? $artist->user->email,
                ]);
            }

            // --- Handle images (file upload or base64 or keep) ---
            $imageMap = [
                'image'       => 'artist/images',
                'cover_photo' => 'artist/covers',
            ];

            foreach ($imageMap as $field => $folder) {
                // Case A: real file uploaded (multipart/form-data)
                if ($request->hasFile($field)) {
                    // delete old
                    if ($artist->$field) {
                        Storage::disk('public')->delete($artist->$field);
                    }
                    $path = $request->file($field)->store($folder, 'public'); // returns relative path
                    $artist->$field = $path;
                    unset($validated[$field]);
                    continue;
                }

                // Case B: base64 string sent
                if (isset($validated[$field]) && is_string($validated[$field])) {
                    $val = $validated[$field];

                    if (str_starts_with($val, 'data:image')) {
                        if ($artist->$field) {
                            Storage::disk('public')->delete($artist->$field);
                        }
                        $artist->$field = $this->saveBase64Image($val, $folder); // returns relative path
                    } else {
                        // If they passed an existing relative path (e.g. "artist/images/xxx.png"), keep it.
                        // If they passed a full URL ("https://.../storage/artist/images/xxx.png"), strip to storage-relative.
                        if (str_starts_with($val, 'http')) {
                            // try to extract after "/storage/"
                            $pos = strpos($val, '/storage/');
                            if ($pos !== false) {
                                $artist->$field = ltrim(substr($val, $pos + strlen('/storage/')), '/');
                            } else {
                                // Unknown URL -> keep old value instead of corrupting
                                $artist->$field = $artist->$field;
                            }
                        } else {
                            // assume already a storage-relative path
                            $artist->$field = ltrim($val, '/');
                            // Avoid accidental "storage/storage/..." later
                            $artist->$field = preg_replace('#^storage/#', '', $artist->$field);
                        }
                    }

                    unset($validated[$field]); // prevent fill from overwriting
                }
            }

            // --- Update other non-image fields ---
            $artist->fill($validated);
            $artist->save();

            // --- Fresh URLs using Storage::url (works with storage:link) ---
            $artist->refresh();
            $artist->image_url       = $artist->image       ? Storage::url($artist->image)       : null;
            $artist->cover_photo_url = $artist->cover_photo ? Storage::url($artist->cover_photo) : null;

            return response()->json([
                'data'    => $artist,
                'success' => true,
                'status'  => 200,
                'message' => 'Artist profile updated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error("Update profile failed for Artist ID {$id}: ".$e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'error'   => 'An error occurred while updating the artist profile.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Save Base64 image to storage and return relative path
     */
    private function saveBase64Image($base64Image, $folder)
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            throw new \Exception('Invalid base64 image');
        }

        $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
        $imageData = str_replace(' ', '+', $imageData);
        $decoded = base64_decode($imageData);

        if ($decoded === false) {
            throw new \Exception('Base64 decode failed');
        }

        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $fileName = $folder.'/'.substr(uniqid(), 0, 13).'.'.strtolower($type[1]);
        Storage::disk('public')->put($fileName, $decoded);

        return $fileName;
    }


    /**
     * Remove the specified artist profile.
     */
    public function destroy(Artist $artist)
    {
        try {
            if ($artist->user_id !== Auth::id()) {
                return response()->json([
                    'error' => 'Unauthorized to delete this profile.'
                ], 403);
            }

            if ($artist->image) {
                Storage::disk('public')->delete($artist->image);
            }
            if ($artist->cover_photo) {
                Storage::disk('public')->delete($artist->cover_photo);
            }

            $artist->delete();

            return response()->json([
                'success' => true,
                'status'  => 200,
                'message' => 'Artist profile deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred while deleting the artist profile.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
