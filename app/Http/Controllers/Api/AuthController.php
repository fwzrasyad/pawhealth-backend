<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * POST /api/auth/sync
     *
     * Syncs the Firebase-authenticated user to the local database.
     * The VerifyFirebaseToken middleware already handles find-or-create,
     * so this endpoint simply returns the authenticated user's data.
     * The Flutter app may also send extra profile fields to update.
     */
    public function sync(Request $request)
    {
        $user = $request->user();

        // Allow the Flutter app to push profile updates during sync
        $updateData = $request->only(['name', 'email', 'phone_number', 'role']);
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return new UserResource($user->fresh());
    }
}
