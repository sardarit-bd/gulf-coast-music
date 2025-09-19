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
     * Display a listing of the resource.
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

            return response()->json($artist, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the artist profile.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'bio'  => 'nullable|string',
                'city' => 'nullable|string|max:255',
            ]);

            Artist::create(array_merge($validated, [
                'user_id' => Auth::id()
            ]));

            return response()->json([
                'message' => 'Artist profile created successfully.'
            ], 201);
        } catch (ValidationException $e) {
            // Validation error handle
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Other exceptions
            return response()->json([
                'error'   => 'An error occurred while creating the artist profile.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function show(Artist $artist)
    {
        //
    }

    public function update(Request $request, Artist $artist)
    {
        //
    }

    public function destroy(Artist $artist)
    {
        //
    }
}
