<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\JsonResponse;

class MemberLoginController extends Controller
{
    public function redirect(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'auth_url' => $url
        ]);
    }

    public function callback(): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->user();
        } catch (Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Google authentication failed'
            ], 401);
        }

        $email = $googleUser->getEmail();

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'google_id' => $googleUser->getId(),
                'name'      => $googleUser->getName(),
                'email'     => $email,
                'avatar'    => $googleUser->getAvatar(),
                'role'      => 'student',
            ]);
        }

        $token = $user->createToken('member-token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token'   => $token,
            'user'    => $user
        ]);
    }
}
