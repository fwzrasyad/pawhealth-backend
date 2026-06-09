<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $storageService;

    public function __construct(FirebaseStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

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

        // Handle profile image file upload
        if ($request->hasFile('profile_image')) {
            $request->validate([
                'profile_image' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            // Delete old image if exists
            $this->storageService->delete($user->profile_image_url);

            $updateData['profile_image_url'] = $this->storageService->upload(
                $request->file('profile_image'),
                'users'
            );
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Load clinic relationship to include clinic status in the response
        $user->load('clinic');

        return new UserResource($user->fresh(['clinic']));
    }
}
