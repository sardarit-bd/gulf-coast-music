<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
