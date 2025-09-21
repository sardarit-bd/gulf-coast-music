<?php

namespace App\Http\Controllers\Journalist;

use App\Http\Controllers\Controller;
use App\Models\Journalist;
use Illuminate\Http\Request;

class JournalistController extends Controller
{
    public function index()
    {   $journalists = Journalist::all();
        return response()->json([
        'data'=>[
            'journalists'=>$journalists
        ],
        'success' => true,
        'status' => 200,
        'message' => 'Journalists fetched successfully.',
    ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:journalists',
            'phone' => 'nullable|string',
            'image' => 'nullable|string|max:255',
        ]);

        $validated['image'] = url('public/pre_images/annonimouse_user.png');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('journalist/images', 'public');
        }

        $journalist = Journalist::create($validated);

        return response()->json([
            'data' => $journalist,
            'success' => true,
            'status' => 201,
            'message' => 'Journalist created successfully.',
        ], 201);
    }
}
