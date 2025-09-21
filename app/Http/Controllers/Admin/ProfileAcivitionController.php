<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileAcivitionController extends Controller
{
    public function pendingProfiles()
    {
        $pendingUsers = User::where('status', 'Inactive')->get();

        return response()->json([
            'pending_users' => $pendingUsers,
            'success' => true,
            'status' => 200,
            'message' => 'Pending profiles retrieved successfully.'
        ]);
    }

    public function activateProfile($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'status' => 404,
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->status === 'Active') {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'User profile is already active.'
            ], 400);
        }

        $user->status = 'Active';
        $user->save();

        Artist::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'genre' => '',
            'image' => '',
            'cover_photo' => '',
            'bio' => '',
            'city' => '',
        ]);


        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'User profile activated successfully.',
            'user' => $user
        ]);
    }
}
