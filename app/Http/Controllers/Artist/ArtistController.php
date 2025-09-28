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

        // Ownership check
        if ($artist->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized to update this profile.'
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string|max:255',
            'email'       => 'sometimes|required|email|max:255',
            'genre'       => 'nullable|string|max:255',
            'bio'         => 'nullable|string',
            'city'        => 'nullable|string|max:255',
            'image'       => 'nullable|string',       // Base64 or existing path
            'cover_photo' => 'nullable|string',       // Base64 or existing path
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Update user info (name & email)
        if ($artist->user) {
            $artist->user->update([
                'name' => $validated['name'] ?? $artist->user->name,
            ]);
        }

        // Handle base64 images
        $imageFields = [
            'image' => 'artist/images',
            'cover_photo' => 'artist/covers'
        ];

        foreach ($imageFields as $field => $folder) {
            if (isset($validated[$field])) {
                $newValue = $validated[$field];

                if (str_starts_with($newValue, 'data:image')) {
                    // Delete old file if exists
                    if ($artist->$field) {
                        Storage::disk('public')->delete($artist->$field);
                    }

                    // Save new image and get file path
                    $artist->$field = $this->saveBase64Image($newValue, $folder);
                } else {
                    // Keep existing path
                    $artist->$field = $newValue;
                }

                // Remove from validated so fill() does not overwrite
                unset($validated[$field]);
            }
        }

        // Update other fields safely
        $artist->fill($validated);
        $artist->save();

        // Refresh and add full URL
        $artist->refresh();
        $artist->image_url = $artist->image ? asset('storage/'.$artist->image) : null;
        $artist->cover_photo_url = $artist->cover_photo ? asset('storage/'.$artist->cover_photo) : null;

        return response()->json([
            'data'    => $artist,
            'success' => true,
            'status'  => 200,
            'message' => 'Artist profile updated successfully.',
        ]);

    } catch (\Exception $e) {
        Log::error("Update profile failed for Artist ID {$id}: ".$e->getMessage(), [
            'request' => $request->all()
        ]);

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
