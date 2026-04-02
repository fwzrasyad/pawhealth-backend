<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Symfony\Component\HttpFoundation\Response;

class VerifyFirebaseToken
{
    /**
     * Handle an incoming request.
     *
     * Extracts the Firebase ID token from the Authorization header,
     * verifies it, maps the Firebase UID to a local user, and logs them in.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized — no token provided.'], 401);
        }

        try {
            $auth = Firebase::auth();
            $verifiedToken = $auth->verifyIdToken($token);
            $firebaseUid = $verifiedToken->claims()->get('sub');

            // Look up or auto-create the local user using the Firebase UID
            $user = User::firstOrCreate(
                ['user_id' => $firebaseUid],
                [
                    'name'         => $verifiedToken->claims()->get('name', ''),
                    'email'        => $verifiedToken->claims()->get('email', ''),
                    'password'     => '',
                    'role'         => 'owner',
                    'phone_number' => '',
                ]
            );

            Auth::login($user);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unauthorized — invalid or expired token.',
                'error'   => $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
