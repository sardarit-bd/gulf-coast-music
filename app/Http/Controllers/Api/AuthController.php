<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VarifyMailer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
        $data = $req->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required']
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'status'   => 'active',
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
        ]);
    }

    /**
     * POST /api/auth/login
     * Body: email, password
     */
    public function login(Request $request)
    {

        $cred = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt login via jwt guard
        if (!$token = auth('api')->attempt($cred)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();
        if ($user->status !== 'active') {
            auth('api')->logout();
            return response()->json(['message' => 'Account is not active'], 403);
        }

        return response()->json([
            'message' => 'Logged in',
            'token'   => $token,
            'user'    => $user,
        ]);
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
                'id'     => $user->id,
                'name'   => $user->username ?? $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'status' => $user->status,
            ], 200);
        } catch (JWTException $e) {
            // টোকেন মিসিং/ইনভ্যালিড/এক্সপায়ার্ড
            return response()->json(['message' => 'Unauthorized'], 401);
        } catch (\Throwable $e) {
            \Log::error('ME endpoint failed: ' . $e->getMessage());
            return response()->json(['message' => 'Server error'], 500);
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
        if (!in_array($role, ['artist', 'venue', 'journalist'])) {
            return; // fan/admin => no instruction mail
        }

        $target = 'thegulfcoastmusic@gmail.com';
        $lines = [
            'artist'     => "Hello, please email {$target} to request verification as a Gulf Coast Artist.",
            'venue'      => "Hello, please email {$target} to request verification as a Gulf Coast Venue.",
            'journalist' => "Hello, please email {$target} to request verification as a Gulf Coast Journalist.",
        ];

        $subject = 'Gulf Coast Music — Role Verification Instruction';
        $body = $lines[$role] ?? null;
        if (!$body) return;

        // Simple text mail; you can replace with Mailable if you prefer
        try {
            Mail::raw($body, function ($m) use ($user, $subject) {
                $m->to($user->email, $user->name)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            // Fail silently or log; don't break registration flow
            \Log::warning('Role instruction email failed: ' . $e->getMessage());
        }
    }


    public function check()
    {
        return response()->json(['message' => 'OK']);
    }
}
