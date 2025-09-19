<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtistGenreController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'genre_ids' => 'required|array',
            'genre_ids.*' => 'exists:genres,id'
        ]);

        $artist = Auth::user()->artist;
        $artist->genres()->syncWithoutDetaching($request->genre_ids);

        return response()->json(['message' => 'Genres added successfully.']);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'genre_ids' => 'required|array',
            'genre_ids.*' => 'exists:genres,id'
        ]);

        $artist = Auth::user()->artist;
        $artist->genres()->detach($request->genre_ids);

        return response()->json(['message' => 'Genres removed successfully.']);
    }
}
