<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VarifyMailer;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * Body: name, email, password, role [admin|artist|venue|journalist|fan]
     */
    public function register(Request $req)
    {
        try {
            $data = $req->validate([
                'name'     => ['required', 'string', 'max:120'],
                'email'    => ['required', 'email', 'max:191', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
                'role'     => ['required']
            ]);

            $user = User::create([
                'name'           => $data['name'],
                'email'          => $data['email'],
                'password'       => Hash::make($data['password']),
                'role'           => $data['role'],
                'status'         => 'active',
                'remember_token' => Str::random(10),
            ]);

            // Optional: If you use Laravel's email verification
            // $user->sendEmailVerificationNotification();

            // Send role instruction email if role is artist/venue/journalist (doc requirement)
            $this->sendRoleInstructionEmail($user);

            // Issue JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Registered',
                'token'   => $token,
                'user'    => $user,
            ], 201);
        } catch (ValidationException $e) {
            // Validation errors → 422
            return response()->json([
                'error'   => 'Validation failed',
                'message' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            // Database-related error → 400/500
            return response()->json([
                'error'   => 'Database error',
                'message' => $e->getMessage(),
            ], 500);
        } catch (JWTException $e) {
            // JWT issue → 500
            return response()->json([
                'error'   => 'Token creation failed',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Any other exception → 500
            return response()->json([
                'error'   => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * POST /api/auth/login
     * Body: email, password
     */
    public function login(Request $request)
    {
        // Validate the request
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Attempt login via JWT guard
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = auth('api')->user();

        // Check if user is active
        if ($user->status !== 'Active') {
            auth('api')->logout();
            return response()->json([
                'data' => [],
                'success' => false,
                'status' => 401,
                'message' => 'Account created but ' . $user->status
            ], 401);
        }

        return response()->json([
            'data' => [
                'token'   => $token,
                'user'    => $user
            ],
            'status' => 200,
            'success' => true,
            'message' => 'Logged in successfully',
        ], 200);
    }


    /**
     * GET /api/me  (protected)
     * Header: Authorization: Bearer <token>
     */
    public function me()
    {
        try {
            // টোকেন থেকে ইউজার বের করো; টোকেন না থাকলে/ভুল হলে JWTException হবে
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // মিনিমাল পে-লোড (বড় রিলেশন সিরিয়ালাইজিং এড়াতে)
            return response()->json([
                'data' => [
                    'id'     => $user->id,
                    'name'   => $user->username ?? $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'status' => $user->status,
                ],
                'success' => true,
                'status' => 200,
                'message' => 'User fetched successfully',
            ], 200);
        } catch (JWTException $e) {
            // টোকেন মিসিং/ইনভ্যালিড/এক্সপায়ার্ড
            return response()->json([
                'data' => [],
                'success' => false,
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        } catch (\Throwable $e) {
            \Log::error('ME endpoint failed: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'success' => false,
                'status' => 500,
                'message' => 'Server error'
            ], 500);
        }
    }


    /**
     * POST /api/auth/refresh  (protected)
     * Returns a new token by invalidating the old one.
     */
    public function refresh()
    {
        return response()->json([
            'token' => auth('api')->refresh(),
        ]);
    }

    /**
     * POST /api/auth/logout  (protected)
     * Invalidates the current token.
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Sends role-specific instruction email per client doc:
     * - Artist: ask user to email thegulfcoastmusic@gmail.com to request verification
     * - Venue:  same
     * - Journalist: same
     * Fan/Admin: no instruction email
     */
    private function sendRoleInstructionEmail(User $user): void
    {
        $role = $user->role;
        if (!in_array($role, ['Artist', 'Venue', 'Journalist'])) {
            return; // fan/admin => no instruction mail
        }

        try {
            Mail::to($user->email)->send(new VarifyMailer($user->email, $user->name, $role));
            \Log::info('Role instruction email sent to ' . $user->email);
        } catch (\Throwable $e) {
            \Log::warning('Role instruction email failed: ' . $e->getMessage());
        }
    }


    public function check()
    {
        return response()->json(['message' => 'OK']);
    }
}
