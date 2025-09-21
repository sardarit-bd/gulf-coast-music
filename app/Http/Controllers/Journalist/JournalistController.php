<?php

namespace App\Http\Controllers\Journalist;

use App\Http\Controllers\Controller;
use App\Models\Journalist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JournalistController extends Controller
{
    /**
     * Display a listing of the journalists.
     */
    public function index()
    {
        $journalists = Journalist::latest()->get();

        return response()->json([
            'data' => $journalists,
            'success' => true,
            'status' => 200,
            'message' => 'Journalists fetched successfully.',
        ], 200);
    }

    /**
     * Store a newly created journalist.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:journalists,email',
            'phone'   => 'nullable|string|max:20',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Default image
        $imagePath = 'public/pre_images/anonymous_user.png';

        // If file uploaded, store in storage/app/public/journalists/images
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('journalists/images', 'public');
        }

        $validated['image'] = $imagePath;

        $journalist = Journalist::create($validated);

        return response()->json([
            'data' => $journalist,
            'success' => true,
            'status' => 201,
            'message' => 'Journalist created successfully.',
        ], 201);
    }

    /**
     * Display a specific journalist.
     */
    public function show(Journalist $journalist)
    {
        return response()->json([
            'data' => $journalist,
            'success' => true,
            'status' => 200,
            'message' => 'Journalist fetched successfully.',
        ], 200);
    }

    /**
     * Update the specified journalist.
     */
    public function update(Request $request, Journalist $journalist)
    {
        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'email'   => [
                'sometimes','required','string','email','max:255',
                Rule::unique('journalists')->ignore($journalist->id),
            ],
            'phone'   => 'nullable|string|max:20',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($journalist->image && Storage::disk('public')->exists($journalist->image)) {
                Storage::disk('public')->delete($journalist->image);
            }

            $validated['image'] = $request->file('image')->store('journalists/images', 'public');
        }

        $journalist->update($validated);

        return response()->json([
            'data' => $journalist,
            'success' => true,
            'status' => 200,
            'message' => 'Journalist updated successfully.',
        ], 200);
    }

    /**
     * Remove the specified journalist.
     */
    public function destroy(Journalist $journalist)
    {
        if ($journalist->image && Storage::disk('public')->exists($journalist->image)) {
            Storage::disk('public')->delete($journalist->image);
        }

        $journalist->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Journalist deleted successfully.',
        ], 200);
    }
}
