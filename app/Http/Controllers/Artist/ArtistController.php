<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
                ->with(['photos', 'songs', 'genres'])
                ->first();

            if (!$artist) {
                return response()->json([
                    'error' => 'Please complete your artist profile first.'
                ], 404);
            }

            return response()->json([
                'data' => $artist
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
                'name' => 'required|string|max:255',
                'bio'  => 'nullable|string',
                'city' => 'nullable|string|max:255',
            ]);

            $artist = Artist::create(array_merge($validated, [
                'user_id' => Auth::id()
            ]));

            return response()->json([
                'message' => 'Artist profile created successfully.',
                'data'    => $artist
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

            return response()->json([
                'data' => $artist
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
    public function update(Request $request, Artist $artist)
    {
        try {
            // Ensure the logged-in user is the owner
            if ($artist->user_id !== Auth::id()) {
                return response()->json([
                    'error' => 'Unauthorized to update this profile.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'bio'  => 'nullable|string',
                'city' => 'nullable|string|max:255',
            ]);

            $artist->update($validated);

            return response()->json([
                'message' => 'Artist profile updated successfully.',
                'data'    => $artist
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'An error occurred while updating the artist profile.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified artist profile.
     */
    public function destroy(Artist $artist)
    {
        try {
            // Ensure the logged-in user is the owner
            if ($artist->user_id !== Auth::id()) {
                return response()->json([
                    'error' => 'Unauthorized to delete this profile.'
                ], 403);
            }

            $artist->delete();

            return response()->json([
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
